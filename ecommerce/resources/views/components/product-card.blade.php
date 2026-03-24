<a href="{{ route('product', $product->slug) }}" class="product-card group block">
    <div class="relative aspect-square overflow-hidden bg-gray-100 mb-4 rounded">
        @if($product->primaryImage)
            <img src="{{ asset('storage/' . $product->primaryImage->image_url) }}" 
                 alt="{{ $product->name }}" 
                 class="product-image w-full h-full object-cover"
                 onerror="this.src='{{ $product->primaryImage->image_url }}'">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <i class="fas fa-image text-4xl"></i>
            </div>
        @endif
        @if($product->isOnSale())
            <span class="sale-badge absolute top-3 left-3 rounded">OFERTA</span>
        @endif
        @if($product->stock <= 0)
            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                <span class="text-white font-bold">AGOTADO</span>
            </div>
        @endif
    </div>
    <div>
        <p class="label-caps text-gray-500 mb-1">{{ $product->category->name ?? '' }}</p>
        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2">{{ $product->name }}</h3>
        <div class="flex items-center gap-2">
            <span class="font-semibold text-lg">S/ {{ number_format($product->current_price, 2) }}</span>
            @if($product->isOnSale())
                <span class="text-gray-400 line-through text-sm">S/ {{ number_format($product->price, 2) }}</span>
            @endif
        </div>
        @if($product->stock > 0 && $product->stock <= 5)
            <p class="text-xs text-orange-600 mt-1">¡Solo quedan {{ $product->stock }}!</p>
        @endif
    </div>
</a>
