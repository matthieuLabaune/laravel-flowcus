<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'noteable_type' => 'App\\Models\\Task',
            'noteable_id' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the note is for a specific noteable model.
     */
    public function forNoteable(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'noteable_type' => $type,
            'noteable_id' => $id,
        ]);
    }
}
