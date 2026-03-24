<?php

namespace App\Http\Controllers;

use App\Models\{BrandSetting, Product, Category, Banner, Testimonial, Order, OrderItem, Inventory, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function index()
    {
        $brand = BrandSetting::getSettings();
        $banners = Banner::active()->orderBy('position')->get();
        $categories = Category::where('is_active', true)->orderBy('position')->withCount('activeProducts')->get();
        $featuredProducts = Product::with(['primaryImage', 'inventory', 'category'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->take(8)
            ->get();
        $testimonials = Testimonial::active()->get();

        return view('landing.index', compact('brand', 'banners', 'categories', 'featuredProducts', 'testimonials'));
    }

    public function products(Request $request)
    {
        $brand = BrandSetting::getSettings();
        $categories = Category::where('is_active', true)->orderBy('position')->get();
        
        $query = Product::with(['primaryImage', 'inventory', 'category'])->where('is_active', true);

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $products = $query->paginate(12);

        return view('landing.products', compact('brand', 'categories', 'products'));
    }

    public function product($slug)
    {
        $brand = BrandSetting::getSettings();
        $product = Product::with(['images', 'inventory', 'category'])->where('slug', $slug)->firstOrFail();
        $relatedProducts = Product::with(['primaryImage', 'inventory'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('landing.product', compact('brand', 'product', 'relatedProducts'));
    }

    public function cart()
    {
        $brand = BrandSetting::getSettings();
        return view('landing.cart', compact('brand'));
    }

    public function checkout()
    {
        $brand = BrandSetting::getSettings();
        return view('landing.checkout', compact('brand'));
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:yape,plin',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($request->cart as $item) {
                $product = Product::with(['primaryImage', 'inventory'])->findOrFail($item['id']);
                
                if ($product->available_stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$product->name}");
                }

                $price = $product->current_price;
                $itemTotal = $price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->primaryImage?->image_url,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'total' => $itemTotal,
                ];

                // Reserve stock
                $inventory = $product->inventory;
                $stockBefore = $inventory->stock;
                $inventory->reserved += $item['quantity'];
                $inventory->save();

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'reserve',
                    'quantity' => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $inventory->stock,
                    'notes' => 'Reserva para pedido',
                ]);
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending_payment',
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function payment($orderId)
    {
        $brand = BrandSetting::getSettings();
        $order = Order::with('items')->findOrFail($orderId);

        return view('landing.payment', compact('brand', 'order'));
    }

    public function uploadVoucher(Request $request, $orderId)
    {
        $request->validate(['voucher' => 'required|image|max:5120']);

        $order = Order::findOrFail($orderId);

        if ($order->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Pedido ya procesado'], 400);
        }

        $path = $request->file('voucher')->store('vouchers', 'public');

        $order->update([
            'voucher_url' => $path,
            'voucher_uploaded_at' => now(),
            'status' => 'pending_validation',
        ]);

        return response()->json(['success' => true, 'message' => 'Comprobante subido']);
    }

    public function orderStatus($orderId)
    {
        $brand = BrandSetting::getSettings();
        $order = Order::with('items')->findOrFail($orderId);

        return view('landing.order-status', compact('brand', 'order'));
    }
}
