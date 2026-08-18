<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('church_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('artist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // concert | anniversary | wedding | camp_meeting | evangelism | album_launch | youth | other
            $table->string('type', 32)->default('concert');
            $table->string('venue')->nullable();
            $table->string('address')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('ticket_url')->nullable();
            $table->boolean('is_free')->default(true);
            $table->decimal('ticket_price', 12, 2)->nullable();
            $table->string('currency', 8)->default('MWK');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'starts_at']);
            $table->index('is_published');
        });

        Schema::create('copyright_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name');
            $table->string('reporter_email');
            $table->string('reporter_phone', 32)->nullable();
            $table->string('reporter_organization')->nullable();

            // Target — polymorphic (song / album / hymn / hymn_recording)
            $table->nullableMorphs('target');

            $table->text('claim');
            $table->string('evidence_path')->nullable();
            // received | under_review | valid | invalid | resolved | withdrawn
            $table->string('status', 32)->default('received');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copyright_reports');
        Schema::dropIfExists('events');
    }
};
