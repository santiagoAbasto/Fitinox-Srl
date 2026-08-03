<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company')->nullable()->after('email');
            $table->string('phone')->nullable()->after('company');
            $table->string('tax_id')->nullable()->after('phone');
            $table->string('approval_status', 20)->default('approved')->after('is_active')->index();
        });

        Schema::create('client_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('status', 30)->default('received')->index();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('client_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('code')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('list_price', 14, 2)->nullable();
            $table->decimal('discount_percent', 6, 2)->default(0);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_order_items');
        Schema::dropIfExists('client_orders');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['company', 'phone', 'tax_id', 'approval_status']));
    }
};
