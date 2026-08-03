<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('company')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->timestamps();
        });
        Schema::create('quote_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('category_name')->nullable();
            $table->string('code')->nullable();
            $table->string('tube')->nullable();
            $table->string('thread')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });
        DB::table('pages')->updateOrInsert(
            ['slug' => 'presupuesto'],
            [
                'title' => 'Solicitud de presupuesto',
                'template' => 'quote',
                'hero_title' => 'Solicitud de presupuesto',
                'hero_image' => 'figma/quote/hero.png',
                'seo_title' => 'Solicitud de presupuesto | Fitinox',
                'seo_description' => 'Solicitá un presupuesto personalizado de productos Fitinox.',
                'is_published' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_items');
        Schema::dropIfExists('quote_requests');
        DB::table('pages')->where('slug', 'presupuesto')->delete();
    }
};
