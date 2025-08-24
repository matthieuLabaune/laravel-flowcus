<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'deadline_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'status' => TaskStatus::Pending,
            'completed_at' => null,
        ];
    }

    public function done(): self
    {
        return $this->state(fn() => [
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);
    }
}
