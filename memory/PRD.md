# PRD - Ecommerce Perú con Yape/Plin

## Problema Original
Plataforma web ecommerce con sistema de gestión de contenidos (CMS) para el mercado peruano, con flujo de pago manual mediante Yape/Plin y validación de comprobantes.

## Arquitectura
- **Frontend**: React 19 + Tailwind CSS + Shadcn UI + Framer Motion
- **Backend**: FastAPI (Python)
- **Base de datos**: MongoDB
- **Almacenamiento**: Emergent Object Storage
- **Email**: Resend (preparado, requiere API key para producción)

## User Personas
1. **Cliente**: Compra productos, paga con Yape/Plin, sube comprobante
2. **Admin**: Gestiona productos, valida pedidos, configura marca

## Core Requirements (Estático)
- [x] Catálogo de productos con categorías y búsqueda
- [x] Detalle de producto con galería de imágenes
- [x] Carrito de compras persistente
- [x] Checkout con selección Yape/Plin
- [x] Generación de QR para pago
- [x] Módulo de subida de comprobantes
- [x] Estado de pedido en tiempo real
- [x] Panel Admin: Dashboard con métricas
- [x] Panel Admin: CRUD de productos con imágenes
- [x] Panel Admin: Gestión de pedidos y validación
- [x] Panel Admin: Banners promocionales (Cyber Days)
- [x] Panel Admin: Configuración de marca (colores, Yape/Plin)

## Implementado (24/03/2026)
- Plataforma ecommerce completa
- Flujo Yape/Plin con QR dinámico
- Sistema de validación de vouchers
- Panel de administración completo
- 4 productos de ejemplo + 3 banners
- Diseño minimalista con fuentes Outfit + DM Sans
- Object Storage integrado para imágenes
- Autenticación JWT para admin

## Backlog Priorizado
### P0 (Crítico)
- N/A - Core funcional completado

### P1 (Importante)
- Integración Resend para emails de confirmación (requiere API key)
- Notificaciones push al admin (nuevos pedidos)
- Precios programados (Cyber Days con fechas)

### P2 (Mejoras)
- Integración Niubiz/Izipay para tarjetas
- WhatsApp notifications (Twilio)
- Sistema de inventario con bloqueo temporal (Redis)
- Múltiples imágenes en producto (galería completa)
- Reportes de ventas

## Próximos Pasos
1. Configurar Resend API key para emails
2. Agregar más productos reales
3. Personalizar colores de marca desde admin
