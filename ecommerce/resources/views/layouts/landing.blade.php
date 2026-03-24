<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brand->store_name ?? 'Tienda' }} - @yield('title', 'Inicio')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: {{ $brand->primary_color ?? '220 20% 10%' }};
            --secondary: {{ $brand->secondary_color ?? '0 0% 96%' }};
        }
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
        .bg-primary { background-color: hsl(var(--primary)); }
        .text-primary { color: hsl(var(--primary)); }
        .border-primary { border-color: hsl(var(--primary)); }
        .btn-primary {
            background-color: hsl(var(--primary));
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .glass-nav { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
        .label-caps { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; font-weight: 500; }
        .product-card:hover .product-image { transform: scale(1.05); }
        .product-image { transition: transform 0.5s ease-out; }
        .sale-badge { background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.5rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-gray-900">
    <!-- Navbar -->
    <header class="glass-nav sticky top-0 z-50 border-b border-gray-100" x-data="{ mobileMenu: false }">
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('landing') }}" class="font-bold text-xl tracking-tight">
                    {{ $brand->store_name ?? 'Mi Tienda' }}
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('landing') }}" class="text-sm font-medium hover:text-gray-600 {{ request()->routeIs('landing') ? 'text-primary' : 'text-gray-700' }}">Inicio</a>
                    <a href="{{ route('products') }}" class="text-sm font-medium hover:text-gray-600 {{ request()->routeIs('products') ? 'text-primary' : 'text-gray-700' }}">Productos</a>
                    <a href="{{ route('landing') }}#contacto" class="text-sm font-medium text-gray-700 hover:text-gray-600">Contacto</a>
                </nav>

                <div class="flex items-center gap-4">
                    <a href="{{ route('cart') }}" class="relative p-2 hover:bg-gray-100 rounded" x-data="cart" @cart-updated.window="loadCart()">
                        <i class="fas fa-shopping-bag text-lg"></i>
                        <span x-show="count > 0" x-text="count" class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-xs rounded-full flex items-center justify-center"></span>
                    </a>
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 hover:bg-gray-100 rounded">
                        <i class="fas" :class="mobileMenu ? 'fa-times' : 'fa-bars'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-collapse class="md:hidden border-t border-gray-100 bg-white">
            <nav class="px-6 py-4 space-y-2">
                <a href="{{ route('landing') }}" class="block py-2 text-sm font-medium">Inicio</a>
                <a href="{{ route('products') }}" class="block py-2 text-sm font-medium">Productos</a>
                <a href="{{ route('landing') }}#contacto" class="block py-2 text-sm font-medium">Contacto</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-16">
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="grid md:grid-cols-4 gap-12">
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold tracking-tight mb-4">{{ $brand->store_name ?? 'Mi Tienda' }}</h3>
                    <p class="text-gray-300 text-sm max-w-md">{{ $brand->about_text ?? 'Tu tienda de confianza en Perú.' }}</p>
                </div>
                <div>
                    <h4 class="label-caps text-gray-400 mb-4">Navegación</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('landing') }}" class="text-gray-300 hover:text-white">Inicio</a></li>
                        <li><a href="{{ route('products') }}" class="text-gray-300 hover:text-white">Productos</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="label-caps text-gray-400 mb-4">Pagos</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        @if($brand->yape_number)<li>Yape: {{ $brand->yape_number }}</li>@endif
                        @if($brand->plin_number)<li>Plin: {{ $brand->plin_number }}</li>@endif
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-12 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} {{ $brand->store_name ?? 'Mi Tienda' }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cart', () => ({
                items: [],
                count: 0,
                init() { this.loadCart(); },
                loadCart() {
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    this.items = cart;
                    this.count = cart.reduce((sum, item) => sum + item.quantity, 0);
                },
                addItem(product, quantity = 1) {
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    const existing = cart.find(i => i.id === product.id);
                    if (existing) {
                        existing.quantity += quantity;
                    } else {
                        cart.push({ ...product, quantity });
                    }
                    localStorage.setItem('cart', JSON.stringify(cart));
                    this.loadCart();
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                },
                removeItem(productId) {
                    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    cart = cart.filter(i => i.id !== productId);
                    localStorage.setItem('cart', JSON.stringify(cart));
                    this.loadCart();
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                },
                updateQuantity(productId, quantity) {
                    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    const item = cart.find(i => i.id === productId);
                    if (item) {
                        item.quantity = Math.max(1, quantity);
                        localStorage.setItem('cart', JSON.stringify(cart));
                        this.loadCart();
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                    }
                },
                getTotal() {
                    return this.items.reduce((sum, item) => sum + (item.current_price * item.quantity), 0);
                },
                clear() {
                    localStorage.removeItem('cart');
                    this.loadCart();
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                }
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>
