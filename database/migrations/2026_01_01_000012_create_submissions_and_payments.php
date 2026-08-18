<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Submitter contact (may differ from the account holder)
            $table->string('submitter_name');
            $table->string('submitter_email');
            $table->string('submitter_phone', 32)->nullable();

            // Music metadata (denormalized until approval, then materialized into songs)
            $table->string('song_title');
            $table->string('artist_name')->nullable();
            $table->string('group_name')->nullable();
            $table->string('choir_name')->nullable();
            $table->string('church_name')->nullable();
            $table->string('album_title')->nullable();
            $table->year('release_year')->nullable();
            $table->text('description')->nullable();

            $table->foreignId('language_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            // If artist/group already exists in the platform
            $table->foreignId('artist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('church_id')->nullable()->constrained()->nullOnDelete();

            // Copyright / permission
            $table->string('copyright_owner')->nullable();
            $table->string('rights_holder')->nullable();
            $table->string('permission_status', 32)->default('unknown');
            $table->boolean('owner_confirmation')->default(false);
            $table->boolean('platform_distribution_permission')->default(false);
            $table->boolean('accuracy_confirmation')->default(false);
            $table->text('copyright_notes')->nullable();

            // draft | awaiting_payment | payment_pending | paid | under_review
            // | approved | rejected | changes_requested | published | withdrawn
            $table->string('status', 32)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // When approved, the resulting song
            $table->foreignId('song_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        // Selected taxonomies for the pending submission (moved to song pivots on approval)
        Schema::create('submission_category', function (Blueprint $table) {
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['submission_id', 'category_id']);
        });

        Schema::create('submission_occasion', function (Blueprint $table) {
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occasion_id')->constrained()->cascadeOnDelete();
            $table->primary(['submission_id', 'occasion_id']);
        });

        Schema::create('submission_mood', function (Blueprint $table) {
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mood_id')->constrained()->cascadeOnDelete();
            $table->primary(['submission_id', 'mood_id']);
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            // audio | artwork | artist_image | permission_document | other
            $table->string('kind', 32);
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'kind']);
        });

        Schema::create('submission_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            // approved | rejected | changes_requested | note
            $table->string('action', 32);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polymorphic — payment can be for a submission, promotion, ad campaign, etc.
            $table->nullableMorphs('payable');

            // pending | processing | successful | failed | cancelled | refunded
            $table->string('status', 32)->default('pending');
            // paychangu | manual | credit
            $table->string('provider', 32)->default('paychangu');

            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('MWK');

            $table->string('provider_reference')->nullable()->index();
            $table->string('checkout_url')->nullable();
            $table->json('provider_payload')->nullable();
            $table->json('provider_response')->nullable();

            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            // initiate | callback | verify | refund | webhook
            $table->string('event_type', 32);
            $table->string('status', 32);
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['payment_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('submission_reviews');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submission_mood');
        Schema::dropIfExists('submission_occasion');
        Schema::dropIfExists('submission_category');
        Schema::dropIfExists('submissions');
    }
};
