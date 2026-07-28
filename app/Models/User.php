<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'avatar_path',
        'cover_path', 'bio', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function songs(): HasMany { return $this->hasMany(Song::class); }
    public function albums(): HasMany { return $this->hasMany(Album::class); }
    public function playlists(): HasMany { return $this->hasMany(Playlist::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function listeningHistories(): HasMany { return $this->hasMany(ListeningHistory::class); }

    public function likedSongs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'song_likes')->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isArtist(): bool { return in_array($this->role, ['artist', 'admin'], true); }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)) ?: [])
            ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    }
}
