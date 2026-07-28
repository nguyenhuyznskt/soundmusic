<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150); $table->string('slug', 190); $table->text('description')->nullable();
            $table->string('cover_path')->nullable(); $table->string('visibility', 20)->default('public')->index(); $table->timestamps();
            $table->unique(['user_id', 'slug'], 'playlists_user_slug_uq');
        });
        Schema::create('playlist_song', function (Blueprint $table) {
            $table->id(); $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete(); $table->unsignedInteger('position')->default(0); $table->timestamps();
            $table->unique(['playlist_id', 'song_id'], 'playlist_song_uq');
            $table->index(['playlist_id', 'position'], 'playlist_position_idx');
        });
        Schema::create('song_likes', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(['user_id', 'song_id'], 'song_likes_uq');
        });
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('content'); $table->string('status', 20)->default('visible')->index(); $table->timestamps();
        });
        Schema::create('follows', function (Blueprint $table) {
            $table->id(); $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete(); $table->timestamps();
            $table->unique(['follower_id', 'following_id'], 'follows_pair_uq');
        });
        Schema::create('listening_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('last_position_seconds')->default(0); $table->timestamp('listened_at')->useCurrent();
            $table->unique(['user_id', 'song_id'], 'history_user_song_uq'); $table->index(['user_id', 'listened_at'], 'history_user_time_idx');
        });
        Schema::create('play_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_key', 100)->nullable(); $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable(); $table->timestamp('played_at')->useCurrent();
            $table->index(['song_id', 'played_at'], 'plays_song_time_idx');
            $table->index(['user_id', 'played_at'], 'plays_user_time_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('play_events'); Schema::dropIfExists('listening_histories'); Schema::dropIfExists('follows');
        Schema::dropIfExists('comments'); Schema::dropIfExists('song_likes'); Schema::dropIfExists('playlist_song'); Schema::dropIfExists('playlists');
    }
};
