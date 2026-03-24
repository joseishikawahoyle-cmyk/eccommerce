<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    
    protected $fillable = ['product_id', 'stock', 'reserved', 'min_stock', 'sku'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableAttribute()
    {
        return $this->stock - $this->reserved;
    }

    public function isLowStock()
    {
        return $this->stock <= $this->min_stock;
    }
}
