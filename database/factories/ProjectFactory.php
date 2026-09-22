<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'name' => fake()->randomElement(['Website Redesign', 'Mobile App', 'API Integration', 'Data Migration', 'Cloud Migration', 'Support Portal', 'Analytics Dashboard', 'E-commerce Platform', 'Brand Refresh', 'System Audit']).' '.fake()->numerify('##'),
            'client_id' => Client::factory(),
            'description' => fake()->sentence(12),
            'project_manager_id' => null,
            'start_date' => $start,
            'end_date' => fake()->dateTimeBetween($start, '+8 months'),
            'priority' => fake()->randomElement(Priority::cases()),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'budget' => fake()->randomFloat(2, 5000, 250000),
            'currency' => 'USD',
        ];
    }
}
