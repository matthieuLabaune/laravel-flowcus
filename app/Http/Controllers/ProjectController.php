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

        // If this is an Inertia visit, return the page component.
        if ($request->header('X-Inertia')) {
            return Inertia::render('Projects/Index', [
                'projects' => $projects,
            ]);
        }

        return ProjectResource::collection($projects)->response();
    }

    public function store(StoreProjectRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Project::class);
        $project = Project::query()->create([
            'user_id' => $request->user()->id,
            'name' => $request->string('name'),
            'color' => $request->input('color'),
        ]);

        if ($request->header('X-Inertia')) {
            return redirect()->route('projects.index');
        }

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function show(Request $request, Project $project): JsonResponse|\Inertia\Response
    {
        $this->authorize('view', $project);
        $project->loadCount('tasks');

        if ($request->header('X-Inertia')) {
            return Inertia::render('Projects/Show', [
                'project' => $project,
            ]);
        }

        return (new ProjectResource($project))->response();
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $project);
        $project->fill($request->only(['name', 'color']))->save();

        if ($request->header('X-Inertia')) {
            return redirect()->route('projects.index');
        }

        return (new ProjectResource($project))->response();
    }

    public function destroy(Request $request, Project $project): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        if ($request->header('X-Inertia')) {
            return redirect()->route('projects.index');
        }

        return response()->json(null, 204);
    }
}
