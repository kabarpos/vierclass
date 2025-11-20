<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserLessonProgress;
use App\Models\SectionContent;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LessonProgressController extends Controller
{
    /**
     * Get lesson progress for current user and course.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $userId = Auth::id();
        $courseId = $request->course_id;

        $progress = UserLessonProgress::forUser($userId)
            ->forCourse($courseId)
            ->with('sectionContent')
            ->get()
            ->keyBy('section_content_id');

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ]);
    }

    /**
     * Mark lesson as complete.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'section_content_id' => 'required|exists:section_contents,id',
            'time_spent' => 'integer|min:0|max:3600' // Max 1 hour per lesson
        ]);

        $userId = Auth::id();
        $courseId = $request->course_id;
        $sectionContentId = $request->section_content_id;
        $timeSpent = $request->time_spent ?? 0;

        // Verify user has access to this course
        $user = Auth::user();
        if (!$user->canAccessCourse($courseId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course access required to track progress.'
            ], 403);
        }

        // Verify section content belongs to the course
        $sectionContent = SectionContent::whereHas('courseSection', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->find($sectionContentId);

        if (!$sectionContent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course.'
            ], 404);
        }

        // Create or update progress
        $progress = UserLessonProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'section_content_id' => $sectionContentId
            ],
            [
                'course_id' => $courseId,
                'is_completed' => true,
                'completed_at' => now(),
                'time_spent_seconds' => $timeSpent
            ]
        );

        // Calculate updated course progress
        $courseProgress = $user->getCourseProgress($courseId);

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson marked as completed!',
            'data' => [
                'lesson_progress' => $progress,
                'course_progress' => $courseProgress
            ]
        ]);
    }

    /**
     * Get lesson status.
     */
    public function show(Request $request, string $sectionContentId): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $userId = Auth::id();
        $courseId = $request->course_id;

        $progress = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('section_content_id', $sectionContentId)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'is_completed' => $progress ? $progress->is_completed : false,
                'completed_at' => $progress?->completed_at,
                'time_spent' => $progress?->formatted_time_spent ?? '00:00'
            ]
        ]);
    }

    /**
     * Update time spent on lesson.
     */
    public function update(Request $request, string $sectionContentId): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'time_spent' => 'required|integer|min:1|max:3600'
        ]);

        $userId = Auth::id();
        $courseId = $request->course_id;
        $timeSpent = $request->time_spent;

        $progress = UserLessonProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
                'section_content_id' => $sectionContentId
            ],
            [
                'time_spent_seconds' => $timeSpent
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Time tracking updated.',
            'data' => [
                'time_spent' => $progress->formatted_time_spent
            ]
        ]);
    }

    /**
     * Get course progress summary.
     */
    public function courseProgress(Request $request, string $courseId): JsonResponse
    {
        $user = Auth::user();
        $courseProgress = $user->getCourseProgress($courseId);

        $completedLessons = UserLessonProgress::forUser($user->id)
            ->forCourse($courseId)
            ->completed()
            ->with('sectionContent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'progress' => $courseProgress,
                'completed_lessons' => $completedLessons
            ]
        ]);
    }
}
