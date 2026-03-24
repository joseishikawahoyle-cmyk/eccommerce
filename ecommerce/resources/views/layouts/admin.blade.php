<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
        .sidebar { background: hsl(220, 20%, 10%); }
        .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #9ca3af; transition: all 0.2s; }
        .sidebar-link:hover, .sidebar-link.active { color: white; background: rgba(255,255,255,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar fixed left-0 top-0 h-screen w-64 text-white z-50">
            <div class="p-6 border-b border-white/10">
                <h1 class="text-lg font-bold tracking-tight">{{ App\Models\BrandSetting::getSettings()->store_name }}</h1>
                <p class="text-xs text-gray-400 mt-1">Panel de Administración</p>
            </div>

            <nav class="py-4">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i> Dashboard
                </a>
                <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fas fa-box w-5"></i> Productos
                </a>
                <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags w-5"></i> Categorías
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse w-5"></i> Inventario
                </a>
                <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart w-5"></i> Pedidos
                    @php $pendingCount = App\Models\Order::where('status', 'pending_validation')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.banners.index') }}" class="sidebar-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="fas fa-image w-5"></i> Banners
                </a>
                <a href="{{ route('admin.brand.edit') }}" class="sidebar-link {{ request()->routeIs('admin.brand.*') ? 'active' : '' }}">
                    <i class="fas fa-palette w-5"></i> Marca
                </a>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <p class="text-white font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-gray-400 text-xs">{{ auth()->user()->email }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 hover:bg-white/10 rounded transition">
                            <i class="fas fa-sign-out-alt text-gray-400"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
