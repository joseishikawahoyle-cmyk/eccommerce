@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
    <p class="text-gray-600">Resumen general de tu tienda</p>
</div>

<!-- Stats Grid -->
<div class="grid md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-blue-100 text-blue-600 rounded"><i class="fas fa-box"></i></div>
        </div>
        <p class="text-2xl font-bold">{{ $stats['total_products'] }}</p>
        <p class="text-sm text-gray-600">Productos</p>
    </div>
    <div class="bg-white p-6 rounded shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-green-100 text-green-600 rounded"><i class="fas fa-shopping-cart"></i></div>
        </div>
        <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
        <p class="text-sm text-gray-600">Pedidos Totales</p>
    </div>
    <div class="bg-white p-6 rounded shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-yellow-100 text-yellow-600 rounded"><i class="fas fa-clock"></i></div>
        </div>
        <p class="text-2xl font-bold">{{ $stats['pending_orders'] }}</p>
        <p class="text-sm text-gray-600">Pendientes</p>
    </div>
    <div class="bg-white p-6 rounded shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-purple-100 text-purple-600 rounded"><i class="fas fa-dollar-sign"></i></div>
        </div>
        <p class="text-2xl font-bold">S/ {{ number_format($stats['total_revenue'], 2) }}</p>
        <p class="text-sm text-gray-600">Ingresos</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-8">
    <!-- Recent Orders -->
    <div class="bg-white rounded shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold">Pedidos Recientes</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:underline">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4 font-medium text-gray-600">ID</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Cliente</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Total</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr class="border-b border-gray-50">
                            <td class="py-3 px-4 font-mono text-xs">#{{ substr($order->order_number, -8) }}</td>
                            <td class="py-3 px-4">{{ $order->customer_name }}</td>
                            <td class="py-3 px-4 font-medium">S/ {{ number_format($order->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium rounded bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-800">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-gray-500">No hay pedidos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="bg-white rounded shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold">Stock Bajo</h2>
            <a href="{{ route('admin.inventory.low-stock') }}" class="text-sm text-blue-600 hover:underline">Ver todos</a>
        </div>
        <div class="p-4">
            @forelse($lowStockProducts as $product)
                <div class="flex items-center gap-4 p-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                        @if($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->image_url) }}" alt="" class="w-full h-full object-cover"
                                 onerror="this.src='{{ $product->primaryImage->image_url }}'">
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">{{ $product->name }}</p>
                        <p class="text-sm text-red-600">Stock: {{ $product->inventory->stock ?? 0 }}</p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-blue-600 hover:underline text-sm">Ajustar</a>
                </div>
            @empty
                <p class="text-center py-8 text-gray-500">No hay productos con stock bajo</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
