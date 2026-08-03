<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('company')->nullable();
            $table->text('message');
            $table->string('status', 30)->default('new')->index();
            $table->timestamps();
        });

        DB::table('pages')->updateOrInsert(
            ['slug' => 'contacto'],
            [
                'title' => 'Contacto',
                'template' => 'contact',
                'hero_title' => 'Contacto',
                'hero_image' => 'figma/contact/hero.png',
                'seo_title' => 'Contacto | Fitinox',
                'seo_description' => 'Contactate con Fitinox.',
                'is_published' => true,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        DB::table('pages')->where('slug', 'contacto')->delete();
    }
};
