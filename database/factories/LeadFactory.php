<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /** @var class-string<\App\Models\Lead> */
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null, // set explicitly when attaching to a tenant
            'zip' => $this->faker->postcode(),
            'service' => $this->faker->randomElement(['plumbing', 'electrical', 'hvac', 'roofing']),
            'name' => $this->faker->optional()->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'source' => $this->faker->optional()->randomElement(['landing-page', 'adwords', 'referral']),
            'status' => 'new',
            'meta' => [],
        ];
    }
}
