<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 2 admins first
        $admins = \App\Models\User::factory()->admin()->count(2)->create();

        // Create 10 customers
        $customers = \App\Models\User::factory()->customer()->count(10)->create();

        // Create 5 categories first
        $categories = \App\Models\Category::factory()->count(5)->create();

        // Create 20 products using existing categories
        $products = \App\Models\Product::factory()->count(20)->create([
            'category_id' => function() use ($categories) {
                return $categories->random()->id;
            }
        ]);

        // Create 10 carts using existing customers and products
        \App\Models\Cart::factory()->count(10)->create([
            'user_id' => function() use ($customers) {
                return $customers->random()->id;
            },
            'product_id' => function() use ($products) {
                return $products->random()->id;
            }
        ]);

        // Create 15 orders using existing customers
        $orders = \App\Models\Order::factory()->count(15)->create([
            'user_id' => function() use ($customers) {
                return $customers->random()->id;
            }
        ]);

        // Create order items for each order
        $orders->each(function($order) use ($products) {
            \App\Models\OrderItem::factory()->count(rand(1, 5))->create([
                'order_id' => $order->id,
                'product_id' => function() use ($products) {
                    return $products->random()->id;
                },
                'price' => function($attributes) use ($products) {
                    return $products->find($attributes['product_id'])->price;
                }
            ]);
        });

        // Create payments for orders
        \App\Models\Payment::factory()->count(15)->create([
            'order_id' => function() use ($orders) {
                return $orders->random()->id;
            },
            'amount' => function($attributes) use ($orders) {
                return $orders->find($attributes['order_id'])->total_amount;
            }
        ]);
    }
}
