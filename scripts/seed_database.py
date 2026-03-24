#!/usr/bin/env python3
"""
Script de inicialización de base de datos para Ecommerce Perú
Ejecutar: python3 /app/scripts/seed_database.py
"""

import os
import sys
from datetime import datetime, timezone
import uuid
import bcrypt

# Add backend to path
sys.path.insert(0, '/app/backend')

from dotenv import load_dotenv
from pymongo import MongoClient

# Load environment variables
load_dotenv('/app/backend/.env')

# MongoDB connection
MONGO_URL = os.environ.get('MONGO_URL', 'mongodb://localhost:27017')
DB_NAME = os.environ.get('DB_NAME', 'test_database')

client = MongoClient(MONGO_URL)
db = client[DB_NAME]

def generate_id():
    return str(uuid.uuid4())

def now_iso():
    return datetime.now(timezone.utc).isoformat()

def hash_password(password: str) -> str:
    return bcrypt.hashpw(password.encode(), bcrypt.gensalt()).decode()

# ============== SEED DATA ==============

ADMIN_USERS = [
    {
        "id": generate_id(),
        "email": "admin@tienda.pe",
        "password_hash": hash_password("admin123"),
        "name": "Administrador",
        "role": "admin",
        "created_at": now_iso()
    }
]

BRAND_SETTINGS = {
    "id": generate_id(),
    "store_name": "Artesanías Perú",
    "primary_color": "220 20% 10%",
    "secondary_color": "0 0% 96%",
    "logo_url": None,
    "yape_number": "987654321",
    "yape_qr_url": None,
    "plin_number": "987654321",
    "plin_qr_url": None,
    "updated_at": now_iso()
}

CATEGORIES = [
    "Bolsos",
    "Accesorios", 
    "Decoración",
    "Textiles",
    "Joyería",
    "Cerámica"
]

PRODUCTS = [
    {
        "id": generate_id(),
        "name": "Bolso de Cuero Premium",
        "description": "Bolso elegante de cuero genuino con acabados artesanales. Perfecto para el uso diario. Incluye correa ajustable y múltiples compartimentos.",
        "price": 299.00,
        "sale_price": 249.00,
        "sale_start": "2024-01-01T00:00:00Z",
        "sale_end": "2026-12-31T23:59:59Z",
        "stock": 15,
        "category": "Bolsos",
        "images": ["https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Cartera Minimalista",
        "description": "Cartera compacta con diseño minimalista. Ideal para llevar lo esencial. Fabricada con materiales de alta calidad.",
        "price": 89.00,
        "sale_price": None,
        "sale_start": None,
        "sale_end": None,
        "stock": 25,
        "category": "Accesorios",
        "images": ["https://images.pexels.com/photos/167703/pexels-photo-167703.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Jarrón Decorativo Artesanal",
        "description": "Jarrón de cerámica artesanal hecho a mano. Pieza única para decoración de interiores. Acabado mate elegante.",
        "price": 159.00,
        "sale_price": 129.00,
        "sale_start": "2024-01-01T00:00:00Z",
        "sale_end": "2026-12-31T23:59:59Z",
        "stock": 8,
        "category": "Decoración",
        "images": ["https://images.unsplash.com/photo-1643569556871-91ec60671ed7"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Set de Tazas Artesanales",
        "description": "Set de 2 tazas de cerámica hechas a mano. Perfectas para el café de la mañana. Diseño moderno y funcional.",
        "price": 79.00,
        "sale_price": None,
        "sale_start": None,
        "sale_end": None,
        "stock": 20,
        "category": "Cerámica",
        "images": ["https://images.unsplash.com/photo-1605714117967-9fe201ddfe9d"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Manta Tejida Alpaca",
        "description": "Manta tejida a mano con fibra de alpaca 100% natural. Suave, cálida y duradera. Diseño tradicional peruano.",
        "price": 349.00,
        "sale_price": 299.00,
        "sale_start": "2024-01-01T00:00:00Z",
        "sale_end": "2026-12-31T23:59:59Z",
        "stock": 10,
        "category": "Textiles",
        "images": ["https://images.pexels.com/photos/6032280/pexels-photo-6032280.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Aretes de Plata Andinos",
        "description": "Aretes de plata 950 con diseño inspirado en la iconografía andina. Hechos a mano por artesanos locales.",
        "price": 129.00,
        "sale_price": None,
        "sale_start": None,
        "sale_end": None,
        "stock": 30,
        "category": "Joyería",
        "images": ["https://images.pexels.com/photos/1191531/pexels-photo-1191531.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Bolso Tote de Tela",
        "description": "Bolso tote de tela resistente con estampado artesanal. Ideal para compras y uso diario. Eco-friendly.",
        "price": 59.00,
        "sale_price": None,
        "sale_start": None,
        "sale_end": None,
        "stock": 50,
        "category": "Bolsos",
        "images": ["https://images.pexels.com/photos/5864601/pexels-photo-5864601.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    },
    {
        "id": generate_id(),
        "name": "Lámpara de Mesa Cerámica",
        "description": "Lámpara de mesa con base de cerámica artesanal. Incluye pantalla de tela. Iluminación cálida y acogedora.",
        "price": 189.00,
        "sale_price": 159.00,
        "sale_start": "2024-01-01T00:00:00Z",
        "sale_end": "2026-12-31T23:59:59Z",
        "stock": 12,
        "category": "Decoración",
        "images": ["https://images.pexels.com/photos/1112598/pexels-photo-1112598.jpeg"],
        "is_active": True,
        "is_deleted": False,
        "created_at": now_iso(),
        "updated_at": now_iso()
    }
]

BANNERS = [
    {
        "id": generate_id(),
        "title": "Cyber Days",
        "subtitle": "Hasta 50% de descuento en productos seleccionados",
        "image_url": "https://images.pexels.com/photos/7302793/pexels-photo-7302793.jpeg",
        "link": "/productos",
        "is_active": True,
        "position": 0,
        "size": "large",
        "created_at": now_iso()
    },
    {
        "id": generate_id(),
        "title": "Nueva Colección",
        "subtitle": "Descubre las últimas tendencias",
        "image_url": "https://images.pexels.com/photos/13221796/pexels-photo-13221796.jpeg",
        "link": "/productos",
        "is_active": True,
        "position": 1,
        "size": "small",
        "created_at": now_iso()
    },
    {
        "id": generate_id(),
        "title": "Envío Gratis",
        "subtitle": "En compras mayores a S/150",
        "image_url": "https://images.pexels.com/photos/3817497/pexels-photo-3817497.jpeg",
        "link": "/productos",
        "is_active": True,
        "position": 2,
        "size": "small",
        "created_at": now_iso()
    }
]

# ============== SEED FUNCTIONS ==============

def clear_database():
    """Elimina todos los datos existentes"""
    print("🗑️  Limpiando base de datos...")
    db.admin_users.delete_many({})
    db.brand_settings.delete_many({})
    db.products.delete_many({})
    db.banners.delete_many({})
    db.orders.delete_many({})
    print("   ✓ Base de datos limpiada")

def seed_admin_users():
    """Crea usuarios administradores"""
    print("👤 Creando usuarios admin...")
    for user in ADMIN_USERS:
        db.admin_users.insert_one(user)
        print(f"   ✓ Admin: {user['email']}")

def seed_brand_settings():
    """Configura la marca"""
    print("🎨 Configurando marca...")
    db.brand_settings.insert_one(BRAND_SETTINGS)
    print(f"   ✓ Tienda: {BRAND_SETTINGS['store_name']}")
    print(f"   ✓ Yape: {BRAND_SETTINGS['yape_number']}")
    print(f"   ✓ Plin: {BRAND_SETTINGS['plin_number']}")

def seed_products():
    """Crea productos de ejemplo"""
    print("📦 Creando productos...")
    for product in PRODUCTS:
        db.products.insert_one(product)
        sale_info = f" (Oferta: S/{product['sale_price']})" if product['sale_price'] else ""
        print(f"   ✓ {product['name']} - S/{product['price']}{sale_info}")

def seed_banners():
    """Crea banners promocionales"""
    print("🖼️  Creando banners...")
    for banner in BANNERS:
        db.banners.insert_one(banner)
        print(f"   ✓ {banner['title']} ({banner['size']})")

def create_indexes():
    """Crea índices para optimizar consultas"""
    print("📊 Creando índices...")
    db.products.create_index("id", unique=True)
    db.products.create_index("category")
    db.products.create_index("is_active")
    db.products.create_index("is_deleted")
    db.orders.create_index("id", unique=True)
    db.orders.create_index("status")
    db.orders.create_index("created_at")
    db.admin_users.create_index("id", unique=True)
    db.admin_users.create_index("email", unique=True)
    db.banners.create_index("id", unique=True)
    db.banners.create_index("position")
    print("   ✓ Índices creados")

def show_summary():
    """Muestra resumen de datos"""
    print("\n" + "="*50)
    print("📋 RESUMEN DE BASE DE DATOS")
    print("="*50)
    print(f"   Productos: {db.products.count_documents({})}")
    print(f"   Banners: {db.banners.count_documents({})}")
    print(f"   Admins: {db.admin_users.count_documents({})}")
    print(f"   Pedidos: {db.orders.count_documents({})}")
    print("="*50)
    print("\n🔐 CREDENCIALES ADMIN:")
    print("   Email: admin@tienda.pe")
    print("   Password: admin123")
    print("="*50)

def run_seed(clear=True):
    """Ejecuta el seed completo"""
    print("\n" + "="*50)
    print("🚀 INICIANDO SEED DE BASE DE DATOS")
    print(f"   Database: {DB_NAME}")
    print(f"   MongoDB: {MONGO_URL}")
    print("="*50 + "\n")
    
    if clear:
        clear_database()
    
    seed_admin_users()
    seed_brand_settings()
    seed_products()
    seed_banners()
    create_indexes()
    show_summary()
    
    print("\n✅ Seed completado exitosamente!\n")

# ============== MAIN ==============

if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description='Seed de base de datos Ecommerce Perú')
    parser.add_argument('--no-clear', action='store_true', help='No limpiar datos existentes')
    parser.add_argument('--only', choices=['admin', 'brand', 'products', 'banners'], help='Seed solo una colección')
    
    args = parser.parse_args()
    
    if args.only:
        print(f"\n🎯 Ejecutando seed solo para: {args.only}\n")
        if args.only == 'admin':
            seed_admin_users()
        elif args.only == 'brand':
            seed_brand_settings()
        elif args.only == 'products':
            seed_products()
        elif args.only == 'banners':
            seed_banners()
        show_summary()
    else:
        run_seed(clear=not args.no_clear)
