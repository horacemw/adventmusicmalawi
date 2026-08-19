<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device_type', 32)->nullable();
            $table->string('platform', 32)->nullable();
            $table->string('browser', 32)->nullable();
            $table->timestamps();

            $table->index(['song_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('songs', function (Blueprint $table) {
            if (!Schema::hasColumn('songs', 'download_count')) {
                $table->unsignedBigInteger('download_count')->default(0)->after('share_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (Schema::hasColumn('songs', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
        Schema::dropIfExists('downloads');
    }
};
