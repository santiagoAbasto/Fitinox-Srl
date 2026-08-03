<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_source', 30)->default('admin')->after('approval_status');
            $table->string('deactivation_source', 30)->nullable()->after('account_source');
        });

        DB::table('users')->where('email', 'admin@fitinox.com')->update(['role' => 'super_admin']);
        if (! DB::table('users')->where('role', 'super_admin')->exists()) {
            DB::table('users')->whereIn('role', ['admin', 'editor'])->orderBy('id')->limit(1)->update(['role' => 'super_admin']);
        }

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_subscribed')->default(true)->index();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_source', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('heading')->nullable();
            $table->longText('body');
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('status', 20)->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['account_source', 'deactivation_source']));
    }
};
