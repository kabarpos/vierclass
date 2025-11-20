<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subTotal = $this->faker->numberBetween(99000, 799000);
        $adminFee = $this->faker->numberBetween(5000, 25000); // Manual admin fee
        $grandTotal = $subTotal + $adminFee;

        $startDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $pricingDuration = $this->faker->randomElement([1, 3, 6, 12]); // months
        $endDate = (clone $startDate)->modify("+{$pricingDuration} months");

        return [
            'booking_trx_id' => 'TRX' . strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'sub_total_amount' => $subTotal,
            'admin_fee_amount' => $adminFee,
            'grand_total_amount' => $grandTotal,
            'is_paid' => $this->faker->boolean(80), // 80% chance to be paid
            'payment_type' => $this->faker->randomElement(['Manual', 'Midtrans']),
            'proof' => $this->faker->boolean(70) ? 'https://via.placeholder.com/400x600/22c55e/ffffff?text=Payment+Proof' : null,
            'started_at' => $startDate,
            'ended_at' => $endDate,
        ];
    }

    /**
     * Indicate that the transaction should be paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => true,
            'proof' => 'https://via.placeholder.com/400x600/22c55e/ffffff?text=Payment+Proof',
        ]);
    }

    /**
     * Indicate that the transaction should be unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => false,
            'proof' => null,
        ]);
    }
}