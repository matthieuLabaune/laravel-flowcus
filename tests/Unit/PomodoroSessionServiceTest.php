<?php

declare(strict_types=1);

use App\Enums\SessionType;
use App\Domain\Pomodoro\Exceptions\ActiveSessionExists;
use App\Domain\Pomodoro\Exceptions\SessionAlreadyFinished;
use App\Models\PomodoroSession;
use App\Models\User;
use App\Services\PomodoroSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('starts a focus session', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();

    $session = $service->startFocus($user, null, 1500);

    expect($session)
        ->user_id->toBe($user->id)
        ->type->toBe(SessionType::Focus)
        ->actual_seconds->toBe(0)
        ->ended_at->toBeNull();
});

it('prevents multiple active sessions', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();
    $service->startFocus($user, null, 1500);

    expect(fn() => $service->startFocus($user, null, 1500))->toThrow(ActiveSessionExists::class);
});

it('finishes a session and computes actual seconds', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();

    $now = Carbon::now();
    Carbon::setTestNow($now);
    $session = $service->startFocus($user, null, 1500);

    $endTime = $now->copy()->addSeconds(95);

    $service->finish($session, $endTime);

    $session->refresh();
    expect($session->ended_at)->not->toBeNull();
    expect((int) round($session->actual_seconds))->toBe(95);

    Carbon::setTestNow(); // clear
});

it('interrupt increments counter', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();
    $session = $service->startFocus($user, null, 1500);

    $service->interrupt($session);
    $service->interrupt($session);

    $session->refresh();
    expect($session->interruptions_count)->toBe(2);
});

it('resume resets interruptions counter', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();
    $session = $service->startFocus($user, null, 1500);

    $service->interrupt($session);
    $service->interrupt($session);
    expect($session->refresh()->interruptions_count)->toBe(2);

    $service->resume($session);
    expect($session->refresh()->interruptions_count)->toBe(0);
});

it('cannot resume finished session', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();
    $session = $service->startFocus($user, null, 1500);
    $service->finish($session);

    expect(fn() => $service->resume($session))->toThrow(SessionAlreadyFinished::class);
});

it('cannot finish twice', function () {
    $user = User::factory()->create();
    $service = new PomodoroSessionService();
    $session = $service->startFocus($user, null, 1500);
    $service->finish($session);

    expect(fn() => $service->finish($session))->toThrow(SessionAlreadyFinished::class);
});
