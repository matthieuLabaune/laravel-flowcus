<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse|\Inertia\Response
    {
        $this->authorize('viewAny', Project::class);
        $projects = Project::query()
            ->where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'user_id']);

        // Always return Inertia response for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return ProjectResource::collection($projects)->response();
        }

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Project::class);
        $project = Project::query()->create([
            'user_id' => $request->user()->id,
            'name' => $request->string('name'),
            'description' => $request->input('description'),
            'color' => $request->input('color'),
        ]);

        // Always redirect for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return (new ProjectResource($project))->response()->setStatusCode(201);
        }

        return redirect()->route('projects.index');
    }

    public function show(Request $request, Project $project): JsonResponse|\Inertia\Response
    {
        $this->authorize('view', $project);
        $project->loadCount('tasks');

        // Load notes for this project
        $projectNotes = $project->notes()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get(['id', 'content', 'created_at', 'updated_at', 'noteable_type', 'noteable_id']);

        // Load tasks for this project
        $projectTasks = $project->tasks()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get(['id', 'title', 'description', 'status', 'created_at', 'updated_at']);

        // Always return Inertia response for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return (new ProjectResource($project))->response();
        }

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'projectNotes' => $projectNotes,
            'projectTasks' => $projectTasks,
        ]);
    }

    public function edit(Request $request, Project $project): \Inertia\Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $project);
        $project->fill($request->only(['name', 'description', 'color']))->save();

        // Always redirect for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return (new ProjectResource($project))->response();
        }

        return redirect()->route('projects.show', $project);
    }

    public function destroy(Request $request, Project $project): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        // Always redirect for web routes
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(null, 204);
        }

        return redirect()->route('projects.index');
    }
}
