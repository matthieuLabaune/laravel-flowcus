<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Task;
use App\Models\PomodoroSession;

it('requires csrf token for note creation', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();
    $session = PomodoroSession::factory()->for($user)->for($task)->create();

    // Attempt to create note without CSRF token
    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/notes', [
            'content' => 'Test note for session',
            'noteable_type' => 'App\\Models\\PomodoroSession',
            'noteable_id' => $session->id,
        ]);

    $response->assertStatus(419); // CSRF token mismatch
});

it('can create note with valid csrf token', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();
    $session = PomodoroSession::factory()->for($user)->for($task)->create();

    // Get CSRF token first
    $csrfResponse = $this->actingAs($user)->get('/dashboard');
    $csrfToken = $csrfResponse->viewData('_token') ?? session()->token();

    $response = $this->actingAs($user)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => $csrfToken,
        ])
        ->postJson('/notes', [
            'content' => 'Test note for session',
            'noteable_type' => 'App\\Models\\PomodoroSession',
            'noteable_id' => $session->id,
        ]);

    $response->assertStatus(201);
});
