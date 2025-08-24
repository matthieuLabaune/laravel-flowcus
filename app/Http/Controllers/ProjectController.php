<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);
        $projects = Project::query()->where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'user_id']);
        return response()->json(['data' => $projects]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);
        $project = Project::query()->create([
            'user_id' => $request->user()->id,
            'name' => $request->string('name'),
            'color' => $request->input('color'),
        ]);
        return response()->json(['data' => $project], 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->loadCount('tasks');
        return response()->json(['data' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->fill($request->only(['name', 'color']))->save();
        return response()->json(['data' => $project]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(status: 204);
    }
}
