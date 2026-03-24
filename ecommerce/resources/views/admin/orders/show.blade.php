@extends('layouts.admin')

@section('title', 'Pedido ' . $order->order_number)

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Volver a pedidos
    </a>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Pedido {{ $order->order_number }}</h1>
        <span class="px-3 py-1 text-sm font-medium rounded bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-800">
            {{ $order->status_label }}
        </span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <!-- Products -->
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Productos</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex gap-4 p-3 bg-gray-50 rounded">
                        @if($item->product_image)
                            <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden">
                                <img src="{{ asset('storage/' . $item->product_image) }}" alt="" class="w-full h-full object-cover"
                                     onerror="this.src='{{ $item->product_image }}'">
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->quantity }} x S/ {{ number_format($item->unit_price, 2) }}</p>
                        </div>
                        <p class="font-medium">S/ {{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="border-t mt-4 pt-4 flex justify-between font-bold">
                <span>Total</span>
                <span>S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <!-- Voucher -->
        @if($order->voucher_url)
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Comprobante de Pago</h2>
            <div class="bg-gray-100 rounded overflow-hidden">
                <img src="{{ asset('storage/' . $order->voucher_url) }}" alt="Comprobante" class="w-full max-h-96 object-contain">
            </div>
            @if($order->voucher_uploaded_at)
                <p class="text-xs text-gray-500 mt-2">Subido el {{ $order->voucher_uploaded_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
        @endif

        <!-- Actions -->
        @if($order->status === 'pending_validation')
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Validar Pedido</h2>
            <div class="flex gap-4">
                <form action="{{ route('admin.orders.validate', $order) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="w-full px-4 py-3 border border-red-200 text-red-600 rounded font-medium hover:bg-red-50 transition"
                            onclick="return confirm('¿Rechazar pedido?')">
                        <i class="fas fa-times mr-2"></i> Rechazar
                    </button>
                </form>
                <form action="{{ route('admin.orders.validate', $order) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded font-medium hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i> Confirmar Pago
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if(in_array($order->status, ['confirmed', 'processing', 'shipped']))
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Actualizar Estado</h2>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex gap-4">
                @csrf
                <select name="status" class="flex-1 px-4 py-2 border border-gray-200 rounded">
                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En Proceso</option>
                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregado</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded font-medium hover:bg-gray-800 transition">
                    Actualizar
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Customer Info -->
    <div class="space-y-6">
        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Cliente</h2>
            <div class="space-y-3 text-sm">
                <p><span class="text-gray-500">Nombre:</span> {{ $order->customer_name }}</p>
                <p><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
                <p><span class="text-gray-500">Teléfono:</span> {{ $order->customer_phone }}</p>
                <p><span class="text-gray-500">Método:</span> {{ ucfirst($order->payment_method) }}</p>
            </div>
        </div>

        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Dirección de Envío</h2>
            <p class="text-sm text-gray-600">{{ $order->shipping_address }}</p>
        </div>

        <div class="bg-white rounded shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold mb-4">Historial</h2>
            <div class="space-y-3 text-sm">
                <div class="flex gap-3">
                    <div class="w-2 h-2 mt-1.5 rounded-full bg-gray-400"></div>
                    <div>
                        <p>Pedido creado</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @if($order->voucher_uploaded_at)
                <div class="flex gap-3">
                    <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-500"></div>
                    <div>
                        <p>Comprobante subido</p>
                        <p class="text-xs text-gray-500">{{ $order->voucher_uploaded_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif
                @if($order->validated_at)
                <div class="flex gap-3">
                    <div class="w-2 h-2 mt-1.5 rounded-full bg-{{ $order->status === 'rejected' ? 'red' : 'green' }}-500"></div>
                    <div>
                        <p>{{ $order->status === 'rejected' ? 'Rechazado' : 'Confirmado' }}</p>
                        <p class="text-xs text-gray-500">{{ $order->validated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
