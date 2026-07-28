<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayEvent extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['song_id', 'user_id', 'session_key', 'ip_hash', 'user_agent', 'played_at'];
    protected function casts(): array { return ['played_at' => 'datetime']; }
    public function song(): BelongsTo { return $this->belongsTo(Song::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
