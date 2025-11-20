<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Course;
use App\Models\UserLessonProgress;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        // Izinkan mentor masuk panel admin, menu & aksi dibatasi oleh Policy
        // Deteksi role secara case-insensitive untuk mencegah mismatch di production
        $roleNames = $this->getRoleNames()->map(fn($n) => strtolower($n));
        if ($roleNames->isEmpty() && method_exists($this, 'roles')) {
            $roleNames = $this->roles->pluck('name')->map(fn($n) => strtolower($n));
        }
        return $roleNames->contains('admin')
            || $roleNames->contains('super-admin')
            || $roleNames->contains('mentor');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'whatsapp_number',
        'verification_token',
        'email_verified_at',
        'whatsapp_verified_at',
        'is_account_active',
        'oauth_provider',
        'oauth_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'whatsapp_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_account_active' => 'boolean',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
    
    /**
     * Check if user has purchased a specific course
     */
    public function hasPurchasedCourse($courseId)
    {
        return $this->transactions()
            ->where('course_id', $courseId)
            ->where('is_paid', true)
            ->exists();
    }
    
    /**
     * Get all courses purchased by user
     */
    public function purchasedCourses()
    {
        return $this->belongsToMany(Course::class, 'transactions', 'user_id', 'course_id')
            ->wherePivot('is_paid', true)
            ->withPivot('created_at', 'grand_total_amount')
            ->withTimestamps();
    }
    
    /**
     * Get course purchase transaction
     */
    public function getCoursePurchaseTransaction($courseId)
    {
        return $this->transactions()
            ->where('course_id', $courseId)
            ->where('is_paid', true)
            ->first();
    }
    
    /**
     * Check if user can access a course (purchased course only)
     */
    public function canAccessCourse($courseId)
    {
        // Deteksi role case-insensitive + fallback relasi roles untuk robust di production
        $roleNames = $this->getRoleNames()->map(fn($n) => strtolower($n));
        if ($roleNames->isEmpty() && method_exists($this, 'roles')) {
            $roleNames = $this->roles->pluck('name')->map(fn($n) => strtolower($n));
        }

        // Admin & super-admin selalu boleh
        if ($roleNames->contains('admin') || $roleNames->contains('super-admin')) {
            return true;
        }

        // Mentor aktif untuk course ini boleh akses
        if ($roleNames->contains('mentor')) {
            $isMentorOfCourse = \App\Models\CourseMentor::where('user_id', $this->id)
                ->where('course_id', $courseId)
                ->where('is_active', true)
                ->exists();
            if ($isMentorOfCourse) {
                return true;
            }
        }

        // Student dengan enrolment aktif boleh akses
        $hasActiveEnrollment = \App\Models\CourseStudent::where('user_id', $this->id)
            ->where('course_id', $courseId)
            ->where('is_active', true)
            ->exists();
        if ($hasActiveEnrollment) {
            return true;
        }

        // Fallback: ada transaksi paid untuk course ini
        return $this->hasPurchasedCourse($courseId);
    }

    public function lessonProgress()
    {
        return $this->hasMany(UserLessonProgress::class);
    }

    public function completedLessons()
    {
        return $this->lessonProgress()->completed();
    }

    public function getCourseProgress($courseId)
    {
        $course = Course::with('courseSections.sectionContents')->find($courseId);
        $totalLessons = $course
            ? $course->courseSections->sum(fn($section) => $section->sectionContents->count())
            : 0;
            
        $completedLessons = $this->lessonProgress()
            ->forCourse($courseId)
            ->completed()
            ->count();
            
        return [
            'total' => $totalLessons,
            'completed' => $completedLessons,
            'percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0
        ];
    }

    /**
     * Generate verification token for user
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['verification_token' => $token]);
        return $token;
    }

    /**
     * Verify email
     */
    public function verifyEmail(): void
    {
        $this->update([
            'email_verified_at' => now(),
            // Aktifkan akun jika ada minimal satu metode verifikasi
            'is_account_active' => true
        ]);
    }

    /**
     * Verify WhatsApp
     */
    public function verifyWhatsapp(): void
    {
        $this->update([
            'whatsapp_verified_at' => now(),
            // Aktifkan akun jika ada minimal satu metode verifikasi
            'is_account_active' => true
        ]);
    }

    /**
     * Check if both email and WhatsApp are verified
     */
    public function isFullyVerified(): bool
    {
        return $this->email_verified_at && $this->whatsapp_verified_at;
    }

    /**
     * Check if account is active and fully verified
     */
    public function isAccountActive(): bool
    {
        // Akun dianggap aktif jika flag aktif dan minimal satu verifikasi terpenuhi
        return $this->is_account_active && ($this->email_verified_at || $this->whatsapp_verified_at);
    }
}
