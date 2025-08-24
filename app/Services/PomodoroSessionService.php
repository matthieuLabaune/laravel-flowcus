<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SessionType;
use App\Domain\Pomodoro\Exceptions\ActiveSessionExists;
use App\Domain\Pomodoro\Exceptions\InvalidPlannedSeconds;
use App\Domain\Pomodoro\Exceptions\SessionAlreadyFinished;
use App\Models\PomodoroSession;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PomodoroSessionService
{
    /**
     * Start a focus session (or break) — currently only focus exposed.
     * @throws RuntimeException if another active session exists.
     */
    public function startFocus(User $user, ?Task $task, int $plannedSeconds): PomodoroSession
    {
        if ($plannedSeconds < 60 || $plannedSeconds > 7200) {
            throw new InvalidPlannedSeconds('planned_seconds must be between 60 and 7200');
        }

        $active = $this->activeSession($user);
        if ($active) {
            throw new ActiveSessionExists('Another active session exists.');
        }

        return PomodoroSession::query()->create([
            'user_id' => $user->id,
            'task_id' => $task?->id,
            'type' => SessionType::Focus,
            'planned_seconds' => $plannedSeconds,
            'actual_seconds' => 0,
            'interruptions_count' => 0,
            'started_at' => now(),
        ]);
    }

    /** Finish a running session and compute actual_seconds.
     *  @param \DateTimeInterface|null $endOverride allow deterministic testing
     */
    public function finish(PomodoroSession $session, ?\DateTimeInterface $endOverride = null): PomodoroSession
    {
        if ($session->ended_at) {
            throw new SessionAlreadyFinished('Cannot finish twice.');
        }

        // Use current in-memory started_at (original persisted value) to avoid resetting by refresh.
        $startedAt = $session->started_at instanceof Carbon ? $session->started_at->copy() : Carbon::parse($session->getRawOriginal('started_at'));
        $endedAt = $endOverride ? Carbon::parse($endOverride) : Carbon::now();
        $delta = max(0, $endedAt->diffInSeconds($startedAt));

        $session->ended_at = $endedAt;
        $session->actual_seconds = $delta;
        $session->save();

        return $session;
    }

    /** Register an interruption. */
    public function interrupt(PomodoroSession $session): PomodoroSession
    {
        if ($session->ended_at) {
            throw new SessionAlreadyFinished('Cannot interrupt finished session.');
        }

        $session->increment('interruptions_count');
        return $session->refresh();
    }

    /** Return currently active session (focus or break). */
    public function activeSession(User $user): ?PomodoroSession
    {
        return PomodoroSession::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
