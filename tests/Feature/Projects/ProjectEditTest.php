<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('projects');

it('can display the project show page', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertInertia(
            fn($page) => $page
                ->component('Projects/Show')
                ->has('project')
                ->has('projectNotes')
                ->has('projectTasks')
                ->where('project.id', $project->id)
                ->where('project.name', $project->name)
        );
});

it('can display the project edit page', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('projects.edit', $project))
        ->assertInertia(
            fn($page) => $page
                ->component('Projects/Edit')
                ->has('project')
                ->where('project.id', $project->id)
                ->where('project.name', $project->name)
        );
});

it('can update a project', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->put(route('projects.update', $project), [
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
            'color' => '#ff0000'
        ])
        ->assertRedirect(route('projects.show', $project));

    $project->refresh();
    expect($project->name)->toBe('Updated Project Name');
    expect($project->description)->toBe('Updated description');
    expect($project->color)->toBe('#ff0000');
});
