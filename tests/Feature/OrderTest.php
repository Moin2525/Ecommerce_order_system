<?php
// tests/Feature/OrderTest.php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->customer()->create();
        $this->product = Product::factory()->create(['stock' => 10, 'price' => 50.00]);
    }

    public function test_customer_can_create_order_from_cart()
    {
        // Add item to cart
        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'total_amount' => 100.00, // 2 * 50.00
            'status' => 'pending',
        ]);

        // Check that cart is cleared
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->customer->id,
        ]);
    }

    public function test_cannot_create_order_with_empty_cart()
    {
        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_customer_can_view_their_orders()
    {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
