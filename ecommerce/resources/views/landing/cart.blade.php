@extends('layouts.landing')

@section('title', 'Carrito')

@section('content')
<section class="py-8 md:py-12 px-6 md:px-12 max-w-7xl mx-auto" x-data="cartPage()">
    <a href="{{ route('products') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary mb-4 transition">
        <i class="fas fa-arrow-left"></i> Seguir comprando
    </a>
    <h1 class="text-2xl md:text-4xl font-bold tracking-tight mb-8">Tu Carrito</h1>

    <template x-if="items.length === 0">
        <div class="text-center py-16">
            <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
            <h2 class="text-xl font-bold mb-2">Tu carrito está vacío</h2>
            <p class="text-gray-600 mb-8">Agrega productos para comenzar tu compra</p>
            <a href="{{ route('products') }}" class="btn-primary rounded inline-flex items-center gap-2">
                Explorar Productos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </template>

    <template x-if="items.length > 0">
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <template x-for="item in items" :key="item.id">
                    <div class="flex gap-4 p-4 bg-white border border-gray-100 rounded">
                        <div class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 flex flex-col">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="font-medium" x-text="item.name"></h3>
                                    <p class="text-sm text-gray-500" x-text="item.category"></p>
                                </div>
                                <button @click="removeItem(item.id)" class="p-2 text-gray-400 hover:text-red-500 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="mt-auto flex items-center justify-between">
                                <div class="flex items-center border border-gray-200 rounded">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)" class="p-2 hover:bg-gray-100">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <span class="px-3 text-sm font-medium" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(item.id, item.quantity + 1)" class="p-2 hover:bg-gray-100">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                <span class="font-semibold" x-text="'S/ ' + (item.current_price * item.quantity).toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-stone-50 p-6 rounded sticky top-24">
                    <h2 class="text-lg font-bold mb-4">Resumen del Pedido</h2>
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span x-text="'S/ ' + getTotal().toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Envío</span>
                            <span class="text-green-600">Por calcular</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between font-bold">
                            <span>Total</span>
                            <span x-text="'S/ ' + getTotal().toFixed(2)"></span>
                        </div>
                    </div>
                    <a href="{{ route('checkout') }}" class="w-full btn-primary rounded flex items-center justify-center gap-2">
                        Proceder al Pago <i class="fas fa-arrow-right"></i>
                    </a>
                    <p class="text-xs text-gray-500 text-center mt-4">Aceptamos Yape, Plin y transferencias</p>
                </div>
            </div>
        </div>
    </template>
</section>
@endsection

@push('scripts')
<script>
function cartPage() {
    return {
        items: [],
        init() { this.loadCart(); },
        loadCart() {
            this.items = JSON.parse(localStorage.getItem('cart') || '[]');
        },
        removeItem(id) {
            this.items = this.items.filter(i => i.id !== id);
            localStorage.setItem('cart', JSON.stringify(this.items));
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },
        updateQuantity(id, quantity) {
            if (quantity < 1) {
                this.removeItem(id);
                return;
            }
            const item = this.items.find(i => i.id === id);
            if (item) {
                item.quantity = Math.min(item.stock, quantity);
                localStorage.setItem('cart', JSON.stringify(this.items));
                window.dispatchEvent(new CustomEvent('cart-updated'));
            }
        },
        getTotal() {
            return this.items.reduce((sum, item) => sum + (item.current_price * item.quantity), 0);
        }
    }
}
</script>
@endpush
