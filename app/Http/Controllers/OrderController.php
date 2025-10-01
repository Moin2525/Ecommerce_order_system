<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\OrderShippedNotification;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends ApiController
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        try {
            $orders = Order::with(['orderItems.product', 'payments'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get();

            return $this->success($orders, 'Orders retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $order = $this->orderService->createOrderFromCart($request->user());

            return $this->success($order, 'Order created successfully', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create order: ' . $e->getMessage(), 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            ]);

            $order = Order::with('user')->findOrFail($id);
            $oldStatus = $order->status;
            $order->update(['status' => $validated['status']]);

            // Send notification when order is shipped
            if ($validated['status'] === 'shipped' && $oldStatus !== 'shipped') {
                $order->user->notify(new OrderShippedNotification($order));
            }

            return $this->success($order, 'Order status updated successfully');

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Order not found');
        } catch (\Exception $e) {
            return $this->error('Failed to update order status: ' . $e->getMessage(), 500);
        }
    }

    public function applyDiscount(Request $request, $id)
    {
        try {
            $order = Order::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $discountResult = $this->orderService->applyDiscountToOrder($order, 10); // 10% discount

            return $this->success($discountResult, 'Discount applied successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Order not found');
        } catch (\Exception $e) {
            return $this->error('Failed to apply discount: ' . $e->getMessage(), 500);
        }
    }
}
