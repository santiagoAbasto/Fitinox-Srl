<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->char('hash', 64)->unique();
            $table->string('source_locale', 5)->default('ES');
            $table->string('target_locale', 5);
            $table->longText('source_text');
            $table->longText('translated_text');
            $table->timestamps();
            $table->index(['source_locale', 'target_locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
