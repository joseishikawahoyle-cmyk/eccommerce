<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'payment_method', 'subtotal', 'shipping_cost', 'total',
        'status', 'voucher_url', 'voucher_uploaded_at', 'validated_at', 'validated_by',
        'notes', 'admin_notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'voucher_uploaded_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public static function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending_payment' => 'Pendiente de Pago',
            'pending_validation' => 'En Validación',
            'confirmed' => 'Confirmado',
            'processing' => 'En Proceso',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            'rejected' => 'Rechazado',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending_payment' => 'yellow',
            'pending_validation' => 'blue',
            'confirmed' => 'green',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'gray',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
