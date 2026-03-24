@extends('layouts.admin')

@section('title', 'Productos')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Productos</h1>
        <p class="text-gray-600">Gestiona tu catálogo de productos</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-gray-900 text-white rounded font-medium hover:bg-gray-800 transition">
        <i class="fas fa-plus mr-2"></i> Nuevo Producto
    </a>
</div>

<div class="bg-white rounded shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-3 px-4 font-medium text-gray-600">Producto</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Categoría</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Precio</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Stock</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Estado</th>
                <th class="text-left py-3 px-4 font-medium text-gray-600">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                @if($product->primaryImage)
                                    <img src="{{ asset('storage/' . $product->primaryImage->image_url) }}" alt="" class="w-full h-full object-cover"
                                         onerror="this.src='{{ $product->primaryImage->image_url }}'">
                                @endif
                            </div>
                            <div>
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">SKU: {{ $product->inventory?->sku ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">{{ $product->category->name ?? 'Sin categoría' }}</td>
                    <td class="py-3 px-4">
                        <span class="font-medium">S/ {{ number_format($product->price, 2) }}</span>
                        @if($product->sale_price)
                            <span class="text-red-600 text-xs ml-1">S/ {{ number_format($product->sale_price, 2) }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @php $stock = $product->inventory?->stock ?? 0; @endphp
                        <span class="{{ $stock <= 5 ? 'text-red-600' : 'text-gray-900' }}">{{ $stock }}</span>
                    </td>
                    <td class="py-3 px-4">
                        @if($product->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Activo</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Inactivo</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-500">No hay productos</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $products->links() }}</div>
@endsection
