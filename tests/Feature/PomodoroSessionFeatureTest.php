<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\PomodoroSession;
use App\Services\PomodoroSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('starts a session via HTTP', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(route('sessions.start'), [
        'planned_seconds' => 1500,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.planned_seconds', 1500);
});

it('prevents starting two sessions', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('sessions.start'), ['planned_seconds' => 1500]);
    $response = $this->actingAs($user)->postJson(route('sessions.start'), ['planned_seconds' => 1500]);

    $response->assertUnprocessable();
});

it('finishes a session', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('sessions.start'), ['planned_seconds' => 1500]);
    $session = PomodoroSession::first();

    // fast-forward: set started_at 2 minutes earlier
    $session->update(['started_at' => now()->subMinutes(2)]);

    $response = $this->actingAs($user)->postJson(route('sessions.finish', $session));

    $response->assertOk()->assertJsonStructure(['data' => ['actual_seconds', 'ended_at']]);
});

it('interrupt increments count', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('sessions.start'), ['planned_seconds' => 1500]);
    $session = PomodoroSession::first();

    $this->actingAs($user)->postJson(route('sessions.interrupt', $session))->assertOk();
    $this->actingAs($user)->postJson(route('sessions.interrupt', $session))->assertOk();

    $session->refresh();
    expect($session->interruptions_count)->toBe(2);
});
