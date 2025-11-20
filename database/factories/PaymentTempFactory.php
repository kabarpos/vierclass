<?php

namespace Database\Factories;

use App\Models\PaymentTemp;
use App\Models\User;
use App\Models\Course;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentTemp>
 */
class PaymentTempFactory extends Factory
{
    protected $model = PaymentTemp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subTotal = $this->faker->numberBetween(99000, 799000);
        $adminFee = $this->faker->numberBetween(5000, 25000);
        $discountAmount = $this->faker->numberBetween(0, 50000);
        $grandTotal = $subTotal + $adminFee - $discountAmount;

        return [
            'order_id' => 'ORDER-' . strtoupper(Str::random(10)) . '-' . time(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'sub_total_amount' => $subTotal,
            'admin_fee_amount' => $adminFee,
            'discount_amount' => $discountAmount,
            'discount_id' => $discountAmount > 0 ? Discount::factory() : null,
            'grand_total_amount' => $grandTotal,
            'snap_token' => 'snap_' . strtolower(Str::random(32)),
            'discount_data' => $discountAmount > 0 ? [
                'code' => 'TEST' . $this->faker->numerify('##'),
                'name' => 'Test Discount',
                'type' => $this->faker->randomElement(['percentage', 'fixed']),
                'value' => $this->faker->numberBetween(5, 50),
                'amount' => $discountAmount
            ] : null,
            'expires_at' => Carbon::now()->addMinutes($this->faker->numberBetween(15, 120)),
            'status' => 'pending',
        ];
    }

    /**
     * Create a payment temp that's about to expire.
     */
    public function aboutToExpire(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->addMinutes($this->faker->numberBetween(1, 5)),
        ]);
    }

    /**
     * Create an expired payment temp.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    /**
     * Create a payment temp without discount.
     */
    public function withoutDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_amount' => 0,
            'discount_id' => null,
            'discount_data' => null,
            'grand_total_amount' => $attributes['sub_total_amount'] + $attributes['admin_fee_amount'],
        ]);
    }

    /**
     * Create a payment temp with specific discount.
     */
    public function withDiscount(array $discountData = null): static
    {
        $discountAmount = $this->faker->numberBetween(10000, 100000);
        $defaultDiscountData = [
            'code' => 'FACTORY' . $this->faker->numerify('##'),
            'name' => 'Factory Test Discount',
            'type' => 'percentage',
            'value' => 15,
            'amount' => $discountAmount
        ];

        return $this->state(fn (array $attributes) => [
            'discount_amount' => $discountAmount,
            'discount_id' => Discount::factory(),
            'discount_data' => $discountData ?? $defaultDiscountData,
            'grand_total_amount' => $attributes['sub_total_amount'] + $attributes['admin_fee_amount'] - $discountAmount,
        ]);
    }

    /**
     * Create a payment temp with specific course.
     */
    public function forCourse($course): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => is_object($course) ? $course->id : $course,
            'sub_total_amount' => is_object($course) ? $course->price : $this->faker->numberBetween(99000, 799000),
        ]);
    }

    /**
     * Create a payment temp for specific user.
     */
    public function forUser($user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => is_object($user) ? $user->id : $user,
        ]);
    }

    /**
     * Create a payment temp with long expiry.
     */
    public function longExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->addHours($this->faker->numberBetween(2, 24)),
        ]);
    }

    /**
     * Create a payment temp with specific order ID.
     */
    public function withOrderId(string $orderId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Create a payment temp with specific snap token.
     */
    public function withSnapToken(string $snapToken): static
    {
        return $this->state(fn (array $attributes) => [
            'snap_token' => $snapToken,
        ]);
    }

    /**
     * Create a payment temp with high amount.
     */
    public function highAmount(): static
    {
        $subTotal = $this->faker->numberBetween(500000, 2000000);
        $adminFee = $this->faker->numberBetween(15000, 50000);
        
        return $this->state(fn (array $attributes) => [
            'sub_total_amount' => $subTotal,
            'admin_fee_amount' => $adminFee,
            'grand_total_amount' => $subTotal + $adminFee - ($attributes['discount_amount'] ?? 0),
        ]);
    }

    /**
     * Create a payment temp with low amount.
     */
    public function lowAmount(): static
    {
        $subTotal = $this->faker->numberBetween(50000, 150000);
        $adminFee = $this->faker->numberBetween(2500, 7500);
        
        return $this->state(fn (array $attributes) => [
            'sub_total_amount' => $subTotal,
            'admin_fee_amount' => $adminFee,
            'grand_total_amount' => $subTotal + $adminFee - ($attributes['discount_amount'] ?? 0),
        ]);
    }
}