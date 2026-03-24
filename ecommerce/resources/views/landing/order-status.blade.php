@extends('layouts.landing')

@section('title', 'Pedido #' . substr($order->order_number, -8))

@section('content')
<section class="py-8 md:py-12 px-6 md:px-12 max-w-3xl mx-auto">
    <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary mb-8 transition">
        <i class="fas fa-arrow-left"></i> Volver al inicio
    </a>

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <span class="label-caps text-gray-500 mb-1 block">Pedido {{ $order->order_number }}</span>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Estado del Pedido</h1>
        </div>
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded text-sm font-medium bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-800">
            @switch($order->status)
                @case('pending_payment')<i class="fas fa-clock"></i>@break
                @case('pending_validation')<i class="fas fa-hourglass-half"></i>@break
                @case('confirmed')<i class="fas fa-check"></i>@break
                @case('rejected')<i class="fas fa-times"></i>@break
                @case('shipped')<i class="fas fa-truck"></i>@break
                @case('delivered')<i class="fas fa-check-double"></i>@break
            @endswitch
            {{ $order->status_label }}
        </span>
    </div>

    <!-- Status Description -->
    <div class="bg-stone-50 p-6 rounded mb-8">
        @switch($order->status)
            @case('pending_payment')
                <p class="text-gray-600">Esperando tu comprobante de pago</p>
                <form action="{{ route('payment.voucher', $order->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <label class="block border-2 border-dashed border-gray-300 rounded p-6 text-center cursor-pointer hover:border-gray-400 transition">
                        <input type="file" name="voucher" accept="image/*" required class="hidden" onchange="this.form.submit()">
                        <i class="fas fa-upload text-xl text-gray-400 mb-2"></i>
                        <p class="font-medium">Subir Comprobante de Pago</p>
                        <p class="text-sm text-gray-500">Haz clic para subir tu captura</p>
                    </label>
                </form>
                @break
            @case('pending_validation')
                <p class="text-gray-600">Tu comprobante está siendo revisado</p>
                @break
            @case('confirmed')
                <p class="text-gray-600">Tu pago ha sido confirmado. Pronto prepararemos tu pedido.</p>
                @break
            @case('rejected')
                <p class="text-red-600">Hubo un problema con tu pago. Por favor contáctanos.</p>
                @break
        @endswitch
    </div>

    <!-- Order Details -->
    <div class="border border-gray-100 rounded overflow-hidden">
        <div class="bg-white p-6 border-b border-gray-100">
            <h2 class="font-bold mb-4">Productos</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <div>
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-gray-500">Cantidad: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium">S/ {{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white p-6 border-b border-gray-100">
            <h2 class="font-bold mb-4">Información de Envío</h2>
            <div class="text-sm space-y-2">
                <p><span class="text-gray-500">Nombre:</span> {{ $order->customer_name }}</p>
                <p><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
                <p><span class="text-gray-500">Teléfono:</span> {{ $order->customer_phone }}</p>
                <p><span class="text-gray-500">Dirección:</span> {{ $order->shipping_address }}</p>
            </div>
        </div>

        <div class="bg-stone-50 p-6">
            <div class="flex justify-between items-center">
                <span class="font-bold">Total</span>
                <span class="text-xl font-bold">S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="mt-8">
        <h2 class="font-bold mb-4">Historial</h2>
        <div class="space-y-4">
            <div class="flex gap-4">
                <div class="w-3 h-3 mt-1.5 rounded-full bg-primary"></div>
                <div>
                    <p class="font-medium">Pedido creado</p>
                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            @if($order->voucher_uploaded_at)
                <div class="flex gap-4">
                    <div class="w-3 h-3 mt-1.5 rounded-full bg-blue-500"></div>
                    <div>
                        <p class="font-medium">Comprobante subido</p>
                        <p class="text-sm text-gray-500">{{ $order->voucher_uploaded_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @endif
            @if($order->validated_at)
                <div class="flex gap-4">
                    <div class="w-3 h-3 mt-1.5 rounded-full {{ $order->status === 'confirmed' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <div>
                        <p class="font-medium">{{ $order->status === 'confirmed' ? 'Pago confirmado' : 'Pago rechazado' }}</p>
                        <p class="text-sm text-gray-500">{{ $order->validated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
