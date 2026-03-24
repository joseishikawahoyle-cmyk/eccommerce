<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'sale_price',
        'sale_start', 'sale_end', 'category_id', 'is_active', 'is_featured'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getCurrentPriceAttribute()
    {
        if ($this->sale_price && $this->isOnSale()) {
            return $this->sale_price;
        }
        return $this->price;
    }

    public function isOnSale()
    {
        if (!$this->sale_price) return false;
        $now = now();
        if ($this->sale_start && $now < $this->sale_start) return false;
        if ($this->sale_end && $now > $this->sale_end) return false;
        return true;
    }

    public function getStockAttribute()
    {
        return $this->inventory?->stock ?? 0;
    }

    public function getAvailableStockAttribute()
    {
        $inv = $this->inventory;
        return $inv ? ($inv->stock - $inv->reserved) : 0;
    }
}
