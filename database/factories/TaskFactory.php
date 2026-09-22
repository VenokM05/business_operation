<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Task;
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
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'assigned_to' => null,
            'priority' => fake()->randomElement(Priority::cases()),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'created_by' => null,
        ];
    }
}
