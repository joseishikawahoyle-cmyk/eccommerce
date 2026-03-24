@extends('layouts.admin')

@section('title', isset($product) ? 'Editar Producto' : 'Nuevo Producto')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Volver a productos
    </a>
    <h1 class="text-2xl font-bold tracking-tight">{{ isset($product) ? 'Editar Producto' : 'Nuevo Producto' }}</h1>
</div>

<form action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" 
      method="POST" enctype="multipart/form-data" class="bg-white rounded shadow-sm border border-gray-100 p-6">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
            <select name="category_id" required class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                <option value="">Seleccionar...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
        <textarea name="description" rows="4" required
                  class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="grid md:grid-cols-4 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Precio (S/) *</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Precio Oferta (S/)</label>
            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        </div>
        @if(!isset($product))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Inicial *</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" required
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
            <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Auto-generado"
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        </div>
        @endif
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Imágenes</label>
        @if(isset($product) && $product->images->count())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($product->images as $image)
                    <div class="relative w-24 h-24 bg-gray-100 rounded overflow-hidden group">
                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="" class="w-full h-full object-cover"
                             onerror="this.src='{{ $image->image_url }}'">
                        <button type="button" onclick="deleteImage({{ $image->id }})" 
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
        <input type="file" name="images[]" multiple accept="image/*"
               class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        <p class="text-xs text-gray-500 mt-1">Puedes subir varias imágenes</p>
    </div>

    <div class="flex gap-6 mb-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300">
            <span class="text-sm">Producto activo</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300">
            <span class="text-sm">Producto destacado</span>
        </label>
    </div>

    <div class="flex gap-4">
        <a href="{{ route('admin.products.index') }}" class="px-6 py-2 border border-gray-200 rounded font-medium hover:bg-gray-50 transition">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded font-medium hover:bg-gray-800 transition">
            {{ isset($product) ? 'Guardar Cambios' : 'Crear Producto' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function deleteImage(imageId) {
    if (!confirm('¿Eliminar imagen?')) return;
    fetch(`/admin/products/image/${imageId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
}
</script>
@endpush
