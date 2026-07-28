<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id(); $table->string('name', 80)->unique(); $table->string('slug', 100)->unique();
            $table->text('description')->nullable(); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });

        Schema::create('albums', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150); $table->string('slug', 190); $table->text('description')->nullable();
            $table->string('cover_path')->nullable(); $table->date('release_date')->nullable();
            $table->string('visibility', 20)->default('public')->index(); $table->timestamps();
            $table->unique(['user_id', 'slug'], 'albums_user_slug_uq');
        });

        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 180); $table->string('slug', 190);
            $table->text('description')->nullable();
            $table->string('audio_path'); $table->string('audio_mime', 100)->default('audio/mpeg');
            $table->string('cover_path')->nullable(); $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedSmallInteger('track_number')->nullable(); $table->date('release_date')->nullable();
            $table->string('visibility', 20)->default('public')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('play_count')->default(0)->index();
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'slug'], 'songs_user_slug_uq');
            $table->index(['status', 'visibility', 'release_date'], 'songs_publish_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs'); Schema::dropIfExists('albums'); Schema::dropIfExists('genres');
    }
};
