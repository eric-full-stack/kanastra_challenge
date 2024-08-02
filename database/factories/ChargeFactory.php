<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Charge>
 */
class ChargeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Charge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory()->create()->government_id,
            'debt_id' => $this->faker->uuid,
            'debt_amount' => $this->faker->randomFloat(2, 100, 1000),
            'debt_due_date' => $this->faker->date(),
            'status' => 'pending',
            'boleto_bar_code' => null,
            'boleto_url' => null,
            'mailed_at' => null,
            'boleto_generated_at' => null,
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the charge's status is completed.
     *
     * @return \Database\Factories\ChargeFactory
     */
    public function completed(): ChargeFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'boleto_bar_code' => $this->faker->uuid,
                'boleto_url' => $this->faker->url,
                'mailed_at' => now(),
                'boleto_generated_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the charge's status is failed.
     *
     * @return \Database\Factories\ChargeFactory
     */
    public function failed(): ChargeFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'error_message' => $this->faker->sentence,
            ];
        });
    }

    /**
     * Indicate that the charge's status is pending.
     *
     * @return \Database\Factories\ChargeFactory
     */
    public function pending(): ChargeFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }
}
