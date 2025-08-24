<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\PomodoroSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('correctly tracks pause duration', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();

    $now = Carbon::now();
    Carbon::setTestNow($now);

    // Start session
    $session = $service->startFocus($user, null, 1500);
    expect($session->paused_seconds)->toBe(0);
    expect($session->last_paused_at)->toBeNull();

    // Advance time by 60 seconds and pause
    Carbon::setTestNow($now->copy()->addSeconds(60));
    $service->interrupt($session);

    $session->refresh();
    expect($session->last_paused_at)->not->toBeNull();

    // Advance time by 30 seconds (pause duration) and resume
    Carbon::setTestNow($now->copy()->addSeconds(90));
    $service->resume($session);

    $session->refresh();
    expect($session->paused_seconds)->toBe(30); // 30 seconds of pause
    expect($session->last_paused_at)->toBeNull();

    // Advance time by another 60 seconds and pause again
    Carbon::setTestNow($now->copy()->addSeconds(150));
    $service->interrupt($session);

    // Advance time by 20 seconds (second pause) and finish
    Carbon::setTestNow($now->copy()->addSeconds(170));
    $service->finish($session);

    $session->refresh();
    expect($session->paused_seconds)->toBe(50); // 30 + 20 seconds of total pause
    expect((int) round($session->actual_seconds))->toBe(120); // 170 total - 50 paused = 120 actual

    Carbon::setTestNow(); // clear
});

it('handles session finish while paused', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();

    $now = Carbon::now();
    Carbon::setTestNow($now);

    // Start session
    $session = $service->startFocus($user, null, 1500);

    // Work for 100 seconds, then pause
    Carbon::setTestNow($now->copy()->addSeconds(100));
    $service->interrupt($session);

    // Stay paused for 40 seconds, then finish
    Carbon::setTestNow($now->copy()->addSeconds(140));
    $service->finish($session);

    $session->refresh();
    expect($session->paused_seconds)->toBe(40); // 40 seconds paused
    expect((int) round($session->actual_seconds))->toBe(100); // 140 total - 40 paused = 100 actual

    Carbon::setTestNow(); // clear
});
