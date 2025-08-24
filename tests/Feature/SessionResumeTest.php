<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\PomodoroSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can resume a paused session', function () {
    $user = User::factory()->create();
    $service = app(PomodoroSessionService::class);
    $session = $service->startFocus($user, null, 1500);
    $service->interrupt($session);

    expect($session->refresh()->interruptions_count)->toBe(1);

    $response = $this->actingAs($user)->post(route('sessions.resume', $session->id));

    $response->assertSuccessful();
    expect($session->refresh()->interruptions_count)->toBe(0);
});

it('cannot resume session from another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $service = app(PomodoroSessionService::class);
    $session = $service->startFocus($user1, null, 1500);
    $service->interrupt($session);

    $response = $this->actingAs($user2)->post(route('sessions.resume', $session->id));

    $response->assertForbidden();
});

it('cannot resume finished session', function () {
    $user = User::factory()->create();
    $service = app(PomodoroSessionService::class);
    $session = $service->startFocus($user, null, 1500);
    $service->finish($session);

    $response = $this->actingAs($user)->post(route('sessions.resume', $session->id));

    // Should return conflict status when trying to resume finished session
    $response->assertStatus(409);
});
