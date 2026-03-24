# Ecommerce Perú - Esquema de Base de Datos MongoDB

## Colecciones

### 1. admin_users
```json
{
  "id": "uuid",
  "email": "string (unique)",
  "password_hash": "string (bcrypt)",
  "name": "string",
  "role": "string (admin)",
  "created_at": "ISO datetime"
}
```

### 2. brand_settings
```json
{
  "id": "uuid",
  "store_name": "string",
  "primary_color": "string (HSL format: '220 20% 10%')",
  "secondary_color": "string (HSL format)",
  "logo_url": "string | null",
  "yape_number": "string",
  "yape_qr_url": "string | null",
  "plin_number": "string",
  "plin_qr_url": "string | null",
  "updated_at": "ISO datetime"
}
```

### 3. products
```json
{
  "id": "uuid",
  "name": "string",
  "description": "string",
  "price": "number",
  "sale_price": "number | null",
  "sale_start": "ISO datetime | null",
  "sale_end": "ISO datetime | null",
  "stock": "integer",
  "category": "string",
  "images": ["string (URLs or storage paths)"],
  "is_active": "boolean",
  "is_deleted": "boolean (soft delete)",
  "created_at": "ISO datetime",
  "updated_at": "ISO datetime"
}
```

### 4. orders
```json
{
  "id": "uuid",
  "items": [
    {
      "product_id": "uuid",
      "product_name": "string",
      "product_image": "string | null",
      "quantity": "integer",
      "unit_price": "number",
      "total": "number"
    }
  ],
  "customer_name": "string",
  "customer_email": "string",
  "customer_phone": "string",
  "shipping_address": "string",
  "payment_method": "string (yape | plin)",
  "subtotal": "number",
  "total": "number",
  "status": "string (pending_payment | pending_validation | confirmed | rejected | shipped | delivered)",
  "voucher_url": "string | null",
  "voucher_uploaded_at": "ISO datetime | null",
  "validated_at": "ISO datetime | null",
  "validated_by": "uuid | null",
  "created_at": "ISO datetime",
  "updated_at": "ISO datetime"
}
```

### 5. banners
```json
{
  "id": "uuid",
  "title": "string",
  "subtitle": "string | null",
  "image_url": "string",
  "link": "string | null",
  "is_active": "boolean",
  "position": "integer",
  "size": "string (large | small)",
  "created_at": "ISO datetime"
}
```

## Índices

```javascript
// Products
db.products.createIndex({ "id": 1 }, { unique: true })
db.products.createIndex({ "category": 1 })
db.products.createIndex({ "is_active": 1 })
db.products.createIndex({ "is_deleted": 1 })
db.products.createIndex({ "name": "text", "description": "text" }) // Full-text search

// Orders
db.orders.createIndex({ "id": 1 }, { unique: true })
db.orders.createIndex({ "status": 1 })
db.orders.createIndex({ "created_at": -1 })
db.orders.createIndex({ "customer_email": 1 })

// Admin Users
db.admin_users.createIndex({ "id": 1 }, { unique: true })
db.admin_users.createIndex({ "email": 1 }, { unique: true })

// Banners
db.banners.createIndex({ "id": 1 }, { unique: true })
db.banners.createIndex({ "position": 1 })
db.banners.createIndex({ "is_active": 1 })
```

## Estados de Pedido (Order Status Flow)

```
pending_payment → pending_validation → confirmed → shipped → delivered
                                    ↘ rejected
```

1. **pending_payment**: Pedido creado, esperando comprobante
2. **pending_validation**: Comprobante subido, esperando validación admin
3. **confirmed**: Pago validado, preparando envío
4. **rejected**: Pago rechazado (stock devuelto)
5. **shipped**: Pedido enviado
6. **delivered**: Pedido entregado

## Queries Comunes

```javascript
// Productos activos
db.products.find({ is_deleted: false, is_active: true })

// Productos en oferta
db.products.find({
  is_deleted: false,
  is_active: true,
  sale_price: { $ne: null },
  sale_start: { $lte: new Date() },
  sale_end: { $gte: new Date() }
})

// Pedidos pendientes de validación
db.orders.find({ status: "pending_validation" }).sort({ created_at: -1 })

// Ingresos totales
db.orders.aggregate([
  { $match: { status: "confirmed" } },
  { $group: { _id: null, total: { $sum: "$total" } } }
])

// Productos por categoría
db.products.aggregate([
  { $match: { is_deleted: false, is_active: true } },
  { $group: { _id: "$category", count: { $sum: 1 } } }
])
```

## Backup y Restauración

```bash
# Backup
mongodump --uri="mongodb://localhost:27017" --db=test_database --out=/backup/$(date +%Y%m%d)

# Restaurar
mongorestore --uri="mongodb://localhost:27017" --db=test_database /backup/20240101/test_database
```
