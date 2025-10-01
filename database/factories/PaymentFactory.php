<?php
// database/factories/PaymentFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition()
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'status' => $this->faker->randomElement(['success', 'failed', 'refunded']),
            'payment_method' => 'credit_card',
            'transaction_id' => $this->faker->uuid(),
        ];
    }
}
