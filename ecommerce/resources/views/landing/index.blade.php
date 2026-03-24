@extends('layouts.landing')

@section('title', 'Inicio')

@section('content')
<!-- Hero / Banners Bento Grid -->
<section class="py-8 px-6 md:px-12 max-w-7xl mx-auto">
    <div class="grid grid-cols-12 gap-4">
        @php $largeBanner = $banners->where('size', 'large')->first(); @endphp
        @php $smallBanners = $banners->where('size', 'small')->take(2); @endphp

        <!-- Large Banner -->
        <div class="col-span-12 md:col-span-8 relative overflow-hidden rounded min-h-[400px] md:min-h-[500px] bg-gray-100">
            @if($largeBanner)
                <a href="{{ $largeBanner->link ?? route('products') }}" class="block h-full">
                    <img src="{{ asset('storage/' . $largeBanner->image_url) }}" alt="{{ $largeBanner->title }}" 
                         class="w-full h-full object-cover" onerror="this.src='{{ $largeBanner->image_url }}'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-8 md:p-12">
                        <span class="label-caps text-white/80 mb-2 block">Promoción Especial</span>
                        <h1 class="text-3xl md:text-5xl font-bold text-white tracking-tight mb-4">{{ $largeBanner->title }}</h1>
                        @if($largeBanner->subtitle)
                            <p class="text-white/90 text-lg mb-6 max-w-lg">{{ $largeBanner->subtitle }}</p>
                        @endif
                        <span class="inline-flex items-center gap-2 bg-white text-gray-900 px-6 py-3 rounded font-medium hover:bg-gray-100 transition">
                            Ver Ofertas <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            @else
                <div class="h-full flex flex-col justify-end p-8 md:p-12">
                    <span class="label-caps text-gray-500 mb-2">Bienvenido</span>
                    <h1 class="text-3xl md:text-5xl font-bold tracking-tight mb-4">{{ $brand->store_name }}</h1>
                    <p class="text-gray-600 text-lg mb-6">Descubre productos artesanales únicos</p>
                    <a href="{{ route('products') }}" class="btn-primary w-fit rounded inline-flex items-center gap-2">
                        Comprar Ahora <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Small Banners -->
        <div class="col-span-12 md:col-span-4 flex flex-col gap-4">
            @forelse($smallBanners as $banner)
                <div class="flex-1 relative overflow-hidden rounded bg-gray-100 min-h-[200px]">
                    <a href="{{ $banner->link ?? route('products') }}" class="block h-full">
                        <img src="{{ asset('storage/' . $banner->image_url) }}" alt="{{ $banner->title }}" 
                             class="w-full h-full object-cover" onerror="this.src='{{ $banner->image_url }}'">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-4 md:p-6">
                            <h3 class="text-lg md:text-xl font-bold text-white">{{ $banner->title }}</h3>
                            @if($banner->subtitle)
                                <p class="text-white/80 text-sm">{{ $banner->subtitle }}</p>
                            @endif
                        </div>
                    </a>
                </div>
            @empty
                <div class="flex-1 relative overflow-hidden rounded bg-stone-100 p-6 flex flex-col justify-end">
                    <span class="label-caps text-gray-500 mb-1">Pago Fácil</span>
                    <h3 class="text-lg font-bold">Yape & Plin</h3>
                </div>
                <div class="flex-1 relative overflow-hidden rounded bg-stone-100 p-6 flex flex-col justify-end">
                    <span class="label-caps text-gray-500 mb-1">Envío</span>
                    <h3 class="text-lg font-bold">A Todo el Perú</h3>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 md:py-24 px-6 md:px-12 max-w-7xl mx-auto" id="productos">
    <div class="flex items-end justify-between mb-12">
        <div>
            <span class="label-caps text-gray-500 mb-2 block">Colección</span>
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Productos Destacados</h2>
        </div>
        <a href="{{ route('products') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-primary transition">
            Ver Todos <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($featuredProducts as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach
    </div>

    <a href="{{ route('products') }}" class="md:hidden mt-8 flex items-center justify-center gap-2 text-sm font-medium text-gray-600 hover:text-primary transition">
        Ver Todos los Productos <i class="fas fa-arrow-right"></i>
    </a>
</section>

<!-- Features -->
<section class="py-16 md:py-24 bg-stone-50">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center md:text-left">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto md:mx-0">
                    <i class="fas fa-mobile-alt text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Pago con Yape/Plin</h3>
                <p class="text-gray-600 text-sm">Paga fácil y rápido con tu billetera digital favorita</p>
            </div>
            <div class="text-center md:text-left">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto md:mx-0">
                    <i class="fas fa-truck text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Envío Seguro</h3>
                <p class="text-gray-600 text-sm">Entrega a domicilio en todo el Perú</p>
            </div>
            <div class="text-center md:text-left">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto md:mx-0">
                    <i class="fas fa-medal text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Calidad Garantizada</h3>
                <p class="text-gray-600 text-sm">Productos artesanales de la mejor calidad</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
@if($testimonials->count())
<section class="py-16 md:py-24 px-6 md:px-12 max-w-7xl mx-auto">
    <div class="text-center mb-12">
        <span class="label-caps text-gray-500 mb-2 block">Testimonios</span>
        <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Lo que dicen nuestros clientes</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
        @foreach($testimonials as $testimonial)
            <div class="bg-stone-50 p-6 rounded">
                <div class="flex gap-1 text-yellow-500 mb-4">
                    @for($i = 0; $i < $testimonial->rating; $i++)
                        <i class="fas fa-star"></i>
                    @endfor
                </div>
                <p class="text-gray-600 mb-4">"{{ $testimonial->content }}"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-500"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $testimonial->name }}</p>
                        <p class="text-sm text-gray-500">{{ $testimonial->location }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

<!-- Contact -->
<section class="py-16 md:py-24 bg-stone-50" id="contacto">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="grid md:grid-cols-2 gap-12">
            <div>
                <span class="label-caps text-gray-500 mb-2 block">Contacto</span>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight mb-6">¿Tienes preguntas?</h2>
                <p class="text-gray-600 mb-8">{{ $brand->about_text ?? 'Estamos aquí para ayudarte.' }}</p>
                <div class="space-y-4">
                    @if($brand->contact_email)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-envelope text-primary"></i>
                            <span>{{ $brand->contact_email }}</span>
                        </div>
                    @endif
                    @if($brand->contact_phone)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-phone text-primary"></i>
                            <span>{{ $brand->contact_phone }}</span>
                        </div>
                    @endif
                    @if($brand->whatsapp_number)
                        <a href="https://wa.me/{{ $brand->whatsapp_number }}" target="_blank" class="inline-flex items-center gap-2 bg-green-500 text-white px-6 py-3 rounded font-medium hover:bg-green-600 transition">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    @endif
                </div>
            </div>
            <div class="bg-white p-8 rounded shadow-sm">
                <h3 class="font-bold mb-6">Métodos de Pago</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 border rounded text-center">
                        <span class="text-3xl mb-2 block">💜</span>
                        <p class="font-medium">Yape</p>
                        <p class="text-sm text-gray-500">{{ $brand->yape_number ?? '---' }}</p>
                    </div>
                    <div class="p-4 border rounded text-center">
                        <span class="text-3xl mb-2 block">💚</span>
                        <p class="font-medium">Plin</p>
                        <p class="text-sm text-gray-500">{{ $brand->plin_number ?? '---' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
