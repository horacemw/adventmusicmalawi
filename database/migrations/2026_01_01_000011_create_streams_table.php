<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->nullable();
            $table->string('ip_hash', 64)->nullable(); // hashed for privacy
            $table->string('country', 2)->nullable();
            $table->string('region', 64)->nullable();
            $table->string('device_type', 32)->nullable();
            $table->string('platform', 32)->nullable();
            $table->string('browser', 32)->nullable();
            $table->unsignedInteger('duration_played_seconds')->default(0);
            $table->boolean('completed')->default(false);
            // If true, this stream counts toward the song's stream_count (anti-abuse rules)
            $table->boolean('counted')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['song_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['session_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
