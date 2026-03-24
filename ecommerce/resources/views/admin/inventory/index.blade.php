@extends('layouts.admin')

@section('title', 'Inventario')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Inventario</h1>
        <p class="text-gray-600">Control de stock de productos</p>
    </div>
    <a href="{{ route('admin.inventory.low-stock') }}" class="px-4 py-2 bg-yellow-500 text-white rounded font-medium hover:bg-yellow-600 transition">
        <i class="fas fa-exclamation-triangle mr-2"></i> Stock Bajo
    </a>
</div>

<div class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-3 px-4 font-medium text-gray-600">Producto</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">SKU</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Stock</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Reservado</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Disponible</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $inv)
                <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ showAdjust: false }">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden">
                                @if($inv->product->primaryImage)
                                    <img src="{{ asset('storage/' . $inv->product->primaryImage->image_url) }}" alt="" class="w-full h-full object-cover"
                                         onerror="this.src='{{ $inv->product->primaryImage->image_url }}'">
                                @endif
                            </div>
                            <div>
                                <p class="font-medium">{{ $inv->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $inv->product->category->name ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 font-mono text-xs">{{ $inv->sku }}</td>
                    <td class="py-3 px-4">
                        <span class="{{ $inv->isLowStock() ? 'text-red-600 font-bold' : '' }}">{{ $inv->stock }}</span>
                        @if($inv->isLowStock())
                            <i class="fas fa-exclamation-triangle text-yellow-500 ml-1"></i>
                        @endif
                    </td>
                    <td class="py-3 px-4">{{ $inv->reserved }}</td>
                    <td class="py-3 px-4 font-medium">{{ $inv->available }}</td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            <button @click="showAdjust = !showAdjust" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                Ajustar
                            </button>
                            <a href="{{ route('admin.inventory.movements', $inv->product_id) }}" class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-50">
                                Historial
                            </a>
                        </div>
                    </td>
                </tr>
                <tr x-show="showAdjust" x-collapse class="bg-gray-50">
                    <td colspan="6" class="py-4 px-4">
                        <form action="{{ route('admin.inventory.adjust', $inv->product_id) }}" method="POST" class="flex items-end gap-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                                <select name="type" class="px-3 py-2 border border-gray-200 rounded text-sm">
                                    <option value="in">Entrada</option>
                                    <option value="out">Salida</option>
                                    <option value="adjustment">Ajuste (nuevo valor)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Cantidad</label>
                                <input type="number" name="quantity" required min="1" class="w-24 px-3 py-2 border border-gray-200 rounded text-sm">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                                <input type="text" name="notes" placeholder="Motivo del ajuste" class="w-full px-3 py-2 border border-gray-200 rounded text-sm">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded text-sm font-medium hover:bg-gray-800">
                                Aplicar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $inventory->links() }}</div>
@endsection
