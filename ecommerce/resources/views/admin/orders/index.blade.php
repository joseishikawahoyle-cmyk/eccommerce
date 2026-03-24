@extends('layouts.admin')

@section('title', 'Pedidos')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Pedidos</h1>
        <p class="text-gray-600">Gestiona y valida los pedidos</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 {{ !request('status') ? 'bg-gray-900 text-white' : 'border border-gray-200' }} rounded font-medium">Todos</a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_validation']) }}" class="px-4 py-2 {{ request('status') === 'pending_validation' ? 'bg-yellow-500 text-white' : 'border border-gray-200' }} rounded font-medium">Pendientes</a>
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="px-4 py-2 {{ request('status') === 'confirmed' ? 'bg-green-500 text-white' : 'border border-gray-200' }} rounded font-medium">Confirmados</a>
    </div>
</div>

<div class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-3 px-4 font-medium text-gray-600">Pedido</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Cliente</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Total</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Método</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Estado</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Fecha</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-3 px-4 font-mono text-xs">{{ $order->order_number }}</td>
                    <td class="py-3 px-4">
                        <p class="font-medium">{{ $order->customer_name }}</p>
                        <p class="text-xs text-gray-500">{{ $order->customer_email }}</p>
                    </td>
                    <td class="py-3 px-4 font-medium">S/ {{ number_format($order->total, 2) }}</td>
                    <td class="py-3 px-4">
                        <span class="text-lg">{{ $order->payment_method === 'yape' ? '💜' : '💚' }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-800">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded inline-block">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-500">No hay pedidos</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $orders->withQueryString()->links() }}</div>
@endsection
