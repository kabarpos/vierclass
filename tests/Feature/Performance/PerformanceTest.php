<?php

namespace Tests\Feature\Performance;

use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data for performance testing
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create categories
        $categories = Category::factory(10)->create();
        
        // Create users
        User::factory(100)->create();
        
        // Create courses with relationships
        Course::factory(50)->create()->each(function ($course) use ($categories) {
            $course->categories()->attach($categories->random(rand(1, 3)));
        });
        
        // Create transactions
        Transaction::factory(200)->create();
    }

    public function test_course_listing_performance(): void
    {
        // Arrange
        $startTime = microtime(true);
        
        // Act - Get courses with pagination
        $response = $this->get('/api/courses?per_page=20');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(500, $executionTime, 'Course listing should complete within 500ms');
        
        // Check query count
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(10, $queryCount, 'Course listing should use less than 10 queries');
    }

    public function test_course_search_performance(): void
    {
        // Arrange
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Act - Search courses
        $response = $this->get('/api/courses?search=test&category=1');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(800, $executionTime, 'Course search should complete within 800ms');
        
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(15, $queryCount, 'Course search should use less than 15 queries');
    }

    public function test_user_dashboard_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        $courses = Course::factory(5)->create();
        $user->courses()->attach($courses->pluck('id'));
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Act - Load user dashboard
        $response = $this->actingAs($user)->get('/api/user/dashboard');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(600, $executionTime, 'User dashboard should load within 600ms');
        
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(12, $queryCount, 'User dashboard should use less than 12 queries');
    }

    public function test_admin_analytics_performance(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Act - Load admin analytics
        $response = $this->actingAs($admin)->get('/api/admin/analytics');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(1000, $executionTime, 'Admin analytics should load within 1000ms');
        
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(20, $queryCount, 'Admin analytics should use less than 20 queries');
    }

    public function test_concurrent_user_requests(): void
    {
        // Arrange
        $users = User::factory(10)->create();
        $course = Course::factory()->create();
        
        $startTime = microtime(true);
        $responses = [];
        
        // Act - Simulate concurrent requests
        foreach ($users as $user) {
            $responses[] = $this->actingAs($user)->get("/api/courses/{$course->id}");
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        // Assert
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        $averageTime = $totalTime / count($users);
        $this->assertLessThan(100, $averageTime, 'Average response time should be under 100ms per request');
    }

    public function test_database_query_optimization(): void
    {
        // Arrange
        DB::enableQueryLog();
        
        // Act - Load course with all relationships
        $response = $this->get('/api/courses?include=categories,instructor,reviews');
        
        // Assert
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Should use eager loading to minimize queries
        $this->assertLessThan(5, $queryCount, 'Should use eager loading to minimize database queries');
        
        // Check for N+1 query problems
        $selectQueries = array_filter($queries, function ($query) {
            return strpos(strtolower($query['query']), 'select') === 0;
        });
        
        $this->assertLessThan(3, count($selectQueries), 'Should avoid N+1 query problems');
    }

    public function test_cache_performance(): void
    {
        // Arrange
        $cacheKey = 'popular_courses';
        Cache::forget($cacheKey);
        
        // Act - First request (cache miss)
        $startTime = microtime(true);
        $response1 = $this->get('/api/courses/popular');
        $firstRequestTime = (microtime(true) - $startTime) * 1000;
        
        // Act - Second request (cache hit)
        $startTime = microtime(true);
        $response2 = $this->get('/api/courses/popular');
        $secondRequestTime = (microtime(true) - $startTime) * 1000;
        
        // Assert
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        // Second request should be significantly faster due to caching
        $this->assertLessThan($firstRequestTime * 0.5, $secondRequestTime, 
            'Cached request should be at least 50% faster');
    }

    public function test_large_dataset_pagination(): void
    {
        // Arrange - Create large dataset
        Course::factory(1000)->create();
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Act - Request last page
        $response = $this->get('/api/courses?page=50&per_page=20');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(1000, $executionTime, 'Large dataset pagination should complete within 1000ms');
        
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(5, $queryCount, 'Pagination should use minimal queries');
    }

    public function test_file_upload_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['instructor_id' => $user->id]);
        
        // Create a test file (simulate large file)
        $fileContent = str_repeat('test content ', 10000); // ~100KB
        $tempFile = tmpfile();
        fwrite($tempFile, $fileContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $startTime = microtime(true);
        
        // Act - Upload file
        $response = $this->actingAs($user)->post("/api/courses/{$course->id}/materials", [
            'title' => 'Test Material',
            'file' => new \Illuminate\Http\UploadedFile($tempPath, 'test.pdf', 'application/pdf', null, true)
        ]);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(201);
        $this->assertLessThan(5000, $executionTime, 'File upload should complete within 5000ms');
        
        // Clean up
        fclose($tempFile);
    }

    public function test_memory_usage_optimization(): void
    {
        // Arrange
        $initialMemory = memory_get_usage(true);
        
        // Act - Process large dataset
        $courses = Course::with(['categories', 'instructor'])->get();
        $processedData = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'categories' => $course->categories->pluck('name'),
                'instructor' => $course->instructor->name
            ];
        });
        
        $peakMemory = memory_get_peak_usage(true);
        $memoryUsed = $peakMemory - $initialMemory;
        
        // Assert
        $this->assertNotEmpty($processedData);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB');
    }

    public function test_api_rate_limiting_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        $responses = [];
        
        $startTime = microtime(true);
        
        // Act - Make multiple rapid requests
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->actingAs($user)->get('/api/courses');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $successfulRequests = array_filter($responses, function ($response) {
            return $response->status() === 200;
        });
        
        $this->assertGreaterThan(5, count($successfulRequests), 'Should allow reasonable number of requests');
        $this->assertLessThan(2000, $totalTime, 'Rate limiting should not significantly impact performance');
    }

    public function test_search_index_performance(): void
    {
        // Arrange
        $searchTerms = ['programming', 'web development', 'javascript', 'php', 'laravel'];
        
        foreach ($searchTerms as $term) {
            $startTime = microtime(true);
            
            // Act - Perform search
            $response = $this->get("/api/courses?search=" . urlencode($term));
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            // Assert
            $response->assertStatus(200);
            $this->assertLessThan(300, $executionTime, "Search for '{$term}' should complete within 300ms");
        }
    }

    public function test_session_handling_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        $startTime = microtime(true);
        
        // Act - Multiple authenticated requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user)->get('/api/user/profile');
            $response->assertStatus(200);
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $averageTime = $totalTime / 5;
        $this->assertLessThan(100, $averageTime, 'Session-based requests should average under 100ms');
    }

    public function test_error_handling_performance(): void
    {
        // Arrange
        $startTime = microtime(true);
        
        // Act - Request non-existent resource
        $response = $this->get('/api/courses/99999');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(404);
        $this->assertLessThan(100, $executionTime, 'Error responses should be fast');
    }

    public function test_middleware_stack_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Act - Request that goes through full middleware stack
        $response = $this->actingAs($user)->get('/api/courses');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(200, $executionTime, 'Middleware stack should add minimal overhead');
        
        // Middleware shouldn't add excessive database queries
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(8, $queryCount, 'Middleware should not add excessive queries');
    }

    public function test_json_response_serialization_performance(): void
    {
        // Arrange
        $courses = Course::with(['categories', 'instructor'])->limit(20)->get();
        
        $startTime = microtime(true);
        
        // Act - Serialize to JSON
        $response = $this->get('/api/courses?per_page=20');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $response->assertStatus(200);
        $this->assertLessThan(300, $executionTime, 'JSON serialization should be fast');
        
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_validation_performance(): void
    {
        // Arrange
        $user = User::factory()->create();
        $largePayload = [
            'title' => str_repeat('a', 1000),
            'description' => str_repeat('b', 5000),
            'price' => 100000,
            'category_ids' => range(1, 50)
        ];
        
        $startTime = microtime(true);
        
        // Act - Submit large payload for validation
        $response = $this->actingAs($user)->post('/api/courses', $largePayload);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Assert
        $this->assertLessThan(500, $executionTime, 'Validation should complete quickly even with large payloads');
    }
}