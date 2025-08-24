<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Task;

it('can display project detail page with notes', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get("/projects/{$project->id}");

    $response->assertSuccessful()
        ->assertInertia(
            fn($page) => $page
                ->component('Projects/Show')
                ->has('project')
                ->has('projectNotes')
        );
});

it('can display task detail page with notes', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get("/tasks/{$task->id}");

    $response->assertSuccessful()
        ->assertInertia(
            fn($page) => $page
                ->component('Tasks/Show')
                ->has('task')
                ->has('taskNotes')
        );
});
