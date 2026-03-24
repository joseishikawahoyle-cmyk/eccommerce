@extends('layouts.landing')

@section('title', 'Productos')

@section('content')
<section class="py-8 md:py-12 px-6 md:px-12 max-w-7xl mx-auto">
    <div class="mb-8 md:mb-12">
        <span class="label-caps text-gray-500 mb-2 block">Catálogo</span>
        <h1 class="text-2xl md:text-4xl font-bold tracking-tight">Todos los Productos</h1>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <form action="{{ route('products') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Buscar productos..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <select name="category" onchange="this.form.submit()" class="px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Products Grid -->
    @if($products->count())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->withQueryString()->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No se encontraron productos</p>
        </div>
    @endif
</section>
@endsection
