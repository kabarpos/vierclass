<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentTemp extends Model
{
    use HasFactory;
    protected $table = 'payment_temp';
    
    protected $fillable = [
        'order_id',
        'user_id',
        'course_id',
        'sub_total_amount',
        'admin_fee_amount',
        'discount_amount',
        'discount_id',
        'grand_total_amount',
        'snap_token',
        'discount_data',
        'expires_at',
        'status'
    ];
    
    protected $casts = [
        'sub_total_amount' => 'decimal:2',
        'admin_fee_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total_amount' => 'decimal:2',
        'discount_data' => 'array',
        'expires_at' => 'datetime'
    ];
    
    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relationship with Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Relationship with Discount
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
    
    /**
     * Create payment temp record with auto-expiry
     */
    public static function createPaymentRecord(array $data): self
    {
        $data['expires_at'] = Carbon::now()->addHours(2); // Expire in 2 hours
        return self::create($data);
    }
    
    /**
     * Find payment by order ID
     */
    public static function findByOrderId(string $orderId): ?self
    {
        return self::where('order_id', $orderId)->first();
    }
    
    /**
     * Clean up expired records
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
    
    /**
     * Get discount data as array
     */
    public function getDiscountInfo(): ?array
    {
        return $this->discount_data;
    }
}
