@extends('layouts.landing')

@section('title', $product->name)

@section('content')
<section class="py-8 md:py-12 px-6 md:px-12 max-w-7xl mx-auto" x-data="productPage()">
    <a href="{{ route('products') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary mb-8 transition">
        <i class="fas fa-arrow-left"></i> Volver al catálogo
    </a>

    <div class="grid md:grid-cols-2 gap-8 md:gap-12">
        <!-- Images -->
        <div>
            <div class="aspect-square overflow-hidden bg-gray-100 rounded mb-4">
                @if($product->images->count())
                    <img x-bind:src="selectedImage" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <i class="fas fa-image text-6xl"></i>
                    </div>
                @endif
            </div>
            @if($product->images->count() > 1)
                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach($product->images as $image)
                        <button @click="selectedImage = '{{ $image->image_url }}'" 
                                class="flex-shrink-0 w-20 h-20 overflow-hidden rounded border-2 transition"
                                :class="selectedImage === '{{ $image->image_url }}' ? 'border-primary' : 'border-transparent'">
                            <img src="{{ asset('storage/' . $image->image_url) }}" alt="" class="w-full h-full object-cover"
                                 onerror="this.src='{{ $image->image_url }}'">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Info -->
        <div class="flex flex-col">
            <span class="label-caps text-gray-500 mb-2">{{ $product->category->name }}</span>
            <h1 class="text-2xl md:text-4xl font-bold tracking-tight mb-4">{{ $product->name }}</h1>

            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-2xl md:text-3xl font-bold">S/ {{ number_format($product->current_price, 2) }}</span>
                @if($product->isOnSale())
                    <span class="text-lg text-gray-400 line-through">S/ {{ number_format($product->price, 2) }}</span>
                    <span class="sale-badge rounded">-{{ round((1 - $product->current_price / $product->price) * 100) }}%</span>
                @endif
            </div>

            <div class="flex items-center gap-2 mb-6">
                @if($product->stock > 0)
                    <i class="fas fa-check text-green-600"></i>
                    <span class="text-sm text-green-600">En stock ({{ $product->stock }} disponibles)</span>
                @else
                    <i class="fas fa-times text-red-600"></i>
                    <span class="text-sm text-red-600">Agotado</span>
                @endif
            </div>

            <p class="text-gray-600 mb-8 leading-relaxed">{{ $product->description }}</p>

            @if($product->stock > 0)
                <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                    <div class="flex items-center border border-gray-200 rounded">
                        <button @click="quantity = Math.max(1, quantity - 1)" class="p-3 hover:bg-gray-100 transition">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="px-6 py-3 font-medium min-w-[60px] text-center" x-text="quantity"></span>
                        <button @click="quantity = Math.min({{ $product->stock }}, quantity + 1)" class="p-3 hover:bg-gray-100 transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button @click="addToCart()" class="flex-1 btn-primary rounded flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-bag"></i> Agregar al Carrito
                    </button>
                </div>
            @endif

            <div class="mt-8 p-4 bg-stone-50 rounded">
                <p class="text-sm font-medium mb-2">Métodos de Pago</p>
                <p class="text-sm text-gray-600">Aceptamos Yape, Plin y transferencias bancarias</p>
            </div>
        </div>
    </div>

    @if($relatedProducts->count())
        <div class="mt-16">
            <h2 class="text-xl font-bold mb-6">Productos Relacionados</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    @include('components.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
function productPage() {
    return {
        quantity: 1,
        selectedImage: '{{ $product->primaryImage?->image_url ?? "" }}',
        addToCart() {
            const product = {
                id: {{ $product->id }},
                name: '{{ $product->name }}',
                price: {{ $product->price }},
                current_price: {{ $product->current_price }},
                stock: {{ $product->stock }},
                image: this.selectedImage,
                category: '{{ $product->category->name }}'
            };
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                existing.quantity += this.quantity;
            } else {
                cart.push({ ...product, quantity: this.quantity });
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            window.dispatchEvent(new CustomEvent('cart-updated'));
            alert('Producto agregado al carrito');
        }
    }
}
</script>
@endpush
