<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('country')->default('Argentina');
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('marker_x')->default(50);
            $table->unsignedSmallInteger('marker_y')->default(50);
            $table->string('sort_order', 3)->default('A');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('pages')->updateOrInsert(
            ['slug' => 'distribuidores'],
            [
                'title' => 'Distribuidores',
                'template' => 'distributors',
                'hero_title' => 'Distribuidores',
                'hero_image' => 'figma/distributors/hero.png',
                'seo_title' => 'Distribuidores | Fitinox',
                'seo_description' => 'Encontrá distribuidores oficiales de Fitinox en Argentina.',
                'is_published' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([
            ['CEM PROVISIONES INDUSTRIALES de Claudio Macoratti', 'Av. San Martín 3148, San Lorenzo- Santa Fe', '+54 3476-426900', 'cem@cemprovin.com.ar', -32.7503, -60.7358, 48, 20, 'A'],
            ['CLAUDIO COLEDANI', 'Radio Belgrano 3062-Barrio Intersindical, Salta', "0387-4243058\n0387-155-196032", 'claudiocoledani@yahoo.com.ar', -24.7821, -65.4232, 17, 44, 'AA'],
            ['INDAVE PATAGONICO S.A.', 'Rio Negro 1218, Neuquen', '+54 299 422-2780', 'info@indavepatagonico.com', -38.9516, -68.0591, 25, 77, 'AB'],
            ['INDAVE PATAGONICO S.A.', 'Avellaneda 612 , Bahía Blanca', '+54-291-4533466', 'rpuente@saippargentina.com.ar', -38.7196, -62.2724, 75, 77, 'AC'],
        ] as [$name, $address, $phone, $email, $lat, $lng, $x, $y, $order]) {
            DB::table('distributors')->insert([
                'country' => 'Argentina', 'name' => $name, 'address' => $address,
                'phone' => $phone, 'email' => $email, 'latitude' => $lat, 'longitude' => $lng,
                'marker_x' => $x, 'marker_y' => $y, 'sort_order' => $order, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('distributors');
        DB::table('pages')->where('slug', 'distribuidores')->delete();
    }
};
