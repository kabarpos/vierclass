<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_trx_id', // LMS12345
        'user_id',
        'course_id', // For per-course purchases
        'sub_total_amount',
        'grand_total_amount',
        'admin_fee_amount',
        'discount_amount',
        'discount_id',
        'is_paid',
        'payment_type',
        'proof',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function isActive(): bool
    {
        // For course purchases, check if paid
        return $this->is_paid;
    }
    
    /**
     * Check if this is a course purchase transaction
     */
    public function isCoursePurchase(): bool
    {
        return !is_null($this->course_id);
    }
    
    /**
     * Get the product name (course)
     */
    public function getProductNameAttribute()
    {
        return $this->course->name ?? 'Unknown Course';
    }
    
    /**
     * Get the product type
     */
    public function getProductTypeAttribute()
    {
        return 'course';
    }
}
