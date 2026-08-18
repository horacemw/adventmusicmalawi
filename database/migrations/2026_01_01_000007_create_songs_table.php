<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('church_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('language_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('lyrics')->nullable();

            $table->string('audio_path')->nullable();
            $table->string('audio_format', 16)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('audio_size_bytes')->nullable();
            $table->string('artwork_path')->nullable();

            $table->year('release_year')->nullable();
            $table->date('released_at')->nullable();

            // published | draft | pending | rejected | withdrawn | suspended
            $table->string('status', 32)->default('draft');

            $table->unsignedBigInteger('stream_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_download')->default(false);
            $table->boolean('explicit_content')->default(false);

            $table->unsignedInteger('track_number')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
            $table->index('status');
            $table->index('published_at');
            $table->index('stream_count');
        });

        // Copyright / licensing metadata lives with the song
        Schema::create('song_copyrights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->string('copyright_owner')->nullable();
            $table->string('rights_holder')->nullable();
            // owned | licensed | permission_granted | public_domain | unknown
            $table->string('permission_status', 32)->default('unknown');
            // all_rights_reserved | cc_by | cc_by_sa | cc0 | custom
            $table->string('license_type', 32)->nullable();
            $table->boolean('distribution_allowed')->default(true);
            $table->boolean('monetization_allowed')->default(false);
            $table->string('permission_document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_copyrights');
        Schema::dropIfExists('songs');
    }
};
