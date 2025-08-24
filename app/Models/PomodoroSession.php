<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'type',
        'planned_seconds',
        'actual_seconds',
        'interruptions_count',
        'paused_seconds',
        'last_paused_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'type' => SessionType::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_paused_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
