<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->string('online_url')->nullable()->after('file');
            $table->string('sort_order', 3)->default('A')->change();
        });

        DB::table('pages')->updateOrInsert(
            ['slug' => 'descargas'],
            [
                'title' => 'Descargas',
                'template' => 'downloads',
                'hero_title' => 'Descargas',
                'hero_image' => 'figma/downloads/hero.png',
                'seo_title' => 'Descargas | Fitinox',
                'seo_description' => 'Catálogos y documentación técnica de Fitinox.',
                'is_published' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $items = [
            ['Catálogo General', '125kb', 'A'],
            ['Catálogo Accesorios para tuberías', '279kb', 'AA'],
            ['Catálogo de Válvulas para instrumentación', '289kb', 'AB'],
            ['Catálogo Accesorios para cañerías', '140kb', 'AC'],
            ['Catálogo Conexiones Alta presión para tuberías', '248kb', 'AD'],
        ];
        foreach ($items as [$title, $size, $order]) {
            DB::table('downloads')->updateOrInsert(
                ['title' => $title],
                [
                    'file' => '',
                    'format' => 'PDF',
                    'file_size' => $size,
                    'is_active' => true,
                    'sort_order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        DB::table('downloads')->whereIn('title', array_column($items, 0))->update(['is_active' => true]);
    }

    public function down(): void
    {
        DB::table('pages')->where('slug', 'descargas')->delete();
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropColumn('online_url');
            $table->unsignedInteger('sort_order')->default(0)->change();
        });
    }
};
