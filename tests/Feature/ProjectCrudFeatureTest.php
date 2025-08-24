<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a project', function () {
    $user = User::factory()->create();
    $resp = $this->actingAs($user)->postJson(route('projects.store'), [
        'name' => 'Demo Project',
        'color' => '#ff00aa',
    ]);
    $resp->assertCreated()->assertJsonPath('data.name', 'Demo Project');
    expect(Project::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('lists only own projects', function () {
    [$a, $b] = User::factory()->count(2)->create();
    Project::factory()->create(['user_id' => $a->id]);
    Project::factory()->create(['user_id' => $b->id]);
    $resp = $this->actingAs($a)->getJson(route('projects.index'));
    $resp->assertSuccessful();
    expect($resp->json('data'))->toHaveCount(1);
});

it('forbids deleting someone else project', function () {
    [$a, $b] = User::factory()->count(2)->create();
    $project = Project::factory()->create(['user_id' => $a->id]);
    $this->actingAs($b)->deleteJson(route('projects.destroy', $project))->assertForbidden();
});

it('updates a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id, 'name' => 'Old']);
    $this->actingAs($user)->putJson(route('projects.update', $project), ['name' => 'New'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'New');
});
