<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'genre_id', 'album_id', 'title', 'slug', 'description',
        'audio_path', 'audio_mime', 'cover_path', 'duration_seconds', 'track_number',
        'release_date', 'visibility', 'status', 'play_count', 'like_count', 'comment_count',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'duration_seconds' => 'integer',
            'play_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function genre(): BelongsTo { return $this->belongsTo(Genre::class); }
    public function album(): BelongsTo { return $this->belongsTo(Album::class); }
    public function playlists(): BelongsToMany { return $this->belongsToMany(Playlist::class)->withPivot('position')->withTimestamps(); }
    public function likedBy(): BelongsToMany { return $this->belongsToMany(User::class, 'song_likes')->withTimestamps(); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->whereNull('parent_id')->latest(); }
    public function playEvents(): HasMany { return $this->hasMany(PlayEvent::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('visibility', 'public')
            ->where(fn ($q) => $q->whereNull('release_date')->orWhereDate('release_date', '<=', now()->toDateString()));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        $term = trim($term);
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('username', 'like', "%{$term}%"))
              ->orWhereHas('genre', fn ($g) => $g->where('name', 'like', "%{$term}%"));
        });
    }

    public function getCoverUrlAttribute(): string
    {
        if (!$this->cover_path) return $this->album?->cover_url ?? asset('images/default-cover.svg');
        return str_starts_with($this->cover_path, 'demo-covers/') ? asset($this->cover_path) : Storage::disk('public')->url($this->cover_path);
    }

    public function getStreamUrlAttribute(): string { return route('songs.stream', $this); }
    public function getFormattedDurationAttribute(): string
    {
        $minutes = intdiv(max(0, $this->duration_seconds), 60);
        $seconds = max(0, $this->duration_seconds) % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function toPlayerPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->user->name,
            'artistUrl' => route('profiles.show', $this->user->username),
            'url' => $this->stream_url,
            'cover' => $this->cover_url,
            'detailUrl' => route('songs.show', $this),
            'playUrl' => route('songs.played', $this),
            'duration' => $this->duration_seconds,
        ];
    }
}
