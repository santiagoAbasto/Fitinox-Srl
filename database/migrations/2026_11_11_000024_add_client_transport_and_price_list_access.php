<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_view_price_lists')->default(true)->after('discount_percent');
            $table->string('default_transport_name', 120)->nullable()->after('can_view_price_lists');
            $table->string('default_transport_address', 255)->nullable()->after('default_transport_name');
            $table->string('default_transport_phone', 60)->nullable()->after('default_transport_address');
            $table->string('default_delivery_destination', 255)->nullable()->after('default_transport_phone');
        });

        Schema::table('integration_clients', function (Blueprint $table) {
            $table->string('transport_name', 120)->nullable()->after('tax_id');
            $table->string('transport_address', 255)->nullable()->after('transport_name');
            $table->string('transport_phone', 60)->nullable()->after('transport_address');
            $table->string('delivery_destination', 255)->nullable()->after('transport_phone');
        });
    }

    public function down(): void
    {
        Schema::table('integration_clients', function (Blueprint $table) {
            $table->dropColumn(['transport_name', 'transport_address', 'transport_phone', 'delivery_destination']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_view_price_lists', 'default_transport_name', 'default_transport_address', 'default_transport_phone', 'default_delivery_destination']);
        });
    }
};
