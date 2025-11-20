<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class UserLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'user_lesson_progress';
    
    protected $fillable = [
        'user_id',
        'course_id', 
        'section_content_id',
        'is_completed',
        'completed_at',
        'time_spent_seconds'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer'
    ];

    /**
     * Get the user that owns the lesson progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that this progress belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the section content (lesson) that this progress tracks.
     */
    public function sectionContent(): BelongsTo
    {
        return $this->belongsTo(SectionContent::class);
    }

    /**
     * Mark lesson as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => Carbon::now()
        ]);
    }

    /**
     * Update time spent on lesson.
     */
    public function updateTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Scope to get completed lessons for a user.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get progress for a specific course.
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope to get progress for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
