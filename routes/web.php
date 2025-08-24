<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PomodoroSessionController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('/sessions/start', [PomodoroSessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/finish', [PomodoroSessionController::class, 'finish'])->name('sessions.finish');
    Route::post('/sessions/{session}/interrupt', [PomodoroSessionController::class, 'interrupt'])->name('sessions.interrupt');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
