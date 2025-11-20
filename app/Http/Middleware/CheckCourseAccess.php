<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use Symfony\Component\HttpFoundation\Response;

class CheckCourseAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You need to login to access courses.');
        }
        
        // Get course from route parameter
        $course = $request->route('course');
        
        // If course parameter is not found, continue (might be course list page)
        if (!$course) {
            return $next($request);
        }
        
        // If course is a slug, find the course
        if (is_string($course)) {
            $course = Course::where('slug', $course)->first();
        }
        
        // If course not found, return 404
        if (!$course) {
            abort(404, 'Course not found.');
        }
        
        // Check if user can access the course
        if (!$user->canAccessCourse($course->id)) {
            // Logging diagnostik agar cepat mengetahui penyebab deny
            $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
            $hasPaid = method_exists($user, 'hasPurchasedCourse') ? $user->hasPurchasedCourse($course->id) : null;
            $hasEnrollment = \App\Models\CourseStudent::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->exists();
            $isMentor = \App\Models\CourseMentor::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->exists();
            Log::warning('Course access denied', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'course_slug' => $course->slug,
                'roles' => $roles,
                'has_paid_transaction' => $hasPaid,
                'has_active_enrollment' => $hasEnrollment,
                'is_course_mentor' => $isMentor,
            ]);

            // Redirect ke halaman detail dengan opsi pembelian
            return redirect()->route('front.course.details', $course->slug)
                ->with('error', 'You need to purchase this course to access its content.');
        }
        
        return $next($request);
    }
}
