<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'deadline_at',
        'status',
        'project_id',
        'user_id',
        'completed_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => TaskStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PomodoroSession::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    protected function isCompleted(): Attribute
    {
        return Attribute::get(fn(): bool => $this->status === TaskStatus::Done);
    }
}
