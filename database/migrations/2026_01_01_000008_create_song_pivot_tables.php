<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_song', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['song_id', 'category_id']);
        });

        Schema::create('occasion_song', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occasion_id')->constrained()->cascadeOnDelete();
            $table->primary(['song_id', 'occasion_id']);
        });

        Schema::create('mood_song', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mood_id')->constrained()->cascadeOnDelete();
            $table->primary(['song_id', 'mood_id']);
        });

        Schema::create('song_tag', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['song_id', 'tag_id']);
        });

        // Songs may have secondary artists (features)
        Schema::create('artist_song', function (Blueprint $table) {
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('featured');
            $table->primary(['song_id', 'artist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_song');
        Schema::dropIfExists('song_tag');
        Schema::dropIfExists('mood_song');
        Schema::dropIfExists('occasion_song');
        Schema::dropIfExists('category_song');
    }
};
