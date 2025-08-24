<?php

use App\Models\User;
use App\Models\Task;
use App\Models\PomodoroSession;

$user = User::first();
if (!$user) {
    $user = User::factory()->create(['email' => 'test@example.com']);
    echo 'Utilisateur créé: ' . $user->email . PHP_EOL;
} else {
    echo 'Utilisateur existant: ' . $user->email . PHP_EOL;
}

$task = Task::factory()->for($user)->create(['title' => 'Tâche de test avec notes']);
echo 'Tâche créée: ' . $task->title . PHP_EOL;

$session = PomodoroSession::factory()->for($user)->for($task)->create();
echo 'Session créée: ' . $session->id . ' pour la tâche: ' . $task->title . PHP_EOL;
