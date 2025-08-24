<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\PomodoroSession;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a note for a pomodoro session', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();
    $session = PomodoroSession::factory()->for($user)->for($task)->create();

    $response = $this->actingAs($user)->postJson('/notes', [
        'content' => 'Test note for session',
        'noteable_type' => 'App\\Models\\PomodoroSession',
        'noteable_id' => $session->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'note' => [
                'id',
                'content',
                'noteable_type',
                'noteable_id',
                'user_id',
                'created_at',
                'updated_at',
            ]
        ]);

    $this->assertDatabaseHas('notes', [
        'content' => 'Test note for session',
        'noteable_type' => 'App\\Models\\PomodoroSession',
        'noteable_id' => $session->id,
        'user_id' => $user->id,
    ]);
});

it('validates required fields when creating notes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/notes', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['content', 'noteable_type', 'noteable_id']);
});
