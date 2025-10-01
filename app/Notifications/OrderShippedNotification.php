<?php
// app/Notifications/OrderShippedNotification.php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Shipped - #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order has been shipped.')
            ->line('Order ID: #' . $this->order->id)
            ->line('We will notify you when it is out for delivery.')
            ->action('Track Order', url('/orders/' . $this->order->id))
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'Your order #' . $this->order->id . ' has been shipped.',
            'status' => $this->order->status,
        ];
    }
}
