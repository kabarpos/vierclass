<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'thumbnail',
        'about',
        'is_popular',
        'category_id',
        'price',
        'original_price',
        'admin_fee_amount',
    ];

    protected $casts = [
        'price' => 'integer',
        'original_price' => 'integer',
        'admin_fee_amount' => 'integer',
        'is_popular' => 'boolean',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(CourseBenefit::class);
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'course_id');
    }

    public function courseStudents(): HasMany
    {
        return $this->hasMany(CourseStudent::class, 'course_id');
    }

    public function courseMentors(): HasMany
    {
        return $this->hasMany(CourseMentor::class, 'course_id');
    }

    /**
     * SectionContents yang terkait dengan Course melalui CourseSection.
     */
    public function sectionContents(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\SectionContent::class,
            \App\Models\CourseSection::class,
            'course_id',         // FK pada CourseSection yang mengacu ke Course
            'course_section_id', // FK pada SectionContent yang mengacu ke CourseSection
            'id',                // Kunci lokal Course
            'id'                 // Kunci lokal CourseSection
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getContentCountAttribute()
    {
        // Hitung total konten (lessons) dengan satu query agregasi tanpa lazy load
        return \App\Models\SectionContent::whereHas('courseSection', function ($q) {
            $q->where('course_id', $this->id);
        })->count();
    }

    // Add scope for eager loading common relationships
    public function scopeWithFullDetails($query)
    {
        return $query->with([
            'category',
            'benefits',
            'courseSections.sectionContents',
            'courseMentors.mentor'
        ]);
    }

    // Add scope for listing with counts
    public function scopeWithCounts($query)
    {
        return $query->withCount([
            'courseSections',
            'courseStudents',
            'courseMentors'
        ]);
    }

    // Add accessor for better performance
    public function getStudentCountAttribute()
    {
        return $this->course_students_count ?? $this->courseStudents()->count();
    }

    // Add accessor for section count
    public function getSectionCountAttribute()
    {
        return $this->course_sections_count ?? $this->courseSections()->count();
    }
    
    /**
     * Check if a user has purchased this course
     */
    public function isPurchasedByUser($userId)
    {
        return \App\Models\Transaction::where('user_id', $userId)
            ->where('course_id', $this->id)
            ->where('is_paid', true)
            ->exists();
    }
    
    /**
     * Get all users who have purchased this course
     */
    public function purchasedBy()
    {
        return $this->hasManyThrough(
            \App\Models\User::class,
            \App\Models\Transaction::class,
            'course_id', // Foreign key on transactions table
            'id', // Foreign key on users table
            'id', // Local key on courses table
            'user_id' // Local key on transactions table
        )->where('transactions.is_paid', true);
    }
    
    /**
     * Get transactions for this course
     */
    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'course_id');
    }
    
    /**
     * Get course revenue
     */
    public function getRevenueAttribute()
    {
        return $this->transactions()
            ->where('is_paid', true)
            ->sum('grand_total_amount');
    }
    
    /**
     * Get course sales count
     */
    public function getSalesCountAttribute()
    {
        return $this->transactions()
            ->where('is_paid', true)
            ->count();
    }
}
