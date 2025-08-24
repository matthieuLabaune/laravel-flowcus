<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Task;

echo "Creating test data for Project/Task Notes...\n";

$user = User::first() ?? User::factory()->create();
echo "User: {$user->email}\n";

$project = $user->projects()->first() ?? $user->projects()->create([
    'name' => 'Test Project Notes',
    'color' => '#7c3aed'
]);
echo "Project: {$project->name} (ID: {$project->id})\n";

$task = $user->tasks()->first() ?? $user->tasks()->create([
    'title' => 'Test Task Notes',
    'status' => 'pending',
    'project_id' => $project->id
]);
echo "Task: {$task->title} (ID: {$task->id})\n";

echo "\n✅ Test URLs:\n";
echo "Project detail: http://localhost:8000/projects/{$project->id}\n";
echo "Task detail: http://localhost:8000/tasks/{$task->id}\n";
echo "Projects index: http://localhost:8000/projects\n";
echo "Tasks index: http://localhost:8000/tasks\n";

echo "\n🎯 Priority 1 implementation completed:\n";
echo "- ✅ Polymorphic Notes system\n";
echo "- ✅ Project detail page with notes\n";
echo "- ✅ Task detail page with notes\n";
echo "- ✅ NotesPanel component integrated\n";
echo "- ✅ Links from index pages to detail pages\n";
