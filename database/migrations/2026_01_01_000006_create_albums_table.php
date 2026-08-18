<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('church_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('artwork_path')->nullable();
            $table->year('release_year')->nullable();
            $table->date('released_at')->nullable();
            $table->string('label')->nullable();
            $table->foreignId('primary_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('title');
            $table->index('release_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
