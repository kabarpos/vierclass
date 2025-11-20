<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);
        $value = $type === 'percentage' 
            ? $this->faker->numberBetween(5, 50) // 5% - 50%
            : $this->faker->numberBetween(10000, 100000); // 10k - 100k

        $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $endDate = $this->faker->dateTimeBetween('now', '+3 months');

        return [
            'name' => $this->faker->words(3, true) . ' Discount',
            'code' => strtoupper($this->faker->lexify('????') . $this->faker->numberBetween(10, 9999)),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'value' => $value,
            'minimum_amount' => $this->faker->optional(0.7)->numberBetween(50000, 200000), // 70% chance to have minimum
            'maximum_discount' => $type === 'percentage' 
                ? $this->faker->optional(0.5)->numberBetween(25000, 150000) // 50% chance for percentage type
                : null,
            'usage_limit' => $this->faker->optional(0.6)->numberBetween(10, 1000), // 60% chance to have limit
            'used_count' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $this->faker->boolean(85), // 85% chance to be active
        ];
    }

    /**
     * Indicate that the discount should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addMonths(1),
        ]);
    }

    /**
     * Indicate that the discount should be expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the discount should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a percentage discount.
     */
    public function percentage(int $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => $value ?? $this->faker->numberBetween(5, 50),
            'maximum_discount' => $this->faker->numberBetween(25000, 150000),
        ]);
    }

    /**
     * Create a fixed amount discount.
     */
    public function fixed(int $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => $value ?? $this->faker->numberBetween(10000, 100000),
            'maximum_discount' => null,
        ]);
    }

    /**
     * Create a stackable discount (can be combined with others).
     */
    public function stackable(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $attributes['name'] . ' (Stackable)',
            'code' => 'STACK' . $this->faker->numerify('####'),
        ]);
    }

    /**
     * Create a discount with usage limit.
     */
    public function withUsageLimit(int $limit = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => $limit,
            'used_count' => $this->faker->numberBetween(0, max(1, $limit - 10)),
        ]);
    }

    /**
     * Create a discount that's nearly exhausted.
     */
    public function nearlyExhausted(): static
    {
        $limit = $this->faker->numberBetween(10, 50);
        return $this->state(fn (array $attributes) => [
            'usage_limit' => $limit,
            'used_count' => $limit - $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a discount that's fully used.
     */
    public function fullyUsed(): static
    {
        $limit = $this->faker->numberBetween(10, 50);
        return $this->state(fn (array $attributes) => [
            'usage_limit' => $limit,
            'used_count' => $limit,
        ]);
    }

    /**
     * Create a discount with minimum amount requirement.
     */
    public function withMinimumAmount(int $amount = 100000): static
    {
        return $this->state(fn (array $attributes) => [
            'minimum_amount' => $amount,
        ]);
    }
}