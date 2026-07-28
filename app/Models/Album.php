<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Album extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'slug', 'description', 'cover_path', 'release_date', 'visibility'];
    protected function casts(): array { return ['release_date' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function songs(): HasMany { return $this->hasMany(Song::class)->orderBy('track_number'); }
    public function getCoverUrlAttribute(): string
    {
        if (!$this->cover_path) return asset('images/default-cover.svg');
        return str_starts_with($this->cover_path, 'demo-covers/') ? asset($this->cover_path) : Storage::disk('public')->url($this->cover_path);
    }
}
