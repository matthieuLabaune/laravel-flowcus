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

class PomodoroSessionController extends Controller
{
    public function __construct(private PomodoroSessionService $service) {}

    public function start(StartSessionRequest $request): JsonResponse
    {
        $user = $request->user();
        $task = null;
        if ($request->filled('task_id')) {
            $task = Task::query()->where('user_id', $user->id)->findOrFail($request->integer('task_id'));
        }

        try {
            $session = $this->service->startFocus($user, $task, $request->integer('planned_seconds'));
        } catch (InvalidPlannedSeconds | ActiveSessionExists $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $session->id,
                'type' => $session->type->value,
                'planned_seconds' => $session->planned_seconds,
                'actual_seconds' => $session->actual_seconds,
                'started_at' => $session->started_at,
            ],
        ], 201);
    }

    public function finish(Request $request, PomodoroSession $session): JsonResponse
    {
        $this->authorizeSession($request, $session);
        try {
            $this->service->finish($session);
        } catch (SessionAlreadyFinished $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'data' => [
                'id' => $session->id,
                'actual_seconds' => $session->actual_seconds,
                'ended_at' => $session->ended_at,
            ],
        ]);
    }

    public function interrupt(Request $request, PomodoroSession $session): JsonResponse
    {
        $this->authorizeSession($request, $session);
        try {
            $this->service->interrupt($session);
        } catch (SessionAlreadyFinished $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'data' => [
                'id' => $session->id,
                'interruptions_count' => $session->interruptions_count,
            ],
        ]);
    }

    private function authorizeSession(Request $request, PomodoroSession $session): void
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
