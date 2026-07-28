<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'reportable_type', 'reportable_id', 'reason', 'details', 'status', 'admin_note', 'resolved_by', 'resolved_at'];
    protected function casts(): array { return ['resolved_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
    public function reportable(): MorphTo { return $this->morphTo(); }
}
