<?php

namespace Database\Factories;

use App\Enums\TickerStatus;
use App\Models\Ticker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticker>
 */
class TickerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => strtoupper($this->faker->unique()->lexify('????')),
            'long_name' => $this->faker->company(),
            'currency' => 'USD',
            'exchange_name' => 'NASDAQ',
            'last_price' => $this->faker->randomFloat(4, 1, 1000),
            'last_fetched_at' => now(),
            'is_hot' => false,
            'is_in_portfolio' => false,
            'status' => TickerStatus::SetupComplete,
        ];
    }

    /**
     * A ticker that has never had its price fetched.
     */
    public function withoutPrice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_price' => null,
            'last_fetched_at' => null,
        ]);
    }
}
