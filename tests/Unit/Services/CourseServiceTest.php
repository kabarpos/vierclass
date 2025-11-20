<?php

namespace Tests\Unit\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseStudent;
use App\Models\SectionContent;
use App\Models\User;
use App\Models\UserLessonProgress;
use App\Repositories\CourseRepositoryInterface;
use App\Services\CourseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class CourseServiceTest extends TestCase
{
    use RefreshDatabase;

    private CourseService $courseService;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(CourseRepositoryInterface::class);
        $this->courseService = new CourseService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_enroll_user_creates_new_enrollment(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create();
        
        Auth::shouldReceive('user')->andReturn($user);
        
        // Act
        $result = $this->courseService->enrollUser($course);
        
        // Assert
        $this->assertEquals($user->name, $result);
        $this->assertDatabaseHas('course_students', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
    }

    public function test_enroll_user_does_not_duplicate_enrollment(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create();
        
        // Create existing enrollment
        CourseStudent::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
        
        Auth::shouldReceive('user')->andReturn($user);
        
        // Act
        $result = $this->courseService->enrollUser($course);
        
        // Assert
        $this->assertEquals($user->name, $result);
        $this->assertEquals(1, CourseStudent::where('user_id', $user->id)
            ->where('course_id', $course->id)->count());
    }

    public function test_get_first_section_and_content_returns_correct_data(): void
    {
        // Arrange
        $course = Course::factory()->create();
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $content = SectionContent::factory()->create(['course_section_id' => $section->id]);
        
        // Act
        $result = $this->courseService->getFirstSectionAndContent($course);
        
        // Assert
        $this->assertEquals($section->id, $result['firstSectionId']);
        $this->assertEquals($content->id, $result['firstContentId']);
    }

    public function test_get_first_section_and_content_handles_empty_course(): void
    {
        // Arrange
        $course = Course::factory()->create();
        
        // Act
        $result = $this->courseService->getFirstSectionAndContent($course);
        
        // Assert
        $this->assertNull($result['firstSectionId']);
        $this->assertNull($result['firstContentId']);
    }

    public function test_get_learning_data_calculates_progress_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $content1 = SectionContent::factory()->create(['course_section_id' => $section->id]);
        $content2 = SectionContent::factory()->create(['course_section_id' => $section->id]);
        
        // Create progress for one content
        UserLessonProgress::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'section_content_id' => $content1->id,
            'is_completed' => true,
        ]);
        
        Auth::shouldReceive('user')->andReturn($user);
        
        // Act
        $result = $this->courseService->getLearningData($course, $section->id, $content1->id);
        
        // Assert
        $this->assertEquals(50.0, $result['progressPercentage']); // 1 out of 2 completed
        $this->assertEquals(1, $result['completedLessonsCount']);
        $this->assertTrue($result['isCurrentCompleted']);
    }

    public function test_get_learning_data_handles_no_progress(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $content = SectionContent::factory()->create(['course_section_id' => $section->id]);
        
        Auth::shouldReceive('user')->andReturn($user);
        
        // Act
        $result = $this->courseService->getLearningData($course, $section->id, $content->id);
        
        // Assert
        $this->assertEquals(0.0, $result['progressPercentage']);
        $this->assertEquals(0, $result['completedLessonsCount']);
        $this->assertFalse($result['isCurrentCompleted']);
    }
}