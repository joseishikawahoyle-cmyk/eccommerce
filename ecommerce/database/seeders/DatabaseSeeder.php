<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@tienda.pe',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Brand Settings
        DB::table('brand_settings')->insert([
            'store_name' => 'Artesanías Perú',
            'primary_color' => '220 20% 10%',
            'secondary_color' => '0 0% 96%',
            'yape_number' => '987654321',
            'plin_number' => '987654321',
            'about_text' => 'Somos una tienda dedicada a promover el arte y la cultura peruana a través de productos artesanales de alta calidad. Cada pieza cuenta una historia y representa la riqueza de nuestras tradiciones.',
            'contact_email' => 'contacto@artesaniasperu.pe',
            'contact_phone' => '01 234 5678',
            'address' => 'Av. Artesanos 123, Miraflores, Lima',
            'whatsapp_number' => '51987654321',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Categories
        $categories = [
            ['name' => 'Bolsos', 'slug' => 'bolsos', 'description' => 'Bolsos de cuero y tela artesanal'],
            ['name' => 'Accesorios', 'slug' => 'accesorios', 'description' => 'Accesorios únicos y elegantes'],
            ['name' => 'Decoración', 'slug' => 'decoracion', 'description' => 'Piezas decorativas para el hogar'],
            ['name' => 'Textiles', 'slug' => 'textiles', 'description' => 'Textiles tradicionales peruanos'],
            ['name' => 'Joyería', 'slug' => 'joyeria', 'description' => 'Joyería artesanal de plata'],
            ['name' => 'Cerámica', 'slug' => 'ceramica', 'description' => 'Cerámica hecha a mano'],
        ];

        foreach ($categories as $i => $cat) {
            DB::table('categories')->insert(array_merge($cat, [
                'position' => $i,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Products with inventory
        $products = [
            [
                'name' => 'Bolso de Cuero Premium',
                'slug' => 'bolso-cuero-premium',
                'description' => 'Bolso elegante de cuero genuino con acabados artesanales. Perfecto para el uso diario.',
                'price' => 299.00,
                'sale_price' => 249.00,
                'category_id' => 1,
                'is_featured' => true,
                'image' => 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg',
                'stock' => 15,
                'sku' => 'BOL-001',
            ],
            [
                'name' => 'Cartera Minimalista',
                'slug' => 'cartera-minimalista',
                'description' => 'Cartera compacta con diseño minimalista. Ideal para llevar lo esencial.',
                'price' => 89.00,
                'sale_price' => null,
                'category_id' => 2,
                'is_featured' => true,
                'image' => 'https://images.pexels.com/photos/167703/pexels-photo-167703.jpeg',
                'stock' => 25,
                'sku' => 'ACC-001',
            ],
            [
                'name' => 'Jarrón Decorativo Artesanal',
                'slug' => 'jarron-decorativo-artesanal',
                'description' => 'Jarrón de cerámica artesanal hecho a mano. Pieza única para decoración.',
                'price' => 159.00,
                'sale_price' => 129.00,
                'category_id' => 3,
                'is_featured' => true,
                'image' => 'https://images.unsplash.com/photo-1643569556871-91ec60671ed7',
                'stock' => 8,
                'sku' => 'DEC-001',
            ],
            [
                'name' => 'Set de Tazas Artesanales',
                'slug' => 'set-tazas-artesanales',
                'description' => 'Set de 2 tazas de cerámica hechas a mano. Perfectas para el café de la mañana.',
                'price' => 79.00,
                'sale_price' => null,
                'category_id' => 6,
                'is_featured' => false,
                'image' => 'https://images.unsplash.com/photo-1605714117967-9fe201ddfe9d',
                'stock' => 20,
                'sku' => 'CER-001',
            ],
            [
                'name' => 'Manta Tejida Alpaca',
                'slug' => 'manta-tejida-alpaca',
                'description' => 'Manta tejida a mano con fibra de alpaca 100% natural.',
                'price' => 349.00,
                'sale_price' => 299.00,
                'category_id' => 4,
                'is_featured' => true,
                'image' => 'https://images.pexels.com/photos/6032280/pexels-photo-6032280.jpeg',
                'stock' => 10,
                'sku' => 'TEX-001',
            ],
            [
                'name' => 'Aretes de Plata Andinos',
                'slug' => 'aretes-plata-andinos',
                'description' => 'Aretes de plata 950 con diseño inspirado en la iconografía andina.',
                'price' => 129.00,
                'sale_price' => null,
                'category_id' => 5,
                'is_featured' => true,
                'image' => 'https://images.pexels.com/photos/1191531/pexels-photo-1191531.jpeg',
                'stock' => 30,
                'sku' => 'JOY-001',
            ],
            [
                'name' => 'Bolso Tote de Tela',
                'slug' => 'bolso-tote-tela',
                'description' => 'Bolso tote de tela resistente con estampado artesanal. Eco-friendly.',
                'price' => 59.00,
                'sale_price' => null,
                'category_id' => 1,
                'is_featured' => false,
                'image' => 'https://images.pexels.com/photos/5864601/pexels-photo-5864601.jpeg',
                'stock' => 50,
                'sku' => 'BOL-002',
            ],
            [
                'name' => 'Lámpara de Mesa Cerámica',
                'slug' => 'lampara-mesa-ceramica',
                'description' => 'Lámpara de mesa con base de cerámica artesanal.',
                'price' => 189.00,
                'sale_price' => 159.00,
                'category_id' => 3,
                'is_featured' => false,
                'image' => 'https://images.pexels.com/photos/1112598/pexels-photo-1112598.jpeg',
                'stock' => 12,
                'sku' => 'DEC-002',
            ],
        ];

        foreach ($products as $p) {
            $productId = DB::table('products')->insertGetId([
                'name' => $p['name'],
                'slug' => $p['slug'],
                'description' => $p['description'],
                'price' => $p['price'],
                'sale_price' => $p['sale_price'],
                'sale_start' => $p['sale_price'] ? now() : null,
                'sale_end' => $p['sale_price'] ? now()->addYear() : null,
                'category_id' => $p['category_id'],
                'is_active' => true,
                'is_featured' => $p['is_featured'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image_url' => $p['image'],
                'position' => 0,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inventory')->insert([
                'product_id' => $productId,
                'stock' => $p['stock'],
                'reserved' => 0,
                'min_stock' => 5,
                'sku' => $p['sku'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inventory_movements')->insert([
                'product_id' => $productId,
                'type' => 'in',
                'quantity' => $p['stock'],
                'stock_before' => 0,
                'stock_after' => $p['stock'],
                'notes' => 'Stock inicial',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Banners
        $banners = [
            ['title' => 'Cyber Days', 'subtitle' => 'Hasta 50% de descuento', 'image_url' => 'https://images.pexels.com/photos/7302793/pexels-photo-7302793.jpeg', 'link' => '#productos', 'size' => 'large', 'position' => 0],
            ['title' => 'Nueva Colección', 'subtitle' => 'Descubre lo nuevo', 'image_url' => 'https://images.pexels.com/photos/13221796/pexels-photo-13221796.jpeg', 'link' => '#productos', 'size' => 'small', 'position' => 1],
            ['title' => 'Envío Gratis', 'subtitle' => 'En compras +S/150', 'image_url' => 'https://images.pexels.com/photos/3817497/pexels-photo-3817497.jpeg', 'link' => '#productos', 'size' => 'small', 'position' => 2],
        ];

        foreach ($banners as $banner) {
            DB::table('banners')->insert(array_merge($banner, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Landing Sections
        $sections = [
            ['name' => 'hero', 'title' => 'Artesanía Peruana Auténtica', 'content' => 'Descubre piezas únicas', 'position' => 0],
            ['name' => 'about', 'title' => 'Nuestra Historia', 'content' => 'Más de 10 años de tradición', 'position' => 1],
            ['name' => 'features', 'title' => 'Por qué elegirnos', 'content' => null, 'position' => 2],
            ['name' => 'products', 'title' => 'Productos Destacados', 'content' => null, 'position' => 3],
            ['name' => 'testimonials', 'title' => 'Testimonios', 'content' => null, 'position' => 4],
            ['name' => 'contact', 'title' => 'Contáctanos', 'content' => null, 'position' => 5],
        ];

        foreach ($sections as $section) {
            DB::table('landing_sections')->insert(array_merge($section, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Testimonials
        $testimonials = [
            ['name' => 'María García', 'location' => 'Lima', 'content' => 'Excelente calidad. El pago con Yape fue muy fácil.', 'rating' => 5],
            ['name' => 'Carlos Rodríguez', 'location' => 'Arequipa', 'content' => 'Me encantó la manta de alpaca. Muy recomendado.', 'rating' => 5],
            ['name' => 'Ana López', 'location' => 'Trujillo', 'content' => 'Los aretes de plata son preciosos. Hechos a mano con cuidado.', 'rating' => 5],
        ];

        foreach ($testimonials as $i => $t) {
            DB::table('testimonials')->insert(array_merge($t, ['position' => $i, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }
    }
}
