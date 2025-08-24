<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);
        $user = $request->user();
        $query = Task::query()->forUser($user)->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        /** @var LengthAwarePaginator $tasks */
        $tasks = $query->paginate(20);

        return response()->json([
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);
        $task = Task::query()->create([
            'user_id' => $request->user()->id,
            'project_id' => $request->input('project_id'),
            'title' => $request->string('title'),
            'description' => $request->input('description'),
            'deadline_at' => $request->input('deadline_at'),
            'status' => TaskStatus::Pending,
        ]);

        return response()->json(['data' => $task], 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        return response()->json(['data' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $payload = $request->only(['title', 'description', 'deadline_at', 'project_id']);
        $task->fill($payload)->save();
        return response()->json(['data' => $task]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(status: 204);
    }

    public function complete(Request $request, Task $task): JsonResponse
    {
        $this->authorize('complete', $task);
        if ($task->status !== TaskStatus::Done) {
            $task->status = TaskStatus::Done;
            $task->completed_at = Carbon::now();
            $task->save();
        }
        return response()->json(['data' => $task]);
    }
}
