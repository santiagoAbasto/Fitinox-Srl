<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('client_orders', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('status');
            $table->string('delivery_method', 30)->nullable()->after('notes');
            $table->string('transport_name')->nullable()->after('delivery_method');
            $table->string('transport_address')->nullable()->after('transport_name');
            $table->string('transport_phone')->nullable()->after('transport_address');
            $table->string('delivery_destination')->nullable()->after('transport_phone');
            $table->string('attachment_path')->nullable()->after('delivery_destination');
        });
    }

    public function down(): void
    {
        Schema::table('client_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_date', 'delivery_method', 'transport_name', 'transport_address', 'transport_phone', 'delivery_destination', 'attachment_path']);
        });
        Schema::dropIfExists('client_price_lists');
    }
};
