<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Category, ProductImage, Inventory, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'primaryImage', 'inventory'])->latest()->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:inventory,sku',
            'images.*' => 'image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . Str::random(5),
                'description' => $request->description,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'sale_start' => $request->sale_price ? now() : null,
                'sale_end' => $request->sale_price ? now()->addYear() : null,
                'category_id' => $request->category_id,
                'is_active' => $request->has('is_active'),
                'is_featured' => $request->has('is_featured'),
            ]);

            // Create inventory
            Inventory::create([
                'product_id' => $product->id,
                'stock' => $request->stock,
                'sku' => $request->sku ?? 'SKU-' . strtoupper(Str::random(8)),
                'min_stock' => $request->min_stock ?? 5,
            ]);

            // Initial inventory movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $request->stock,
                'stock_before' => 0,
                'stock_after' => $request->stock,
                'notes' => 'Stock inicial',
                'user_id' => auth()->id(),
            ]);

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $i => $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $path,
                        'position' => $i,
                        'is_primary' => $i === 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Producto creado');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear producto: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'inventory']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'sale_start' => $request->sale_price ? ($product->sale_start ?? now()) : null,
            'sale_end' => $request->sale_price ? ($product->sale_end ?? now()->addYear()) : null,
            'category_id' => $request->category_id,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
        ]);

        if ($request->hasFile('images')) {
            $maxPos = $product->images()->max('position') ?? -1;
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                    'position' => ++$maxPos,
                    'is_primary' => false,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado');
    }

    public function deleteImage($imageId)
    {
        $image = ProductImage::findOrFail($imageId);
        $image->delete();
        return response()->json(['success' => true]);
    }
}
