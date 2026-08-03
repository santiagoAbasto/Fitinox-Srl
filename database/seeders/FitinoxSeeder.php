<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FitinoxSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@fitinox.com'], [
            'name' => 'Administrador Fitinox', 'password' => Hash::make('Fitinox2026!'), 'role' => 'super_admin',
        ]);

        foreach ([
            ['site_name', 'Fitinox'], ['email_sales', 'ventas@fittinox.com.ar'],
            ['email_quality', 'calidad@fittinox.com.ar'], ['email_warehouse', 'deposito@fittinox.com.ar'],
            ['email_admin', 'administracion@fittinox.com.ar'],
            ['phone', '+54 (011) 4738-5562 / +54 (011) 4764-2389 / 6647/8'],
            ['whatsapp', '54 9 11 4073-2016'],
            ['address', 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina'],
            ['copyright', '© Copyright 2025 Fitinox S.R.L. Todos los derechos reservados'],
        ] as [$key, $value]) {
            Setting::updateOrCreate(['key' => $key], compact('value'));
        }

        Page::updateOrCreate(['slug' => 'inicio'], [
            'title' => 'Inicio', 'template' => 'home',
            'hero_title' => 'Componentes para sistemas de control y condiciones de fluidos',
            'hero_image' => 'figma/about.png',
            'content' => 'Nuestra Empresa es una organización pyme que se desarrolla en dos aspectos, uno industrial dedicado a la fabricación de conexiones, válvulas aguja y válvulas múltiples y otro comercial dedicado a la venta de materiales para la conducción de fluidos.',
            'sections' => [
                'hero_button_label' => 'VER PRODUCTOS', 'products_title' => 'Productos', 'products_button_label' => 'VER TODOS',
                'nav_about' => 'Nosotros', 'nav_products' => 'Productos', 'nav_downloads' => 'Descargas', 'nav_quality' => 'Calidad',
                'nav_videos' => 'Videos', 'nav_distributors' => 'Distribuidores', 'nav_quote' => 'Presupuesto', 'nav_contact' => 'Contacto',
                'about_title' => '¿Quienes somos?', 'about_image' => 'figma/about.png', 'about_button_label' => 'MAS INFO', 'featured_title' => 'Productos destacados',
                'industry_title' => 'Oil & Gas', 'industry_text' => 'Conocé cómo nuestras soluciones acompañan a la industria energética con tecnología, precisión y experiencia.',
                'industry_image' => 'figma/oil-gas.png', 'industry_button_label' => 'MAS INFO', 'newsletter_title' => 'Suscribite al Newsletter',
                'social_title' => 'Seguinos en', 'client_label' => 'CLIENTES',
            ],
        ]);

        $categories = [
            ['Accesorios para tubos', 'accesorios-para-tubos', 'category-tubes.png'],
            ['Válvulas para instrumentación', 'valvulas-para-instrumentacion', 'category-valves.png'],
            ['Accesorios para caños', 'accesorios-para-canos', 'category-pipes.png'],
            ['Conexiones alta presión 20K/30k', 'conexiones-alta-presion', 'category-pressure.png'],
        ];
        foreach ($categories as $i => [$name, $slug, $image]) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'image' => "figma/$image", 'is_featured' => true, 'sort_order' => $i,
            ]);
        }

        $category = Category::where('slug', 'accesorios-para-tubos')->first();
        foreach ([
            ['Recto Macho', 'recto-macho', 'product-recto.png'],
            ['Codo Macho', 'codo-macho', 'product-codo.png'],
            ['T Unión de Tubos', 't-union-de-tubos', 'product-t-union.png'],
            ['T Hembra Lateral', 't-hembra-lateral', 'product-t-hembra.png'],
        ] as $i => [$name, $slug, $image]) {
            Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $category->id, 'name' => $name, 'main_image' => "figma/$image",
                'is_featured' => true, 'sort_order' => $i,
            ]);
        }
    }
}
