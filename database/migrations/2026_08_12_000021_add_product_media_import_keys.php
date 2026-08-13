<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'photo_import_key')) {
                $table->string('photo_import_key')->nullable()->after('main_image')->index();
            }
            if (!Schema::hasColumn('products', 'video_import_key')) {
                $table->string('video_import_key')->nullable()->after('video_path')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = array_values(array_filter(['photo_import_key', 'video_import_key'], fn ($column) => Schema::hasColumn('products', $column)));
            if ($columns) $table->dropColumn($columns);
        });
    }
};
