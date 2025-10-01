<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'amount', 'status', 'payment_method', 'transaction_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function markAsSuccess($transactionId = null)
    {
        $this->update([
            'status' => 'success',
            'transaction_id' => $transactionId
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsRefunded()
    {
        $this->update(['status' => 'refunded']);
    }
}
