<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandSetting;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function edit()
    {
        $brand = BrandSetting::getSettings();
        return view('admin.brand.edit', compact('brand'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'primary_color' => 'required|string',
            'yape_number' => 'nullable|string|max:20',
            'plin_number' => 'nullable|string|max:20',
            'about_text' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $brand = BrandSetting::getSettings();
        $brand->update($request->all());

        return back()->with('success', 'Configuración guardada');
    }
}
