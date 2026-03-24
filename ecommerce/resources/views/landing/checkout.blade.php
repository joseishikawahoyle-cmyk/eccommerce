@extends('layouts.landing')

@section('title', 'Checkout')

@section('content')
<section class="py-8 md:py-12 px-6 md:px-12 max-w-4xl mx-auto" x-data="checkoutPage()">
    <!-- Progress -->
    <div class="flex items-center justify-center gap-4 mb-12">
        <template x-for="(stepName, index) in ['Datos', 'Pago', 'Confirmación']" :key="index">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                     :class="step > index ? 'bg-primary text-white' : (step === index ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500')">
                    <template x-if="step > index"><i class="fas fa-check"></i></template>
                    <template x-if="step <= index"><span x-text="index + 1"></span></template>
                </div>
                <span class="hidden sm:block text-sm" :class="step >= index ? 'text-primary' : 'text-gray-400'" x-text="stepName"></span>
                <template x-if="index < 2"><div class="w-8 h-px bg-gray-200"></div></template>
            </div>
        </template>
    </div>

    <!-- Step 1: Customer Data -->
    <div x-show="step === 0" x-transition>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight mb-8">Información de Envío</h1>
        <form @submit.prevent="submitOrder" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" x-model="form.customer_name" required class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" x-model="form.customer_email" required class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono / WhatsApp</label>
                <input type="tel" x-model="form.customer_phone" required placeholder="987654321" class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección de Envío</label>
                <textarea x-model="form.shipping_address" required rows="3" placeholder="Av. Principal 123, Distrito, Ciudad" class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Método de Pago</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" x-model="form.payment_method" value="yape" class="sr-only peer">
                        <div class="p-4 border-2 rounded text-center peer-checked:border-primary peer-checked:bg-stone-50 hover:bg-gray-50 transition">
                            <span class="text-2xl mb-1 block">💜</span>
                            <span class="font-medium">Yape</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" x-model="form.payment_method" value="plin" class="sr-only peer">
                        <div class="p-4 border-2 rounded text-center peer-checked:border-primary peer-checked:bg-stone-50 hover:bg-gray-50 transition">
                            <span class="text-2xl mb-1 block">💚</span>
                            <span class="font-medium">Plin</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-stone-50 p-6 rounded">
                <h3 class="font-bold mb-4">Resumen del Pedido</h3>
                <div class="space-y-2 text-sm">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between">
                            <span x-text="item.name + ' x ' + item.quantity"></span>
                            <span x-text="'S/ ' + (item.current_price * item.quantity).toFixed(2)"></span>
                        </div>
                    </template>
                    <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                        <span>Total</span>
                        <span x-text="'S/ ' + getTotal().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('cart') }}" class="flex-1 px-6 py-3 border border-gray-200 rounded text-center font-medium hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <button type="submit" :disabled="loading" class="flex-1 btn-primary rounded">
                    <span x-show="!loading">Continuar al Pago</span>
                    <span x-show="loading"><i class="fas fa-spinner fa-spin"></i> Procesando...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Payment -->
    <div x-show="step === 1" x-transition class="text-center">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight mb-4">Realiza el Pago</h1>
        <p class="text-gray-600 mb-8">Escanea el QR o transfiere al número indicado</p>

        <div class="bg-white p-6 rounded shadow-sm border max-w-sm mx-auto mb-8">
            <span class="label-caps text-gray-500 mb-2 block" x-text="form.payment_method === 'yape' ? 'Yape' : 'Plin'"></span>
            <p class="text-2xl font-bold mb-6" x-text="form.payment_method === 'yape' ? '{{ $brand->yape_number }}' : '{{ $brand->plin_number }}'"></p>
            
            <div class="bg-gray-100 p-4 rounded mb-6">
                <p class="text-sm text-gray-600">Escanea con tu app de <span x-text="form.payment_method === 'yape' ? 'Yape' : 'Plin'"></span></p>
            </div>

            <div class="p-4 bg-yellow-50 rounded text-left">
                <div class="flex gap-2">
                    <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    <div class="text-sm">
                        <p class="font-medium text-yellow-800 mb-1">Total a pagar: <span x-text="'S/ ' + getTotal().toFixed(2)"></span></p>
                        <p class="text-yellow-700">Después de pagar, sube la captura del comprobante</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-sm mx-auto">
            <label class="block border-2 border-dashed border-gray-300 rounded p-8 text-center cursor-pointer hover:border-gray-400 transition">
                <input type="file" accept="image/*" @change="uploadVoucher" class="hidden" :disabled="uploading">
                <i class="fas fa-upload text-2xl text-gray-400 mb-3"></i>
                <p class="font-medium" x-text="uploading ? 'Subiendo...' : 'Subir Comprobante de Pago'"></p>
                <p class="text-sm text-gray-500 mt-1">Haz clic o arrastra tu captura aquí</p>
            </label>
        </div>
    </div>

    <!-- Step 3: Confirmation -->
    <div x-show="step === 2" x-transition class="text-center py-12">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-2xl text-green-600"></i>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight mb-4">¡Pedido Recibido!</h1>
        <p class="text-gray-600 mb-2">Tu comprobante ha sido enviado para validación</p>
        <p class="text-sm text-gray-500 mb-8">Número de pedido: <span class="font-mono font-medium" x-text="orderNumber"></span></p>

        <div class="bg-stone-50 p-6 rounded max-w-md mx-auto mb-8 text-left">
            <h3 class="font-medium mb-2">¿Qué sigue?</h3>
            <ol class="text-sm text-gray-600 space-y-2">
                <li>1. Validaremos tu comprobante de pago</li>
                <li>2. Te notificaremos por correo cuando sea confirmado</li>
                <li>3. Prepararemos tu pedido para envío</li>
            </ol>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a :href="'/pedido/' + orderId" class="px-6 py-3 border border-gray-200 rounded font-medium hover:bg-gray-50 transition">
                Ver Estado del Pedido
            </a>
            <a href="{{ route('landing') }}" class="btn-primary rounded">Seguir Comprando</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function checkoutPage() {
    return {
        step: 0,
        loading: false,
        uploading: false,
        cart: [],
        orderId: null,
        orderNumber: '',
        form: {
            customer_name: '',
            customer_email: '',
            customer_phone: '',
            shipping_address: '',
            payment_method: 'yape'
        },
        init() {
            this.cart = JSON.parse(localStorage.getItem('cart') || '[]');
            if (this.cart.length === 0) {
                window.location.href = '{{ route("cart") }}';
            }
        },
        getTotal() {
            return this.cart.reduce((sum, item) => sum + (item.current_price * item.quantity), 0);
        },
        async submitOrder() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("checkout.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.form,
                        cart: this.cart
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.orderId = data.order_id;
                    this.orderNumber = data.order_number;
                    this.step = 1;
                } else {
                    alert(data.message || 'Error al procesar el pedido');
                }
            } catch (e) {
                alert('Error de conexión');
            }
            this.loading = false;
        },
        async uploadVoucher(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.uploading = true;
            const formData = new FormData();
            formData.append('voucher', file);

            try {
                const response = await fetch(`/pago/${this.orderId}/voucher`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    localStorage.removeItem('cart');
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    this.step = 2;
                } else {
                    alert(data.message || 'Error al subir comprobante');
                }
            } catch (e) {
                alert('Error de conexión');
            }
            this.uploading = false;
        }
    }
}
</script>
@endpush
