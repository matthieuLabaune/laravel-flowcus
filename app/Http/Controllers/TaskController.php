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
use Inertia\Inertia;
use App\Http\Resources\TaskResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse|\Inertia\Response
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

        // Always return Inertia response for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'data' => TaskResource::collection(collect($tasks->items()))->resolve(),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                ],
            ]);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
            'filters' => [
                'status' => $request->query('status'),
                'project_id' => $request->query('project_id'),
            ],
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
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

        // For Inertia requests, just return back (no redirect)
        if ($request->header('X-Inertia')) {
            return back();
        }

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        // If it's an API request, return JSON
        if ($request->expectsJson()) {
            return (new TaskResource($task))->response();
        }

        // For web requests, return Inertia view with notes
        $taskNotes = $task->notes()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get(['id', 'content', 'created_at', 'updated_at', 'noteable_type', 'noteable_id']);

        return Inertia::render('Tasks/Show', [
            'task' => $task->load('project:id,title'),
            'taskNotes' => $taskNotes,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $task);
        $payload = $request->only(['title', 'description', 'deadline_at', 'project_id']);
        $task->fill($payload)->save();

        if ($request->header('X-Inertia')) {
            return redirect()->route('tasks.index');
        }

        return (new TaskResource($task))->response();
    }

    public function destroy(Request $request, Task $task): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        if ($request->header('X-Inertia')) {
            return redirect()->route('tasks.index');
        }

        return response()->json(null, 204);
    }

    public function complete(Request $request, Task $task): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('complete', $task);
        if ($task->status !== TaskStatus::Done) {
            $task->status = TaskStatus::Done;
            $task->completed_at = Carbon::now();
            $task->save();
        }

        if ($request->header('X-Inertia')) {
            return redirect()->route('tasks.index');
        }

        return (new TaskResource($task))->response();
    }
}
