<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PomodoroSession;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PomodoroSession>
 */
class PomodoroSessionFactory extends Factory
{
    protected $model = PomodoroSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 hour', 'now');
        $plannedSeconds = fake()->numberBetween(900, 3600); // 15-60 minutes

        return [
            'user_id' => User::factory(),
            'task_id' => null, // Can be nullable
            'planned_seconds' => $plannedSeconds,
            'actual_seconds' => 0, // Use 0 for active sessions (default value)
            'started_at' => $startedAt,
            'ended_at' => null, // null for active sessions
            'interruptions_count' => 0,
            'paused_seconds' => 0,
            'last_paused_at' => null,
        ];
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $attributes['started_at'] ?? now()->subMinutes(30);
            $actualSeconds = fake()->numberBetween(800, $attributes['planned_seconds']);
            $endedAt = (clone $startedAt)->addSeconds($actualSeconds);

            return [
                'actual_seconds' => $actualSeconds,
                'ended_at' => $endedAt,
            ];
        });
    }

    /**
     * Indicate that the session is paused.
     */
    public function paused(): static
    {
        return $this->state(fn(array $attributes) => [
            'interruptions_count' => fake()->numberBetween(1, 3),
            'paused_seconds' => fake()->numberBetween(60, 300), // 1-5 minutes
            'last_paused_at' => fake()->dateTimeBetween('-10 minutes', 'now'),
        ]);
    }
}
