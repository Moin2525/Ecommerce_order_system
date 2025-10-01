<?php
// tests/Unit/OrderServiceTest.php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 25.00,
            'stock' => 5,
        ]);
    }

    public function test_calculate_order_total()
    {
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 25.00,
        ]);

        $total = $this->orderService->calculateOrderTotal($order);

        $this->assertEquals(50.00, $total);
    }

    public function test_apply_discount_to_total()
    {
        $total = 100.00;
        $discountedTotal = $this->orderService->applyDiscountToTotal($total, 10);

        $this->assertEquals(90.00, $discountedTotal);
    }

    public function test_create_order_from_cart()
    {
        // Add item to cart
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $order = $this->orderService->createOrderFromCart($this->user);

        $this->assertNotNull($order);
        $this->assertEquals(50.00, $order->total_amount);
        $this->assertEquals('pending', $order->status);
        $this->assertCount(1, $order->orderItems);

        // Check stock is updated
        $this->product->refresh();
        $this->assertEquals(3, $this->product->stock); // 5 - 2 = 3
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        // Add more than available stock
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10, // More than available stock of 5
        ]);

        $this->orderService->createOrderFromCart($this->user);
    }
}
