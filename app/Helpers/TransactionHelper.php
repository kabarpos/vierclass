<?php

namespace App\Helpers;

use App\Models\Transaction;

class TransactionHelper
{
    public static function generateUniqueTrxId(): string
    {
        $prefix = 'DC';
        do {
            // Generate 4 digit random number (1000-9999)
            $randomNumber = mt_rand(1000, 9999);
            $randomString = $prefix . $randomNumber; // DC1234
        } while (Transaction::where('booking_trx_id', $randomString)->exists());

        return $randomString;
    }
}
