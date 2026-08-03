<?php

use App\Support\OrderCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'products'] as $tableName) {
            $ids = DB::table($tableName)->orderBy('sort_order')->orderBy('id')->pluck('id');
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('sort_order', 3)->default('A')->change();
            });
            foreach ($ids as $index => $id) {
                DB::table($tableName)->where('id', $id)->update(['sort_order' => OrderCode::forIndex($index)]);
            }
        }
    }

    public function down(): void
    {
        foreach (['categories', 'products'] as $tableName) {
            DB::table($tableName)->update(['sort_order' => 0]);
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->change();
            });
        }
    }
};
