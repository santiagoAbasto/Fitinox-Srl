<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')->where('image', 'like', 'assets/figma/%')->update([
            'image' => DB::raw("REPLACE(image, 'assets/figma/', 'figma/')"),
        ]);

        DB::table('products')->where('main_image', 'like', 'assets/figma/%')->update([
            'main_image' => DB::raw("REPLACE(main_image, 'assets/figma/', 'figma/')"),
        ]);
    }

    public function down(): void
    {
        DB::table('categories')->where('image', 'like', 'figma/%')->update([
            'image' => DB::raw("CONCAT('assets/', image)"),
        ]);

        DB::table('products')->where('main_image', 'like', 'figma/%')->update([
            'main_image' => DB::raw("CONCAT('assets/', main_image)"),
        ]);
    }
};
