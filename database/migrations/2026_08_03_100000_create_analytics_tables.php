<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('analytics_sessions', function (Blueprint $t) {
            $t->id(); $t->uuid('session_uuid')->unique(); $t->char('visitor_hash', 64)->index();
            $t->char('ip_hash', 64)->nullable()->index(); $t->string('ip_version', 4)->nullable();
            $t->timestamp('started_at')->index(); $t->timestamp('last_seen_at')->index(); $t->timestamp('ended_at')->nullable();
            $t->string('landing_path', 512)->nullable(); $t->string('exit_path', 512)->nullable();
            $t->string('referrer_host')->nullable(); $t->string('traffic_source', 32)->default('direct')->index();
            $t->string('utm_source')->nullable(); $t->string('utm_medium')->nullable(); $t->string('utm_campaign')->nullable();
            $t->string('utm_content')->nullable(); $t->string('utm_term')->nullable();
            $t->string('device_type', 24)->nullable()->index(); $t->string('browser', 48)->nullable(); $t->string('os', 48)->nullable();
            $t->char('country_code', 2)->nullable()->index(); $t->string('country_name')->nullable(); $t->string('city')->nullable();
            $t->decimal('latitude', 8, 4)->nullable(); $t->decimal('longitude', 8, 4)->nullable();
            $t->boolean('is_bot')->default(false)->index(); $t->unsignedInteger('page_views')->default(0); $t->unsignedInteger('duration_seconds')->default(0);
            $t->timestamps();
        });
        Schema::create('analytics_page_views', function (Blueprint $t) {
            $t->id(); $t->foreignId('analytics_session_id')->constrained()->cascadeOnDelete(); $t->uuid('event_uuid')->unique();
            $t->string('path', 512)->index(); $t->string('title')->nullable(); $t->string('referrer_path', 512)->nullable(); $t->string('next_path', 512)->nullable();
            $t->unsignedInteger('duration_seconds')->default(0); $t->unsignedSmallInteger('max_scroll_depth')->default(0);
            $t->unsignedSmallInteger('status_code')->nullable(); $t->unsignedInteger('server_response_ms')->nullable();
            $t->unsignedInteger('lcp_ms')->nullable(); $t->unsignedInteger('inp_ms')->nullable(); $t->decimal('cls', 8, 4)->nullable();
            $t->timestamp('created_at')->index(); $t->timestamp('updated_at')->nullable();
        });
        Schema::create('analytics_section_views', function (Blueprint $t) {
            $t->id(); $t->foreignId('analytics_session_id')->constrained()->cascadeOnDelete(); $t->uuid('event_uuid')->unique();
            $t->string('path', 512)->index(); $t->string('section_key')->index(); $t->unsignedSmallInteger('section_order')->nullable();
            $t->unsignedInteger('visible_ms')->default(0); $t->unsignedSmallInteger('max_visibility')->default(50);
            $t->string('previous_section')->nullable(); $t->string('next_section')->nullable(); $t->timestamp('created_at')->index(); $t->timestamp('updated_at')->nullable();
        });
        Schema::create('analytics_events', function (Blueprint $t) {
            $t->id(); $t->foreignId('analytics_session_id')->nullable()->constrained()->nullOnDelete(); $t->uuid('event_uuid')->unique();
            $t->string('event_name', 64)->index(); $t->string('path', 512)->nullable()->index(); $t->json('properties')->nullable();
            $t->boolean('is_bot')->default(false)->index(); $t->char('country_code', 2)->nullable()->index(); $t->timestamp('created_at')->index(); $t->timestamp('updated_at')->nullable();
        });
        Schema::create('analytics_conversions', function (Blueprint $t) {
            $t->id(); $t->foreignId('analytics_session_id')->nullable()->constrained()->nullOnDelete(); $t->uuid('event_uuid')->unique();
            $t->string('conversion_type', 64)->index(); $t->decimal('value', 14, 2)->nullable(); $t->char('currency', 3)->nullable(); $t->timestamp('created_at')->index(); $t->timestamp('updated_at')->nullable();
        });
        Schema::create('analytics_daily_summaries', function (Blueprint $t) {
            $t->id(); $t->date('date')->index(); $t->string('dimension', 32); $t->string('dimension_value', 512)->default('all');
            $t->unsignedBigInteger('sessions')->default(0); $t->unsignedBigInteger('visitors')->default(0); $t->unsignedBigInteger('page_views')->default(0);
            $t->unsignedBigInteger('events')->default(0); $t->unsignedBigInteger('conversions')->default(0); $t->unsignedBigInteger('bots')->default(0);
            $t->unsignedBigInteger('duration_seconds')->default(0); $t->timestamps(); $t->unique(['date', 'dimension', 'dimension_value'], 'analytics_summary_unique');
        });
        Schema::create('analytics_ip_reputation', function (Blueprint $t) {
            $t->id(); $t->char('ip_hash', 64)->unique(); $t->string('masked_ip', 64)->nullable(); $t->string('ip_version', 4)->nullable();
            $t->unsignedInteger('request_count')->default(0); $t->unsignedInteger('risk_score')->default(0); $t->boolean('is_allowed')->default(false); $t->boolean('is_blocked')->default(false)->index();
            $t->timestamp('blocked_until')->nullable(); $t->timestamp('last_seen_at')->nullable(); $t->timestamps();
        });
        Schema::create('analytics_blocked_requests', function (Blueprint $t) {
            $t->id(); $t->char('ip_hash', 64)->index(); $t->string('path', 512); $t->string('reason'); $t->char('country_code', 2)->nullable()->index(); $t->timestamp('created_at')->index();
        });
        Schema::create('analytics_bot_events', function (Blueprint $t) {
            $t->id(); $t->char('ip_hash', 64)->nullable()->index(); $t->string('path', 512)->nullable(); $t->string('bot_type', 64)->nullable();
            $t->unsignedSmallInteger('confidence')->default(0); $t->char('country_code', 2)->nullable()->index(); $t->json('signals')->nullable(); $t->timestamp('created_at')->index();
        });
        Schema::create('analytics_security_audits', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('action', 64); $t->char('subject_ip_hash', 64)->nullable();
            $t->json('metadata')->nullable(); $t->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        foreach (['analytics_security_audits','analytics_bot_events','analytics_blocked_requests','analytics_ip_reputation','analytics_daily_summaries','analytics_conversions','analytics_events','analytics_section_views','analytics_page_views','analytics_sessions'] as $table) Schema::dropIfExists($table);
    }
};
