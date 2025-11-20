<?php

use App\Http\Controllers\Api\LessonProgressController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\Admin\TransactionExportController;

Route::get('/', [FrontController::class, 'index'])->name('front.index');

// Health Check Endpoint
Route::get('/health', function () {
    try {
        // Check database connection
        $dbStatus = 'connected';
        $dbConnection = null;
        try {
            $pdo = DB::connection()->getPdo();
            $dbConnection = DB::connection()->getName();
        } catch (Exception $e) {
            $dbStatus = 'disconnected';
        }

        // Check cache connection
        $cacheStatus = 'connected';
        try {
            Cache::put('health_check', 'test', 10);
            Cache::get('health_check');
        } catch (Exception $e) {
            $cacheStatus = 'disconnected';
        }

        $overallStatus = ($dbStatus === 'connected' && $cacheStatus === 'connected') ? 'ok' : 'warning';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now(),
            'version' => config('app.version', '1.0.0'),
            'services' => [
                'database' => [
                    'status' => $dbStatus,
                    'connection' => $dbConnection
                ],
                'cache' => [
                    'status' => $cacheStatus,
                    'driver' => config('cache.default')
                ]
            ],
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug')
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'timestamp' => now(),
            'error' => $e->getMessage()
        ], 500);
    }
})->name('health.check');

Route::get('/courses', [FrontController::class, 'courses'])->name('front.courses'); 
Route::get('/peraturan-layanan', [FrontController::class, 'termsOfService'])->name('front.terms-of-service');
Route::get('/course/{course:slug}', [FrontController::class, 'courseDetails'])->name('front.course.details');
Route::get('/course/{course:slug}/preview/{sectionContent}', [FrontController::class, 'previewContent'])->name('front.course.preview');
Route::get('/course/{course:slug}/checkout', [FrontController::class, 'courseCheckout'])->name('front.course.checkout');
Route::post('/course/{course:slug}/checkout', function() {
    return response()->json(['error' => 'Form submission not allowed. Please use the Pay Now button.'], 400);
});

// Discount validation and removal routes
Route::post('/course/{course:slug}/validate-discount', [FrontController::class, 'validateDiscount'])->name('front.course.validate-discount');
Route::post('/course/{course:slug}/remove-discount', [FrontController::class, 'removeDiscount'])->name('front.course.remove-discount');

// Redirect old dashboard learning route to unified preview route
Route::get('/dashboard/learning/{course:slug}/{courseSection}/{sectionContent}', function(\App\Models\Course $course, $courseSection, \App\Models\SectionContent $sectionContent) {
    return redirect()->route('front.course.preview', ['course' => $course->slug, 'sectionContent' => $sectionContent->id]);
})->middleware(['auth'])->name('dashboard.course.learning');

Route::match(['get', 'post'], '/booking/payment/midtrans/notification',
[FrontController::class, 'paymentMidtransNotification'])
    ->middleware('throttle:webhook')
    ->name('front.payment_midtrans_notification');

// Alias route untuk konfigurasi Midtrans yang menunjuk ke /payment/notification
Route::match(['get', 'post'], '/payment/notification', [FrontController::class, 'paymentMidtransNotification'])
    // Pastikan CSRF tidak memblokir webhook POST dari Midtrans
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:webhook')
    ->name('front.payment_midtrans_notification_alias');


// WhatsApp Verification routes (public)
Route::get('/verify/{id}/{token}', [VerificationController::class, 'verifyWhatsapp'])
    ->name('whatsapp.verification.verify')
    ->where(['id' => '[0-9]+', 'token' => '[a-zA-Z0-9]+']);

// Email Verification via token (public)
Route::get('/verify-email/{id}/{token}', [VerificationController::class, 'verifyEmail'])
    ->name('email.verification.verify')
    ->where(['id' => '[0-9]+', 'token' => '[a-zA-Z0-9]+']);

Route::post('/verification/resend', [VerificationController::class, 'resend'])
    ->middleware('throttle:password-reset')
    ->name('whatsapp.verification.resend');

Route::get('/verification/status', [VerificationController::class, 'status'])
    ->middleware('auth')
    ->name('whatsapp.verification.status');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route for deleting profile is disabled to prevent user self-deletion

    Route::middleware(['role:student|admin|super-admin|mentor', 'verified.account'])->group(function () {
        Route::get('/dashboard/courses/', [CourseController::class, 'index'])
        ->name('dashboard');

        Route::get('/dashboard/search/courses', [CourseController::class, 'search_courses'])
        ->name('dashboard.search.courses');
        


        // Course access routes - per-course purchase model only
        Route::middleware(['check.course.access'])->group(function () {
            Route::get('/dashboard/join/{course:slug}', [CourseController::class, 'join'])
            ->name('dashboard.course.join');

            Route::get('/dashboard/learning/{course:slug}/finished', [CourseController::class, 'learning_finished'])
            ->name('dashboard.course.learning.finished');
        });

        Route::get('/checkout/success', [FrontController::class, 'checkout_success'])
        ->name('front.checkout.success');
        
        Route::post('/booking/payment/courses/midtrans', [FrontController::class, 'paymentStoreCoursesMidtrans'])
        ->middleware('throttle:payment')
        ->name('front.payment_store_courses_midtrans');
    });

    // API Routes for Lesson Progress (JSON responses)
    Route::prefix('api')->middleware(['verified.account', 'check.course.access', 'throttle:api'])->group(function () {
        Route::get('/lesson-progress', [LessonProgressController::class, 'index'])
            ->name('api.lesson-progress.index');
        
        Route::post('/lesson-progress', [LessonProgressController::class, 'store'])
            ->name('api.lesson-progress.store');
        
        Route::get('/lesson-progress/{sectionContent}', [LessonProgressController::class, 'show'])
            ->name('api.lesson-progress.show');
        
        Route::put('/lesson-progress/{sectionContent}', [LessonProgressController::class, 'update'])
            ->name('api.lesson-progress.update');
        
        Route::get('/course-progress/{course}', [LessonProgressController::class, 'courseProgress'])
            ->name('api.course-progress');
    });

    // Admin Transactions Export (CSV) endpoint
    Route::prefix('api/admin')->middleware(['role:admin|super-admin', 'throttle:api'])->group(function () {
        Route::get('/transactions/export', [TransactionExportController::class, 'export'])
            ->name('api.admin.transactions.export');
    });


});

require __DIR__.'/auth.php';

// Security Report Endpoints
Route::post('/security/csp-report', [App\Http\Controllers\SecurityReportController::class, 'cspReport'])->name('security.csp-report');
Route::post('/security/hpkp-report', [App\Http\Controllers\SecurityReportController::class, 'hpkpReport'])->name('security.hpkp-report');
Route::post('/security/ct-report', [App\Http\Controllers\SecurityReportController::class, 'ctReport'])->name('security.ct-report');
Route::post('/security/nel-report', [App\Http\Controllers\SecurityReportController::class, 'nelReport'])->name('security.nel-report');
// Tripay payment routes (berdampingan dengan Midtrans, tidak mengganggu flow yang ada)
Route::post('/front/payment/store/courses/tripay', [\App\Http\Controllers\FrontController::class, 'paymentStoreCoursesTripay'])
    ->name('front.payment_store_courses_tripay');
Route::post('/front/payment/tripay/notification', [\App\Http\Controllers\FrontController::class, 'paymentTripayNotification'])
    ->name('front.payment_tripay_notification');
