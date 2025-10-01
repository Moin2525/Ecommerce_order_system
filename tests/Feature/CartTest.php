<?php
// tests/Feature/CartTest.php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->customer()->create();
        $this->product = Product::factory()->create(['stock' => 10]);
    }

    public function test_customer_can_add_product_to_cart()
    {
        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product added to cart successfully',
            ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_more_than_available_stock()
    {
        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cart', [
            'product_id' => $this->product->id,
            'quantity' => 15, // More than available stock
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_customer_can_view_cart()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_items',
                    'total',
                ]
            ]);
    }

    public function test_customer_can_remove_item_from_cart()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        $token = $this->customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/cart/{$cart->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Item removed from cart successfully',
            ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }
}
