<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListeningHistory extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['user_id', 'song_id', 'last_position_seconds', 'listened_at'];
    protected function casts(): array { return ['listened_at' => 'datetime', 'last_position_seconds' => 'integer']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function song(): BelongsTo { return $this->belongsTo(Song::class); }
}
