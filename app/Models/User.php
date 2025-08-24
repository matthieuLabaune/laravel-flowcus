<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pomodoro_length',
        'short_break_length',
        'long_break_length',
        'long_break_interval',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pomodoro_length' => 'integer',
            'short_break_length' => 'integer',
            'long_break_length' => 'integer',
            'long_break_interval' => 'integer',
        ];
    }

    /**
     * Get the projects owned by the user.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the tasks owned by the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the pomodoro sessions owned by the user.
     */
    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }
}
