<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Tenant>
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            // Generate a domain sometimes; avoid calling methods on null.
            'domain' => $this->faker->boolean(70)
                ? $this->faker->unique()->domainName()
                : null,
            'plan' => $this->faker->randomElement(['free', 'pro', 'enterprise']),
            'settings' => [],
        ];
    }
}
