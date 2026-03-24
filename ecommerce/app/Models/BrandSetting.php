<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandSetting extends Model
{
    protected $fillable = [
        'store_name', 'primary_color', 'secondary_color', 'logo_url',
        'yape_number', 'yape_qr_url', 'plin_number', 'plin_qr_url',
        'about_text', 'contact_email', 'contact_phone', 'address',
        'facebook_url', 'instagram_url', 'whatsapp_number'
    ];

    public static function getSettings()
    {
        return self::first() ?? self::create(['store_name' => 'Mi Tienda']);
    }
}
