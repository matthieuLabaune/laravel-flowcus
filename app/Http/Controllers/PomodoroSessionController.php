<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Pomodoro\Exceptions\ActiveSessionExists;
use App\Domain\Pomodoro\Exceptions\InvalidPlannedSeconds;
use App\Domain\Pomodoro\Exceptions\SessionAlreadyFinished;
use App\Http\Requests\Sessions\StartSessionRequest;
use App\Models\PomodoroSession;
use App\Models\Task;
use App\Services\PomodoroSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\PomodoroSessionResource;
use Inertia\Response as InertiaResponse;

class PomodoroSessionController extends Controller
{
    public function __construct(private PomodoroSessionService $service) {}

    public function start(StartSessionRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse|InertiaResponse
    {
        $user = $request->user();
        $task = null;
        if ($request->filled('task_id')) {
            $task = Task::query()->where('user_id', $user->id)->findOrFail($request->integer('task_id'));
        }

        try {
            $session = $this->service->startFocus($user, $task, $request->integer('planned_seconds'));
        } catch (InvalidPlannedSeconds | ActiveSessionExists $e) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Dashboard', $this->dashboardProps($user) + [
                    'sessionError' => $e->getMessage(),
                ]);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard');
        }
        return (new PomodoroSessionResource($session))->response()->setStatusCode(201);
    }

    public function finish(Request $request, PomodoroSession $session): JsonResponse|\Illuminate\Http\RedirectResponse|InertiaResponse
    {
        $this->authorizeSession($request, $session);
        try {
            $this->service->finish($session);
        } catch (SessionAlreadyFinished $e) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Dashboard', $this->dashboardProps($request->user()) + [
                    'sessionError' => $e->getMessage(),
                ]);
            }
            return response()->json(['message' => $e->getMessage()], 409);
        }

        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard');
        }
        return (new PomodoroSessionResource($session))->response();
    }

    // Pause (formerly interrupt). Kept method name for old route but will also expose pause alias.
    public function interrupt(Request $request, PomodoroSession $session): JsonResponse|\Illuminate\Http\RedirectResponse|InertiaResponse
    {
        $this->authorizeSession($request, $session);
        try {
            $this->service->interrupt($session);
        } catch (SessionAlreadyFinished $e) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Dashboard', $this->dashboardProps($request->user()) + [
                    'sessionError' => $e->getMessage(),
                ]);
            }
            return response()->json(['message' => $e->getMessage()], 409);
        }
        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard');
        }
        return (new PomodoroSessionResource($session))->response();
    }

    public function resume(Request $request, PomodoroSession $session): JsonResponse|\Illuminate\Http\RedirectResponse|InertiaResponse
    {
        $this->authorizeSession($request, $session);
        try {
            $this->service->resume($session);
        } catch (SessionAlreadyFinished $e) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Dashboard', $this->dashboardProps($request->user()) + [
                    'sessionError' => $e->getMessage(),
                ]);
            }
            return response()->json(['message' => $e->getMessage()], 409);
        }
        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard');
        }
        return (new PomodoroSessionResource($session))->response();
    }

    private function authorizeSession(Request $request, PomodoroSession $session): void
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    // Removed internal inertiaDashboard helper: dashboard rendering handled by route redirect.
    private function dashboardProps($user): array
    {
        $tasks = \App\Models\Task::query()
            ->forUser($user)
            ->latest('id')
            ->limit(10)
            ->get(['id', 'title', 'status', 'deadline_at', 'completed_at']);
        $activeSession = \App\Models\PomodoroSession::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first(['id', 'task_id', 'planned_seconds', 'actual_seconds', 'started_at', 'interruptions_count', 'paused_seconds', 'last_paused_at']);
        return [
            'tasks' => $tasks,
            'activeSession' => $activeSession,
        ];
    }
}
