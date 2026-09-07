<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_clients', function (Blueprint $table) {
            $table->id();
            $table->string('external_code', 80)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->text('email_raw')->nullable();
            $table->json('email_candidates')->nullable();
            $table->string('tax_id', 80)->nullable()->index();
            $table->timestamp('source_updated_at')->nullable();
            $table->char('source_hash', 64);
            $table->json('source_payload');
            $table->timestamp('last_received_at');
            $table->timestamps();
        });

        Schema::create('integration_articles', function (Blueprint $table) {
            $table->id();
            $table->string('external_code', 120)->unique();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->text('configuration')->nullable();
            $table->string('model')->nullable();
            $table->string('article_type', 30)->nullable();
            $table->string('manufacturing_type', 30)->nullable();
            $table->string('source_category')->nullable();
            $table->decimal('stock', 16, 3)->nullable();
            $table->string('unit', 30)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->char('source_hash', 64);
            $table->json('source_payload');
            $table->timestamp('last_received_at');
            $table->timestamps();
        });

        Schema::create('integration_sync_requests', function (Blueprint $table) {
            $table->id();
            $table->string('resource', 80);
            $table->string('idempotency_key', 160);
            $table->char('request_hash', 64);
            $table->unsignedSmallInteger('http_status');
            $table->json('response_payload');
            $table->timestamps();
            $table->unique(['resource', 'idempotency_key']);
        });

        Schema::create('integration_outbox_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('event_type', 80)->index();
            $table->string('aggregate_type', 80);
            $table->string('aggregate_id', 120);
            $table->json('payload');
            $table->timestamp('occurred_at');
            $table->timestamp('acknowledged_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('client_orders', function (Blueprint $table) {
            $table->string('external_reference')->nullable()->after('number')->index();
            $table->timestamp('integration_synced_at')->nullable()->after('external_reference');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('physical_stock', 16, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('physical_stock')->nullable()->change();
        });

        Schema::table('client_orders', function (Blueprint $table) {
            $table->dropColumn(['external_reference', 'integration_synced_at']);
        });

        Schema::dropIfExists('integration_outbox_events');
        Schema::dropIfExists('integration_sync_requests');
        Schema::dropIfExists('integration_articles');
        Schema::dropIfExists('integration_clients');
    }
};
