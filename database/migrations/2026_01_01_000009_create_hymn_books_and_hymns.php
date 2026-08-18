<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hymn_books', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('language_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('publisher')->nullable();
            $table->year('published_year')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('copyright_notice')->nullable();
            $table->string('license_type', 32)->nullable();
            $table->boolean('is_public_domain')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hymns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hymn_book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->string('slug');
            $table->text('lyrics')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('composer')->nullable();
            $table->string('tune_name')->nullable();
            $table->string('meter', 32)->nullable();
            $table->timestamps();
            $table->unique(['hymn_book_id', 'number']);
            $table->unique(['hymn_book_id', 'slug']);
            $table->index('title');
        });

        // A hymn can have multiple recordings — each recording links to a song entry
        Schema::create('hymn_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hymn_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['hymn_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hymn_recordings');
        Schema::dropIfExists('hymns');
        Schema::dropIfExists('hymn_books');
    }
};
