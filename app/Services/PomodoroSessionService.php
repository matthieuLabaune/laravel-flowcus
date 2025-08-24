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
            'paused_seconds' => 0,
            'last_paused_at' => null,
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

        $startedAt = Carbon::parse($session->started_at);
        $endedAt = $endOverride ? Carbon::parse($endOverride) : Carbon::now();

        // Calculate total elapsed time
        $totalElapsed = max(0, $startedAt->diffInSeconds($endedAt));

        // Add current pause time if session is currently paused
        $totalPausedTime = $session->paused_seconds;
        if ($session->last_paused_at) {
            $currentPauseDuration = $endedAt->diffInSeconds(Carbon::parse($session->last_paused_at));
            $totalPausedTime += $currentPauseDuration;
        }

        // Actual seconds = total elapsed - total paused
        $actualSeconds = max(0, $totalElapsed - $totalPausedTime);

        $session->ended_at = $endedAt;
        $session->actual_seconds = $actualSeconds;
        $session->paused_seconds = $totalPausedTime;
        $session->last_paused_at = null;
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
        $session->last_paused_at = now();
        $session->save();

        return $session->refresh();
    }

    /** Resume a paused session by resetting interruptions_count. */
    public function resume(PomodoroSession $session): PomodoroSession
    {
        if ($session->ended_at) {
            throw new SessionAlreadyFinished('Cannot resume finished session.');
        }

        // Add pause duration to total paused time
        if ($session->last_paused_at) {
            $pauseDuration = Carbon::parse($session->last_paused_at)->diffInSeconds(now());
            $session->paused_seconds += $pauseDuration;
        }

        $session->interruptions_count = 0;
        $session->last_paused_at = null;
        $session->save();

        return $session;
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
