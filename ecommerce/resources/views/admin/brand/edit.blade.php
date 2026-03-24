@extends('layouts.admin')

@section('title', 'Configuración de Marca')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Configuración de Marca</h1>
    <p class="text-gray-600">Personaliza la identidad de tu tienda</p>
</div>

<form action="{{ route('admin.brand.update') }}" method="POST" class="bg-white rounded shadow-sm border border-gray-100 p-6 max-w-2xl">
    @csrf @method('PUT')

    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Tienda</label>
            <input type="text" name="store_name" value="{{ old('store_name', $brand->store_name) }}" required
                   class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Color Principal (HSL)</label>
            <div class="flex flex-wrap gap-2 mb-2">
                @foreach([
                    ['name' => 'Charcoal', 'value' => '220 20% 10%'],
                    ['name' => 'Navy', 'value' => '220 50% 20%'],
                    ['name' => 'Forest', 'value' => '150 40% 25%'],
                    ['name' => 'Burgundy', 'value' => '350 50% 30%'],
                    ['name' => 'Terracotta', 'value' => '20 60% 45%'],
                    ['name' => 'Slate', 'value' => '210 20% 35%'],
                ] as $color)
                    <button type="button" onclick="document.querySelector('input[name=primary_color]').value = '{{ $color['value'] }}'"
                            class="w-10 h-10 rounded border-2 border-transparent hover:border-gray-300"
                            style="background-color: hsl({{ $color['value'] }})" title="{{ $color['name'] }}"></button>
                @endforeach
            </div>
            <input type="text" name="primary_color" value="{{ old('primary_color', $brand->primary_color) }}" 
                   class="w-full px-4 py-2 border border-gray-200 rounded font-mono text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20">
            <p class="text-xs text-gray-500 mt-1">Formato: Hue (0-360) Saturation% Lightness%</p>
        </div>

        <div class="border-t pt-6">
            <h3 class="font-medium mb-4">Datos de Pago</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número Yape</label>
                    <input type="text" name="yape_number" value="{{ old('yape_number', $brand->yape_number) }}" placeholder="987654321"
                           class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número Plin</label>
                    <input type="text" name="plin_number" value="{{ old('plin_number', $brand->plin_number) }}" placeholder="987654321"
                           class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                </div>
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="font-medium mb-4">Información de Contacto</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sobre Nosotros</label>
                    <textarea name="about_text" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">{{ old('about_text', $brand->about_text) }}</textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email de Contacto</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $brand->contact_email) }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $brand->contact_phone) }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $brand->address) }}"
                           class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp (con código país)</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $brand->whatsapp_number) }}" placeholder="51987654321"
                           class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-gray-900/20">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded font-medium hover:bg-gray-800 transition">
            <i class="fas fa-save mr-2"></i> Guardar Configuración
        </button>
    </div>
</form>
@endsection
