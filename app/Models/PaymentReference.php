<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentReference extends Model
{
    use HasFactory;

    protected $table = 'payment_references';

    protected $fillable = [
        'merchant_ref',
        'channel', // 'tripay' atau 'midtrans'
        'gateway_reference', // reference dari gateway (Tripay: reference)
        'user_id',
        'course_id',
        'discount_id',
        'discount_amount',
        'amount',
        'status', // UNPAID, PAID, EXPIRED, CANCELED, FAILED
        'paid_amount',
        'payment_method',
        'payment_channel',
        'booking_trx_id',
        'callback_received_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_amount' => 'integer',
        'discount_amount' => 'integer',
        'callback_received_at' => 'datetime',
    ];

    public function discount()
    {
        return $this->belongsTo(\App\Models\Discount::class, 'discount_id');
    }
}

