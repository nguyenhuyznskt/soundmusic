<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'slug', 'description', 'cover_path', 'visibility'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class)->withPivot('position')->withTimestamps()->orderByPivot('position');
    }
    public function getCoverUrlAttribute(): string
    {
        if ($this->cover_path) return Storage::disk('public')->url($this->cover_path);
        return $this->songs->first()?->cover_url ?? asset('images/default-playlist.svg');
    }
}
