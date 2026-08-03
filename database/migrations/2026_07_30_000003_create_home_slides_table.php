<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('button_label')->nullable();
            $table->string('button_url')->default('#productos');
            $table->enum('media_type', ['image', 'video', 'youtube'])->default('image');
            $table->string('media_path')->nullable();
            $table->string('youtube_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $page = DB::table('pages')->where('slug', 'inicio')->first();
        if ($page) {
            $sections = json_decode($page->sections ?? '{}', true);
            DB::table('home_slides')->insert([
                'title' => $page->hero_title,
                'button_label' => $sections['hero_button_label'] ?? 'VER PRODUCTOS',
                'button_url' => '#productos',
                'media_type' => 'image',
                'media_path' => $page->hero_image,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slides');
    }
};
