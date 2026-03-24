from fastapi import FastAPI, APIRouter, HTTPException, UploadFile, File, Depends, Header, Query, Response
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field, EmailStr
from typing import List, Optional
import uuid
from datetime import datetime, timezone
import jwt
import bcrypt
import requests
import asyncio
import resend

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

# Object Storage Configuration
STORAGE_URL = "https://integrations.emergentagent.com/objstore/api/v1/storage"
EMERGENT_KEY = os.environ.get("EMERGENT_LLM_KEY")
APP_NAME = "ecommerce-peru"
storage_key = None

# Resend Configuration
RESEND_API_KEY = os.environ.get("RESEND_API_KEY")
SENDER_EMAIL = os.environ.get("SENDER_EMAIL", "onboarding@resend.dev")
if RESEND_API_KEY:
    resend.api_key = RESEND_API_KEY

# JWT Configuration
JWT_SECRET = os.environ.get("JWT_SECRET", "your-secret-key-change-in-production")
JWT_ALGORITHM = "HS256"

# Create the main app
app = FastAPI(title="Ecommerce Peru API")
api_router = APIRouter(prefix="/api")
security = HTTPBearer(auto_error=False)

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# ============== STORAGE FUNCTIONS ==============
def init_storage():
    global storage_key
    if storage_key:
        return storage_key
    try:
        resp = requests.post(f"{STORAGE_URL}/init", json={"emergent_key": EMERGENT_KEY}, timeout=30)
        resp.raise_for_status()
        storage_key = resp.json()["storage_key"]
        logger.info("Storage initialized successfully")
        return storage_key
    except Exception as e:
        logger.error(f"Storage init failed: {e}")
        return None

def put_object(path: str, data: bytes, content_type: str) -> dict:
    key = init_storage()
    if not key:
        raise Exception("Storage not initialized")
    resp = requests.put(
        f"{STORAGE_URL}/objects/{path}",
        headers={"X-Storage-Key": key, "Content-Type": content_type},
        data=data, timeout=120
    )
    resp.raise_for_status()
    return resp.json()

def get_object(path: str) -> tuple:
    key = init_storage()
    if not key:
        raise Exception("Storage not initialized")
    resp = requests.get(
        f"{STORAGE_URL}/objects/{path}",
        headers={"X-Storage-Key": key}, timeout=60
    )
    resp.raise_for_status()
    return resp.content, resp.headers.get("Content-Type", "application/octet-stream")

# ============== MODELS ==============
class BrandSettings(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    store_name: str = "Mi Tienda"
    primary_color: str = "220 20% 10%"
    secondary_color: str = "0 0% 96%"
    logo_url: Optional[str] = None
    yape_number: str = ""
    yape_qr_url: Optional[str] = None
    plin_number: str = ""
    plin_qr_url: Optional[str] = None
    updated_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())

class Product(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    name: str
    description: str
    price: float
    sale_price: Optional[float] = None
    sale_start: Optional[str] = None
    sale_end: Optional[str] = None
    stock: int
    category: str
    images: List[str] = []
    is_active: bool = True
    is_deleted: bool = False
    created_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())
    updated_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())

class ProductCreate(BaseModel):
    name: str
    description: str
    price: float
    sale_price: Optional[float] = None
    sale_start: Optional[str] = None
    sale_end: Optional[str] = None
    stock: int
    category: str
    images: List[str] = []
    is_active: bool = True

class CartItem(BaseModel):
    product_id: str
    quantity: int

class OrderCreate(BaseModel):
    items: List[CartItem]
    customer_name: str
    customer_email: EmailStr
    customer_phone: str
    shipping_address: str
    payment_method: str = "yape"

class Order(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    items: List[dict]
    customer_name: str
    customer_email: str
    customer_phone: str
    shipping_address: str
    payment_method: str
    subtotal: float
    total: float
    status: str = "pending_payment"
    voucher_url: Optional[str] = None
    voucher_uploaded_at: Optional[str] = None
    validated_at: Optional[str] = None
    validated_by: Optional[str] = None
    created_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())
    updated_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())

class AdminUser(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    email: str
    password_hash: str
    name: str
    role: str = "admin"
    created_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())

class AdminLogin(BaseModel):
    email: str
    password: str

class AdminRegister(BaseModel):
    email: str
    password: str
    name: str

class Banner(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    title: str
    subtitle: Optional[str] = None
    image_url: str
    link: Optional[str] = None
    is_active: bool = True
    position: int = 0
    size: str = "large"
    created_at: str = Field(default_factory=lambda: datetime.now(timezone.utc).isoformat())

class BannerCreate(BaseModel):
    title: str
    subtitle: Optional[str] = None
    image_url: str
    link: Optional[str] = None
    is_active: bool = True
    position: int = 0
    size: str = "large"

# ============== AUTH HELPERS ==============
def create_token(user_id: str, email: str, role: str) -> str:
    payload = {
        "user_id": user_id,
        "email": email,
        "role": role,
        "exp": datetime.now(timezone.utc).timestamp() + 86400
    }
    return jwt.encode(payload, JWT_SECRET, algorithm=JWT_ALGORITHM)

def verify_token(token: str) -> dict:
    try:
        payload = jwt.decode(token, JWT_SECRET, algorithms=[JWT_ALGORITHM])
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expired")
    except jwt.InvalidTokenError:
        raise HTTPException(status_code=401, detail="Invalid token")

async def get_current_admin(credentials: HTTPAuthorizationCredentials = Depends(security)):
    if not credentials:
        raise HTTPException(status_code=401, detail="Not authenticated")
    payload = verify_token(credentials.credentials)
    if payload.get("role") != "admin":
        raise HTTPException(status_code=403, detail="Not authorized")
    return payload

# ============== PUBLIC ROUTES ==============
@api_router.get("/")
async def root():
    return {"message": "Ecommerce Peru API", "status": "running"}

@api_router.get("/brand")
async def get_brand():
    brand = await db.brand_settings.find_one({}, {"_id": 0})
    if not brand:
        default_brand = BrandSettings()
        await db.brand_settings.insert_one(default_brand.model_dump())
        return default_brand.model_dump()
    return brand

@api_router.get("/products")
async def get_products(category: Optional[str] = None, search: Optional[str] = None):
    query = {"is_deleted": False, "is_active": True}
    if category:
        query["category"] = category
    if search:
        query["name"] = {"$regex": search, "$options": "i"}
    
    products = await db.products.find(query, {"_id": 0}).to_list(100)
    
    now = datetime.now(timezone.utc).isoformat()
    for p in products:
        if p.get("sale_price") and p.get("sale_start") and p.get("sale_end"):
            if p["sale_start"] <= now <= p["sale_end"]:
                p["current_price"] = p["sale_price"]
                p["on_sale"] = True
            else:
                p["current_price"] = p["price"]
                p["on_sale"] = False
        else:
            p["current_price"] = p["price"]
            p["on_sale"] = False
    
    return products

@api_router.get("/products/{product_id}")
async def get_product(product_id: str):
    product = await db.products.find_one({"id": product_id, "is_deleted": False}, {"_id": 0})
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
    
    now = datetime.now(timezone.utc).isoformat()
    if product.get("sale_price") and product.get("sale_start") and product.get("sale_end"):
        if product["sale_start"] <= now <= product["sale_end"]:
            product["current_price"] = product["sale_price"]
            product["on_sale"] = True
        else:
            product["current_price"] = product["price"]
            product["on_sale"] = False
    else:
        product["current_price"] = product["price"]
        product["on_sale"] = False
    
    return product

@api_router.get("/categories")
async def get_categories():
    categories = await db.products.distinct("category", {"is_deleted": False, "is_active": True})
    return categories

@api_router.get("/banners")
async def get_banners():
    banners = await db.banners.find({"is_active": True}, {"_id": 0}).sort("position", 1).to_list(20)
    return banners

# ============== ORDER ROUTES ==============
@api_router.post("/orders", status_code=201)
async def create_order(order_data: OrderCreate):
    items_with_details = []
    subtotal = 0
    
    for item in order_data.items:
        product = await db.products.find_one({"id": item.product_id, "is_deleted": False}, {"_id": 0})
        if not product:
            raise HTTPException(status_code=404, detail=f"Product {item.product_id} not found")
        if product["stock"] < item.quantity:
            raise HTTPException(status_code=400, detail=f"Insufficient stock for {product['name']}")
        
        now = datetime.now(timezone.utc).isoformat()
        if product.get("sale_price") and product.get("sale_start") and product.get("sale_end"):
            if product["sale_start"] <= now <= product["sale_end"]:
                price = product["sale_price"]
            else:
                price = product["price"]
        else:
            price = product["price"]
        
        item_total = price * item.quantity
        subtotal += item_total
        
        items_with_details.append({
            "product_id": item.product_id,
            "product_name": product["name"],
            "product_image": product["images"][0] if product["images"] else None,
            "quantity": item.quantity,
            "unit_price": price,
            "total": item_total
        })
        
        await db.products.update_one(
            {"id": item.product_id},
            {"$inc": {"stock": -item.quantity}}
        )
    
    order = Order(
        items=items_with_details,
        customer_name=order_data.customer_name,
        customer_email=order_data.customer_email,
        customer_phone=order_data.customer_phone,
        shipping_address=order_data.shipping_address,
        payment_method=order_data.payment_method,
        subtotal=subtotal,
        total=subtotal
    )
    
    await db.orders.insert_one(order.model_dump())
    return order.model_dump()

@api_router.get("/orders/{order_id}")
async def get_order(order_id: str):
    order = await db.orders.find_one({"id": order_id}, {"_id": 0})
    if not order:
        raise HTTPException(status_code=404, detail="Order not found")
    return order

@api_router.post("/orders/{order_id}/voucher")
async def upload_voucher(order_id: str, file: UploadFile = File(...)):
    order = await db.orders.find_one({"id": order_id}, {"_id": 0})
    if not order:
        raise HTTPException(status_code=404, detail="Order not found")
    
    if order["status"] not in ["pending_payment"]:
        raise HTTPException(status_code=400, detail="Order already has a voucher or is processed")
    
    ext = file.filename.split(".")[-1] if "." in file.filename else "jpg"
    path = f"{APP_NAME}/vouchers/{order_id}/{uuid.uuid4()}.{ext}"
    data = await file.read()
    
    try:
        result = put_object(path, data, file.content_type or "image/jpeg")
        
        await db.orders.update_one(
            {"id": order_id},
            {"$set": {
                "voucher_url": result["path"],
                "voucher_uploaded_at": datetime.now(timezone.utc).isoformat(),
                "status": "pending_validation",
                "updated_at": datetime.now(timezone.utc).isoformat()
            }}
        )
        
        return {"message": "Voucher uploaded successfully", "path": result["path"]}
    except Exception as e:
        logger.error(f"Failed to upload voucher: {e}")
        raise HTTPException(status_code=500, detail="Failed to upload voucher")

# ============== FILE ROUTES ==============
@api_router.post("/upload")
async def upload_file(file: UploadFile = File(...), folder: str = "products"):
    ext = file.filename.split(".")[-1] if "." in file.filename else "bin"
    path = f"{APP_NAME}/{folder}/{uuid.uuid4()}.{ext}"
    data = await file.read()
    
    try:
        result = put_object(path, data, file.content_type or "application/octet-stream")
        return {"path": result["path"], "url": f"/api/files/{result['path']}"}
    except Exception as e:
        logger.error(f"Failed to upload file: {e}")
        raise HTTPException(status_code=500, detail="Failed to upload file")

@api_router.get("/files/{path:path}")
async def get_file(path: str):
    try:
        data, content_type = get_object(path)
        return Response(content=data, media_type=content_type)
    except Exception as e:
        logger.error(f"Failed to get file: {e}")
        raise HTTPException(status_code=404, detail="File not found")

# ============== ADMIN AUTH ROUTES ==============
@api_router.post("/admin/register")
async def admin_register(data: AdminRegister):
    existing = await db.admin_users.find_one({"email": data.email})
    if existing:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    password_hash = bcrypt.hashpw(data.password.encode(), bcrypt.gensalt()).decode()
    admin = AdminUser(
        email=data.email,
        password_hash=password_hash,
        name=data.name
    )
    
    await db.admin_users.insert_one(admin.model_dump())
    token = create_token(admin.id, admin.email, admin.role)
    
    return {"token": token, "user": {"id": admin.id, "email": admin.email, "name": admin.name, "role": admin.role}}

@api_router.post("/admin/login")
async def admin_login(data: AdminLogin):
    admin = await db.admin_users.find_one({"email": data.email}, {"_id": 0})
    if not admin:
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    if not bcrypt.checkpw(data.password.encode(), admin["password_hash"].encode()):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    token = create_token(admin["id"], admin["email"], admin["role"])
    return {"token": token, "user": {"id": admin["id"], "email": admin["email"], "name": admin["name"], "role": admin["role"]}}

@api_router.get("/admin/me")
async def admin_me(current_admin: dict = Depends(get_current_admin)):
    admin = await db.admin_users.find_one({"id": current_admin["user_id"]}, {"_id": 0, "password_hash": 0})
    return admin

# ============== ADMIN BRAND ROUTES ==============
@api_router.put("/admin/brand")
async def update_brand(brand_data: dict, current_admin: dict = Depends(get_current_admin)):
    brand_data["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    existing = await db.brand_settings.find_one({})
    if existing:
        await db.brand_settings.update_one({}, {"$set": brand_data})
    else:
        brand = BrandSettings(**brand_data)
        await db.brand_settings.insert_one(brand.model_dump())
    
    updated = await db.brand_settings.find_one({}, {"_id": 0})
    return updated

# ============== ADMIN PRODUCT ROUTES ==============
@api_router.get("/admin/products")
async def admin_get_products(current_admin: dict = Depends(get_current_admin)):
    products = await db.products.find({"is_deleted": False}, {"_id": 0}).to_list(500)
    return products

@api_router.post("/admin/products", status_code=201)
async def admin_create_product(product_data: ProductCreate, current_admin: dict = Depends(get_current_admin)):
    product = Product(**product_data.model_dump())
    await db.products.insert_one(product.model_dump())
    return product.model_dump()

@api_router.put("/admin/products/{product_id}")
async def admin_update_product(product_id: str, product_data: dict, current_admin: dict = Depends(get_current_admin)):
    product_data["updated_at"] = datetime.now(timezone.utc).isoformat()
    result = await db.products.update_one({"id": product_id}, {"$set": product_data})
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
    
    updated = await db.products.find_one({"id": product_id}, {"_id": 0})
    return updated

@api_router.delete("/admin/products/{product_id}")
async def admin_delete_product(product_id: str, current_admin: dict = Depends(get_current_admin)):
    result = await db.products.update_one(
        {"id": product_id},
        {"$set": {"is_deleted": True, "updated_at": datetime.now(timezone.utc).isoformat()}}
    )
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
    return {"message": "Product deleted"}

# ============== ADMIN ORDER ROUTES ==============
@api_router.get("/admin/orders")
async def admin_get_orders(status: Optional[str] = None, current_admin: dict = Depends(get_current_admin)):
    query = {}
    if status:
        query["status"] = status
    orders = await db.orders.find(query, {"_id": 0}).sort("created_at", -1).to_list(500)
    return orders

@api_router.put("/admin/orders/{order_id}/validate")
async def admin_validate_order(order_id: str, action: str, current_admin: dict = Depends(get_current_admin)):
    order = await db.orders.find_one({"id": order_id}, {"_id": 0})
    if not order:
        raise HTTPException(status_code=404, detail="Order not found")
    
    if action == "approve":
        new_status = "confirmed"
    elif action == "reject":
        new_status = "rejected"
        for item in order["items"]:
            await db.products.update_one(
                {"id": item["product_id"]},
                {"$inc": {"stock": item["quantity"]}}
            )
    else:
        raise HTTPException(status_code=400, detail="Invalid action")
    
    await db.orders.update_one(
        {"id": order_id},
        {"$set": {
            "status": new_status,
            "validated_at": datetime.now(timezone.utc).isoformat(),
            "validated_by": current_admin["user_id"],
            "updated_at": datetime.now(timezone.utc).isoformat()
        }}
    )
    
    if RESEND_API_KEY and action == "approve":
        try:
            params = {
                "from": SENDER_EMAIL,
                "to": [order["customer_email"]],
                "subject": "¡Tu pedido ha sido confirmado!",
                "html": f"""
                <h2>¡Hola {order['customer_name']}!</h2>
                <p>Tu pedido #{order_id[:8]} ha sido confirmado exitosamente.</p>
                <p>Pronto recibirás información sobre el envío.</p>
                <p>¡Gracias por tu compra!</p>
                """
            }
            await asyncio.to_thread(resend.Emails.send, params)
        except Exception as e:
            logger.error(f"Failed to send confirmation email: {e}")
    
    updated = await db.orders.find_one({"id": order_id}, {"_id": 0})
    return updated

# ============== ADMIN BANNER ROUTES ==============
@api_router.get("/admin/banners")
async def admin_get_banners(current_admin: dict = Depends(get_current_admin)):
    banners = await db.banners.find({}, {"_id": 0}).sort("position", 1).to_list(50)
    return banners

@api_router.post("/admin/banners")
async def admin_create_banner(banner_data: BannerCreate, current_admin: dict = Depends(get_current_admin)):
    banner = Banner(**banner_data.model_dump())
    await db.banners.insert_one(banner.model_dump())
    return banner.model_dump()

@api_router.put("/admin/banners/{banner_id}")
async def admin_update_banner(banner_id: str, banner_data: dict, current_admin: dict = Depends(get_current_admin)):
    result = await db.banners.update_one({"id": banner_id}, {"$set": banner_data})
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Banner not found")
    updated = await db.banners.find_one({"id": banner_id}, {"_id": 0})
    return updated

@api_router.delete("/admin/banners/{banner_id}")
async def admin_delete_banner(banner_id: str, current_admin: dict = Depends(get_current_admin)):
    result = await db.banners.delete_one({"id": banner_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Banner not found")
    return {"message": "Banner deleted"}

# ============== ADMIN STATS ==============
@api_router.get("/admin/stats")
async def admin_stats(current_admin: dict = Depends(get_current_admin)):
    total_products = await db.products.count_documents({"is_deleted": False})
    total_orders = await db.orders.count_documents({})
    pending_orders = await db.orders.count_documents({"status": "pending_validation"})
    confirmed_orders = await db.orders.count_documents({"status": "confirmed"})
    
    pipeline = [
        {"$match": {"status": "confirmed"}},
        {"$group": {"_id": None, "total": {"$sum": "$total"}}}
    ]
    result = await db.orders.aggregate(pipeline).to_list(1)
    total_revenue = result[0]["total"] if result else 0
    
    return {
        "total_products": total_products,
        "total_orders": total_orders,
        "pending_orders": pending_orders,
        "confirmed_orders": confirmed_orders,
        "total_revenue": total_revenue
    }

# Include router and middleware
app.include_router(api_router)

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.on_event("startup")
async def startup():
    try:
        init_storage()
    except Exception as e:
        logger.error(f"Storage init failed: {e}")

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()
