<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('+1-###-###-####'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'industry' => fake()->randomElement(['Technology', 'Finance', 'Healthcare', 'Retail', 'Manufacturing', 'Education', 'Logistics']),
            'status' => fake()->randomElement(ClientStatus::cases()),
        ];
    }
}
