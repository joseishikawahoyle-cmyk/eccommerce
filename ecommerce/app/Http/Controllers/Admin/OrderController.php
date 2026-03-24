<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Inventory, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'validator']);
        return view('admin.orders.show', compact('order'));
    }

    public function validate(Request $request, Order $order)
    {
        $request->validate(['action' => 'required|in:approve,reject']);

        if ($order->status !== 'pending_validation') {
            return back()->with('error', 'Este pedido ya fue procesado');
        }

        try {
            DB::beginTransaction();

            if ($request->action === 'approve') {
                // Confirm stock reduction
                foreach ($order->items as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)->first();
                    if ($inventory) {
                        $stockBefore = $inventory->stock;
                        $inventory->stock -= $item->quantity;
                        $inventory->reserved -= $item->quantity;
                        $inventory->save();

                        InventoryMovement::create([
                            'product_id' => $item->product_id,
                            'type' => 'out',
                            'quantity' => $item->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $inventory->stock,
                            'reference' => $order->order_number,
                            'notes' => 'Venta confirmada',
                            'user_id' => auth()->id(),
                        ]);
                    }
                }

                $order->update([
                    'status' => 'confirmed',
                    'validated_at' => now(),
                    'validated_by' => auth()->id(),
                ]);

                $message = 'Pedido confirmado exitosamente';
            } else {
                // Release reserved stock
                foreach ($order->items as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)->first();
                    if ($inventory) {
                        $inventory->reserved -= $item->quantity;
                        $inventory->save();

                        InventoryMovement::create([
                            'product_id' => $item->product_id,
                            'type' => 'release',
                            'quantity' => $item->quantity,
                            'stock_before' => $inventory->stock,
                            'stock_after' => $inventory->stock,
                            'reference' => $order->order_number,
                            'notes' => 'Pedido rechazado - stock liberado',
                            'user_id' => auth()->id(),
                        ]);
                    }
                }

                $order->update([
                    'status' => 'rejected',
                    'validated_at' => now(),
                    'validated_by' => auth()->id(),
                    'admin_notes' => $request->notes,
                ]);

                $message = 'Pedido rechazado';
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:processing,shipped,delivered,cancelled']);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Estado actualizado');
    }
}
