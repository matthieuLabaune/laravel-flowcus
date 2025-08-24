<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\PomodoroSessionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $tasks = \App\Models\Task::query()
        ->forUser($user)
        ->latest('id')
        ->limit(10)
        ->get(['id', 'title', 'status', 'deadline_at', 'completed_at']);
    $activeSession = \App\Models\PomodoroSession::query()
        ->where('user_id', $user->id)
        ->whereNull('ended_at')
        ->latest('started_at')
        ->first(['id', 'task_id', 'planned_seconds', 'actual_seconds', 'started_at', 'interruptions_count', 'paused_seconds', 'last_paused_at']);

    return Inertia::render('Dashboard', [
        'tasks' => $tasks,
        'activeSession' => $activeSession,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('/sessions/start', [PomodoroSessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/finish', [PomodoroSessionController::class, 'finish'])->name('sessions.finish');
    Route::post('/sessions/{session}/interrupt', [PomodoroSessionController::class, 'interrupt'])->name('sessions.interrupt'); // legacy name
    Route::post('/sessions/{session}/pause', [PomodoroSessionController::class, 'interrupt'])->name('sessions.pause');
    Route::post('/sessions/{session}/resume', [PomodoroSessionController::class, 'resume'])->name('sessions.resume');

    // Task CRUD
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    // Projects CRUD
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
