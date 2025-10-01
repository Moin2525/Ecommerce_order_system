<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends ApiController
{
    public function processPayment(Request $request, $orderId)
    {
        try {
            $order = Order::where('user_id', $request->user()->id)
                ->findOrFail($orderId);

            // Check if order already has a successful payment
            if ($order->payments()->where('status', 'success')->exists()) {
                return $this->error('Order already has a successful payment', 422);
            }

            // Mock payment processing
            $paymentSuccess = $this->mockPaymentProcessing();

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => $paymentSuccess ? 'success' : 'failed',
                'payment_method' => 'credit_card',
                'transaction_id' => $paymentSuccess ? 'TXN_' . uniqid() : null,
            ]);

            if ($paymentSuccess) {
                $order->update(['status' => 'confirmed']);
            }

            return $this->success($payment, $paymentSuccess ? 'Payment successful' : 'Payment failed');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Order not found');
        } catch (\Exception $e) {
            return $this->error('Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with('order')->findOrFail($id);

            return $this->success($payment, 'Payment details retrieved successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Payment not found');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve payment details: ' . $e->getMessage(), 500);
        }
    }

    private function mockPaymentProcessing()
    {
        // Mock payment processing - 80% success rate
        return rand(1, 100) <= 80;
    }
}
