<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $slides = DB::table('home_slides')->orderBy('sort_order')->orderBy('id')->pluck('id');

        Schema::table('home_slides', function (Blueprint $table) {
            $table->string('sort_order', 3)->default('A')->change();
        });

        foreach ($slides as $index => $id) {
            DB::table('home_slides')->where('id', $id)->update([
                'sort_order' => $this->codeForIndex($index),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('home_slides')->update(['sort_order' => 0]);

        Schema::table('home_slides', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->change();
        });
    }

    private function codeForIndex(int $index): string
    {
        $group = intdiv($index, 27);
        $position = $index % 27;
        $prefix = chr(65 + $group);

        return $position === 0 ? $prefix : $prefix.chr(64 + $position);
    }
};
