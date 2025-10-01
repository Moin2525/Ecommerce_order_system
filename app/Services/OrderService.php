<?php
// app/Services/OrderService.php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrderFromCart($user, $applyDiscount = false)
    {
        return DB::transaction(function () use ($user, $applyDiscount) {
            // Get user's cart items with products
            $cartItems = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Calculate total amount
            $totalAmount = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            // Apply discount if applicable
            if ($applyDiscount) {
                $totalAmount = $this->applyDiscountToTotal($totalAmount);
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create order items and update product stock
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                // Check stock availability
                if ($product->stock < $cartItem->quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$cartItem->quantity}");
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                ]);

                // Update product stock
                $product->decreaseStock($cartItem->quantity);
            }

            // Clear user's cart
            Cart::where('user_id', $user->id)->delete();

            // Send order confirmation notification (queued)
            $user->notify(new OrderConfirmationNotification($order));

            return $order->load('orderItems.product');
        });
    }

    public function calculateOrderTotal($order)
    {
        return $order->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function applyDiscountToTotal($totalAmount, $discountPercentage = 10)
    {
        $discount = $totalAmount * ($discountPercentage / 100);
        return $totalAmount - $discount;
    }

    public function applyDiscountToOrder($order, $discountPercentage = 10)
    {
        $total = $this->calculateOrderTotal($order);
        $discount = $total * ($discountPercentage / 100);

        $order->update([
            'total_amount' => $total - $discount
        ]);

        return [
            'original_total' => $total,
            'discount_amount' => $discount,
            'final_total' => $total - $discount
        ];
    }

    public function getOrderSummary($order)
    {
        $items = $order->orderItems->map(function ($item) {
            return [
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal
            ];
        });

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'items' => $items,
            'total_amount' => $order->total_amount,
            'item_count' => $order->orderItems->count()
        ];
    }
}
