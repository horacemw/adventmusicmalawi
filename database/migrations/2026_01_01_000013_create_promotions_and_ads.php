<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // song_feature | artist_feature | group_feature | album_feature | homepage_hero
            $table->string('placement', 32);
            $table->unsignedInteger('duration_days');
            $table->decimal('price', 12, 2);
            $table->string('currency', 8)->default('MWK');
            $table->json('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_package_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('promotable');
            // pending_payment | scheduled | active | expired | cancelled
            $table->string('status', 32)->default('pending_payment');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::create('advertisement_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('advertiser_name');
            $table->string('advertiser_email')->nullable();
            $table->string('advertiser_phone', 32)->nullable();
            $table->text('description')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('currency', 8)->default('MWK');
            // draft | pending_review | active | paused | completed | rejected
            $table->string('status', 32)->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image_path')->nullable();
            // banner_top | banner_side | player_overlay | homepage_card
            $table->string('placement', 32);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('advertisement_campaigns');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('promotion_packages');
    }
};
