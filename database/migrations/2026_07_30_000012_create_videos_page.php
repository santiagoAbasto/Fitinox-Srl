<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('thumbnail');
            $table->string('media_type', 20)->default('youtube');
            $table->string('video_path')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('sort_order', 3)->default('A');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('pages')->updateOrInsert(
            ['slug' => 'videos'],
            [
                'title' => 'Videos',
                'template' => 'videos',
                'hero_title' => 'Videos',
                'hero_image' => 'figma/videos/hero.png',
                'seo_title' => 'Videos | Fitinox',
                'seo_description' => 'Videos institucionales, instructivos y novedades de Fitinox.',
                'is_published' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([
            ['Instructivos de Instalacion de nuestras conexiones', 'figma/videos/video-1.png', 'A'],
            ['Junto a nuestros distribuidores', 'figma/videos/video-2.png', 'AA'],
            ['Recibimos la visita del Intendente de San Martin el Sr. Gabriel Katopodis', 'figma/videos/video-3.png', 'AB'],
        ] as [$title, $thumbnail, $order]) {
            DB::table('videos')->insert([
                'title' => $title,
                'thumbnail' => $thumbnail,
                'media_type' => 'youtube',
                'sort_order' => $order,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
        DB::table('pages')->where('slug', 'videos')->delete();
    }
};
