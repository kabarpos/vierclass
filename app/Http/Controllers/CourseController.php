<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    //
    protected $courseService;
    protected $transactionService;

    public function __construct(
        CourseService $courseService,
        TransactionService $transactionService
    ) {
        $this->courseService = $courseService;
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        $coursesByCategory = $this->courseService->getPurchasedCoursesGroupedByCategory();

        return view('courses.index', compact('coursesByCategory'));
    }



    public function join(Course $course)
    {
        $studentName = $this->courseService->enrollUser($course);
        $firstSectionAndContent = $this->courseService->getFirstSectionAndContent($course);

        return view('courses.success_joined', array_merge(
            compact('course', 'studentName'),
            $firstSectionAndContent
        ));
    }

    public function learning(Course $course, $contentSectionId, $sectionContentId)
    {
        $learningData = $this->courseService->getLearningData($course, $contentSectionId, $sectionContentId);

        return view('courses.learning', $learningData);
    }

    public function learning_finished(Course $course)
    {
        // Eager load untuk mencegah lazy loading di view
        $course->load('category');
        return view('courses.learning_finished', compact('course'));
    }



    public function search_courses(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
        ]);

        $keyword = $request->search;

        // Delegate the search logic to the service
        $courses = $this->courseService->searchCourses($keyword);

        return view('courses.search', compact('courses', 'keyword'));
    }


}
