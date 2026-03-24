<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Inventory, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with(['product.category', 'product.primaryImage'])
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->paginate(20);

        return view('admin.inventory.index', compact('inventory'));
    }

    public function adjust(Request $request, $productId)
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $inventory = Inventory::where('product_id', $productId)->firstOrFail();

        try {
            DB::beginTransaction();

            $stockBefore = $inventory->stock;

            if ($request->type === 'in') {
                $inventory->stock += $request->quantity;
            } elseif ($request->type === 'out') {
                if ($inventory->stock < $request->quantity) {
                    throw new \Exception('Stock insuficiente');
                }
                $inventory->stock -= $request->quantity;
            } else {
                $inventory->stock = $request->quantity;
            }

            $inventory->save();

            InventoryMovement::create([
                'product_id' => $productId,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $inventory->stock,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Inventario actualizado');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function movements($productId)
    {
        $product = Product::with('inventory')->findOrFail($productId);
        $movements = InventoryMovement::where('product_id', $productId)
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('admin.inventory.movements', compact('product', 'movements'));
    }

    public function lowStock()
    {
        $inventory = Inventory::with(['product.category', 'product.primaryImage'])
            ->whereRaw('stock <= min_stock')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get();

        return view('admin.inventory.low-stock', compact('inventory'));
    }
}
