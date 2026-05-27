<?php

namespace Database\Factories;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_user_id' => User::factory(),
            'status' => Referral::STATUS_PENDING,
            'total_commission_paid' => 0,
        ];
    }

    public function rewarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Referral::STATUS_FIRST_PURCHASE_REWARDED,
            'first_purchase_rewarded_at' => now(),
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Referral::STATUS_VOID,
        ]);
    }
}
