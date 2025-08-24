<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a task', function () {
    $user = User::factory()->create();
    $payload = [
        'title' => 'My Task',
        'description' => 'Desc',
    ];
    $response = $this->actingAs($user)->postJson(route('tasks.store'), $payload);
    $response->assertCreated();
    $response->assertJsonPath('data.title', 'My Task');
    expect(Task::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('lists tasks with pagination meta', function () {
    $user = User::factory()->create();
    Task::factory()->count(3)->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->getJson(route('tasks.index'));
    $response->assertSuccessful()
        ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
});

it('updates a task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->putJson(route('tasks.update', $task), [
        'title' => 'Updated',
    ]);
    $response->assertSuccessful()->assertJsonPath('data.title', 'Updated');
});

it('forbids updating task of another user', function () {
    [$userA, $userB] = User::factory()->count(2)->create();
    $task = Task::factory()->create(['user_id' => $userA->id]);
    $this->actingAs($userB)->putJson(route('tasks.update', $task), ['title' => 'X'])->assertForbidden();
});

it('completes a task', function () {
    Carbon::setTestNow('2025-08-24 10:00:00');
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user)->postJson(route('tasks.complete', $task))
        ->assertSuccessful()
        ->assertJsonPath('data.status', TaskStatus::Done->value);
    $task->refresh();
    expect($task->completed_at)->not()->toBeNull();
});

it('deletes a task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user)->deleteJson(route('tasks.destroy', $task))->assertNoContent();
    expect(Task::query()->find($task->id))->toBeNull();
});
