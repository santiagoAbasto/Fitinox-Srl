<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('video_type', 20)->nullable()->after('gallery');
            $table->string('video_path')->nullable()->after('video_type');
            $table->string('youtube_url')->nullable()->after('video_path');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('detail')->nullable();
            $table->string('tube')->nullable();
            $table->string('thread')->nullable();
            $table->string('quality')->nullable();
            $table->decimal('list_price', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('final_price', 12, 2)->nullable();
            $table->integer('physical_stock')->nullable();
            $table->integer('transit_stock')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $this->seedProduct('recto-macho', 'Recto Macho', [
            ['200','1/8"',[['1/8"',14.69],['1/4"',15.11],['3/8"',15.45],['1/2"',22.80]]],
            ['400','1/4"',[['1/8"',13.33],['1/4"',13.58],['3/8"',13.91],['1/2"',16.96],['3/4"',21.55]]],
            ['500','5/16"',[['1/8"',19.22],['1/4"',16.15],['3/8"',20.63],['1/2"',24.58]]],
            ['600','3/8"',[['1/8"',16.61],['1/4"',14.32],['3/8"',15.29],['1/2"',17.49],['3/4"',23.56]]],
            ['810','1/2"',[['1/8"',21.16],['1/4"',20.37],['3/8"',21.32],['1/2"',20.30],['3/4"',27.49],['1"',36.80]]],
            ['1010','5/8"',[['1/4"',37.21],['3/8"',37.70],['1/2"',32.34],['3/4"',46.57]]],
            ['1210','3/4"',[['1/4"',35.00],['3/8"',35.74],['1/2"',35.27],['3/4"',35.44],['1"',51.49]]],
            ['1610','1"',[['1/2"',63.29],['3/4"',59.02],['1"',59.62]]],
        ], '1');

        $this->seedProduct('codo-macho', 'Codo Macho', [
            ['200','1/8"',[['1/8"',22.52],['1/4"',25.09]]],
            ['400','1/4"',[['1/8"',20.97],['1/4"',19.27],['3/8"',22.75],['1/2"',25.32]]],
            ['500','5/16"',[['1/8"',24.26],['1/4"',21.69],['3/8"',33.73],['1/2"',40.56]]],
            ['600','3/8"',[['1/8"',25.71],['1/4"',21.44],['3/8"',25.78],['1/2"',25.78],['3/4"',43.52]]],
            ['810','1/2"',[['1/8"',35.27],['1/4"',31.00],['3/8"',32.71],['1/2"',28.44],['3/4"',38.48],['1"',49.25]]],
            ['1010','5/8"',[['3/8"',51.40],['1/2"',44.56]]],
            ['1210','3/4"',[['3/8"',63.43],['1/2"',46.34],['3/4"',46.34],['1"',57.10]]],
            ['1610','1"',[['1/2"',94.55],['3/4"',86.00],['1"',86.00]]],
        ], '2');
    }

    private function seedProduct(string $slug, string $detail, array $groups, string $type): void
    {
        $productId = DB::table('products')->where('slug', $slug)->value('id');
        if (!$productId) return;
        $order = 0;
        foreach ($groups as [$family, $tube, $threads]) {
            foreach ($threads as [$thread, $price]) {
                $threadCode = ['1/8"' => '2', '1/4"' => '4', '3/8"' => '6', '1/2"' => '8', '3/4"' => '12', '1"' => '16'][$thread];
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'code' => "AL-FSS-{$family}-{$type}-{$threadCode}",
                    'detail' => $detail,
                    'tube' => $tube,
                    'thread' => $thread,
                    'quality' => 'AISI 316',
                    'list_price' => $price,
                    'discount_percent' => 5,
                    'final_price' => round($price * .95, 2),
                    'sort_order' => $order++,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['video_type', 'video_path', 'youtube_url']));
    }
};
