<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            // private | public | unlisted
            $table->string('visibility', 16)->default('private');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'slug']);
        });

        Schema::create('playlist_song', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('added_at')->useCurrent();
            $table->unique(['playlist_id', 'song_id']);
            $table->index(['playlist_id', 'position']);
        });

        // Polymorphic likes — song, album, artist, music_group, church, playlist, hymn
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('likeable');
            $table->timestamps();
            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'likes_unique');
        });

        // Polymorphic follows — artist, music_group, church, user
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('followable');
            $table->timestamps();
            $table->unique(['user_id', 'followable_type', 'followable_id'], 'follows_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('playlist_song');
        Schema::dropIfExists('playlists');
    }
};
