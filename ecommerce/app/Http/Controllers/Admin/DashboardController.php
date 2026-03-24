<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Order, Inventory, InventoryMovement};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending_validation')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->count(),
            'total_revenue' => Order::where('status', 'confirmed')->sum('total'),
            'low_stock_count' => Inventory::whereRaw('stock <= min_stock')->count(),
        ];

        $recentOrders = Order::with('items')->latest()->take(10)->get();
        $lowStockProducts = Product::with('inventory')
            ->whereHas('inventory', fn($q) => $q->whereRaw('stock <= min_stock'))
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts'));
    }
}
