<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SessionType;
use App\Enums\TaskStatus;
use App\Models\PomodoroSession;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create();

        // Create demo projects
        $projects = Project::factory()->count(3)->create(['user_id' => $user->id]);

        foreach ($projects as $project) {
            // Mix of pending/done tasks
            Task::factory()->count(4)->create(['user_id' => $user->id, 'project_id' => $project->id]);
            Task::factory()->done()->count(2)->create(['user_id' => $user->id, 'project_id' => $project->id]);
        }

        // Seed a few historical focus sessions for analytics feel
        $base = Carbon::now()->subDay();
        foreach (range(1, 5) as $i) {
            $start = $base->copy()->addHours($i * 2);
            PomodoroSession::query()->create([
                'user_id' => $user->id,
                'task_id' => Task::inRandomOrder()->first()?->id,
                'type' => SessionType::Focus,
                'planned_seconds' => 1500,
                'actual_seconds' => random_int(900, 1600),
                'interruptions_count' => random_int(0, 2),
                'started_at' => $start,
                'ended_at' => $start->copy()->addSeconds(1500),
            ]);
        }
    }
}
