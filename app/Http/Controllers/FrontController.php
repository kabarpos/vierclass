<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorResponse;
use App\Helpers\SuccessResponse;
use App\Models\Course;
use App\Models\MidtransSetting;
use App\Services\DiscountService;
use App\Services\PaymentService;
use App\Services\TransactionService;
use App\Services\CourseService;
use App\Services\MidtransService;
use App\Repositories\WebsiteSettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class FrontController extends Controller
{
    protected $transactionService;
    protected $paymentService;
    protected $courseService;
    protected $discountService;
    protected $midtransService;
    protected $websiteSettingRepository;

    public function __construct(
        PaymentService $paymentService,
        TransactionService $transactionService,
        CourseService $courseService,
        DiscountService $discountService,
        MidtransService $midtransService,
        WebsiteSettingRepositoryInterface $websiteSettingRepository
    ) {
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->courseService = $courseService;
        $this->discountService = $discountService;
        $this->midtransService = $midtransService;
        $this->websiteSettingRepository = $websiteSettingRepository;
    }

    /**
     * Generate kode invoice pendek untuk Tripay: format "UP-XXXXX" (5 karakter alfanumerik, unik).
     * Menggunakan Cache::add agar atomic pada penyimpanan, mencegah tabrakan saat banyak request bersamaan.
     */
    protected function generateShortMerchantRef(): string
    {
        $prefix = 'UP-';
        $length = 5;

        while (true) {
            // 5 karakter alfanumerik uppercase
            $random = strtoupper(Str::random($length));

            // Pastikan kombinasi huruf dan angka ada keduanya
            if (!preg_match('/[A-Z]/', $random) || !preg_match('/\d/', $random)) {
                continue; // regenerasi jika tidak memenuhi syarat
            }

            $candidate = $prefix . $random;
            $cacheKey = 'tripay:merchant_ref:' . $candidate;

            // add bersifat atomic di driver yang mendukung (redis/memcached);
            // jika kunci belum ada, add mengembalikan true dan kita pakai kode ini.
            if (Cache::add($cacheKey, true, now()->addDays(30))) {
                return $candidate;
            }
            // Jika sudah ada (collision), ulangi loop untuk menghasilkan kandidat lain.
        }
    }

    //
    public function index()
    {
        // Get featured courses to display on homepage
        $featuredCourses = $this->courseService->getFeaturedCourses(6);
        $totalStudents = \App\Models\User::role('student')->count();
        $totalCourses = \App\Models\Course::count();
        
        return view('front.index', compact('featuredCourses', 'totalStudents', 'totalCourses'));
    }

    public function courses()
    {
        // Get featured courses to display
        $featuredCourses = $this->courseService->getFeaturedCourses(12);
        $allCourses = $this->courseService->getCoursesForPurchase();
        $totalStudents = \App\Models\User::role('student')->count();
        $totalCourses = \App\Models\Course::count();
        
        return view('front.course-catalog', compact('featuredCourses', 'allCourses', 'totalStudents', 'totalCourses'));
    }

    public function termsOfService()
    {
        return view('front.terms-of-service');
    }

    public function courseDetails(\App\Models\Course $course)
    {
        $user = Auth::user();
        // Gunakan service untuk menyiapkan data agregat dan relasi yang diperlukan
        $viewData = $this->courseService->getCourseDetailsData($course, $user);
        return view('front.course-details', $viewData);
    }

    public function previewContent(
        \App\Models\Course $course, 
        $courseSectionOrSectionContent, 
        \App\Models\SectionContent $sectionContent = null
    ) {
        // Handle both route patterns: preview/{sectionContent} and learning/{courseSection}/{sectionContent}
        if ($sectionContent === null) {
            // Preview route: course/{course}/preview/{sectionContent}
            $sectionContent = $courseSectionOrSectionContent;
        } else {
            // Legacy learning route redirect handled in routes
            $sectionContent = $sectionContent;
        }

        // Check if the content belongs to the course
        if ($sectionContent->courseSection->course_id !== $course->id) {
            abort(404, 'Content not found in this course.');
        }

        // UNIFIED ACCESS CONTROL: Check if user can access premium content
        $user = auth()->user();
        $roleNames = $user?->getRoleNames()?->map(fn($n) => strtolower($n)) ?? collect();
        if ($roleNames->isEmpty() && $user && method_exists($user, 'roles')) {
            $roleNames = $user->roles->pluck('name')->map(fn($n) => strtolower($n));
        }
        $isAdmin = $user && ($roleNames->contains('admin') || $roleNames->contains('super-admin'));
        
        // Ensure $isAdmin is always a boolean
        $isAdmin = (bool) $isAdmin;
        
        // For premium content, check access rights
        if (!$sectionContent->is_free && !$isAdmin) {
            // Check if user is authenticated and has course access
            if (!$user) {
                // Guest user trying to access premium content - show locked view
            } elseif (!$user->canAccessCourse($course->id)) {
                // Authenticated user without course access - redirect to course details for purchase
                return redirect()->route('front.course.details', $course->slug)
                    ->with('error', 'You need to purchase this course to access this content.');
            }
        }

        $course->load(['category', 'courseSections.sectionContents', 'courseStudents', 'benefits']);
        $currentSection = $sectionContent->courseSection;
        
        // Prepare base data
        $viewData = compact('course', 'currentSection', 'sectionContent', 'isAdmin');

        // Selalu tambahkan learning data agar view bebas-query, termasuk untuk guest
        $learningData = $this->courseService->getLearningData(
            $course,
            $currentSection->id,
            $sectionContent->id
        );
        
        // Merge learning data dengan data dasar
        $viewData = array_merge($viewData, $learningData);
        
        return view('front.course-preview', $viewData);
    }

    public function checkout_success()
    {
        // Check if recent course purchase
        $course = $this->transactionService->getRecentCourse();
        
        if ($course) {
            // Bagikan aggregate data ke view agar bebas-query
            try {
                $detailsData = $this->courseService->getCourseDetailsData($course, Auth::user());
                view()->share('totalLessons', $detailsData['totalLessons'] ?? 0);
                view()->share('studentsCount', $detailsData['studentsCount'] ?? 0);
            } catch (\Throwable $e) {
                Log::warning('Failed to compute aggregates for checkout_success', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                view()->share('totalLessons', 0);
                view()->share('studentsCount', 0);
            }
            return view('front.course-checkout-success', compact('course'));
        }
        
        // No recent transaction found
        return redirect()->route('front.index')->with('error', 'No recent transaction found.');
    }
    
    /**
     * Course checkout page
     */
    public function courseCheckout(Course $course, Request $request)
    {
        try {
            Log::info('Checkout accessed', ['course_slug' => $course->slug, 'user_id' => Auth::id()]);
            
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::info('User not authenticated, redirecting to login');
                return redirect()->route('login')
                    ->with('error', 'Please login to purchase this course.');
            }
            
            Log::info('Preparing checkout data for course', ['course_id' => $course->id]);
            $checkoutData = $this->transactionService->prepareCourseCheckout($course);
            Log::info('Checkout data prepared', $checkoutData);

            if ($checkoutData['alreadyPurchased']) {
                Log::info('User already purchased course, redirecting');
                return redirect()->route('front.course.details', $course->slug)
                    ->with('success', 'You already own this course!');
            }

            // Ambil konfigurasi Midtrans terpusat dari service
            $clientKey = $this->midtransService->getClientKey();
            $isProduction = $this->midtransService->isProduction();
            
            $checkoutData['midtrans_client_key'] = $clientKey;
            $checkoutData['midtrans_is_production'] = $isProduction;
            Log::info('Client key added to checkout data', ['client_key_length' => strlen($clientKey ?? '')]);

            // Ambil gateway default via Repository (sesuai arsitektur)
            try {
                $defaultGateway = $this->websiteSettingRepository->getDefaultPaymentGateway();
                $checkoutData['default_payment_gateway'] = $defaultGateway;
                Log::info('Default payment gateway resolved', ['default_payment_gateway' => $defaultGateway]);
            } catch (\Throwable $e) {
                $checkoutData['default_payment_gateway'] = 'midtrans';
                Log::warning('Failed to resolve default payment gateway, fallback to midtrans', ['error' => $e->getMessage()]);
            }

            // Tambahkan totalLessons agar Blade tidak melakukan query manual
            try {
                $detailsData = $this->courseService->getCourseDetailsData($course, Auth::user());
                $checkoutData['totalLessons'] = $detailsData['totalLessons'] ?? 0;
                $checkoutData['studentsCount'] = $detailsData['studentsCount'] ?? 0;
            } catch (\Throwable $e) {
                Log::warning('Failed to compute aggregate data for checkout', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                $checkoutData['totalLessons'] = 0;
                $checkoutData['studentsCount'] = 0;
            }

            Log::info('Rendering checkout view with data', ['view' => 'front.course-checkout']);
            return view('front.course-checkout', $checkoutData);
            
        } catch (Exception $e) {
            Log::error('Checkout error', [
                'course_slug' => $course->slug ?? 'unknown',
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a user-friendly error page or redirect
            return response()->view('errors.custom', [
                'message' => 'There was an error processing your checkout. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate discount code for course
     */
    public function validateDiscount(Course $course, Request $request)
    {
        try {
            $request->validate([
                'discount_code' => 'required|string|max:50'
            ]);
            
            $discountCode = strtoupper(trim($request->discount_code));
            
            // Use injected DiscountService to validate discount
            $validation = $this->discountService->validateDiscountForCourse($discountCode, $course);
            
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message']
                ], 422);
            }
            
            // Apply discount to session using TransactionService
            $this->transactionService->applyDiscount($validation['discount']);
            
            // Calculate new totals using TransactionService
            $pricing = $this->transactionService->calculatePricingWithDiscount($course, $validation['discount']);
            
            return response()->json([
                'success' => true,
                'message' => $validation['message'],
                'discount' => [
                    'id' => $validation['discount']->id,
                    'name' => $validation['discount']->name,
                    'code' => $validation['discount']->code,
                    'type' => $validation['discount']->type,
                    'value' => $validation['discount']->value,
                    // Tambahan detail untuk transparansi perhitungan
                    'minimum_amount' => $validation['discount']->minimum_amount,
                    'maximum_discount' => $validation['discount']->maximum_discount
                ],
                'pricing' => $pricing,
                'formatted' => [
                    'subtotal' => 'Rp ' . number_format($pricing['subtotal'], 0, ',', '.'),
                    'discount_amount' => 'Rp ' . number_format($pricing['discount_amount'], 0, ',', '.'),
                    'admin_fee' => 'Rp ' . number_format($pricing['admin_fee'], 0, ',', '.'),
                    'grand_total' => 'Rp ' . number_format($pricing['grand_total'], 0, ',', '.'),
                    'savings' => 'Rp ' . number_format($pricing['savings'], 0, ',', '.')
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            Log::warning('Discount validation failed', [
                'course_id' => $course->id,
                'discount_code' => $request->discount_code ?? 'N/A',
                'validation_errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak boleh kosong atau tidak valid.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Discount validation error', [
                'course_id' => $course->id,
                'discount_code' => $request->discount_code ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clear any partial discount session data on error
            session()->forget(['applied_discount', 'discount_amount']);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan refresh halaman dan coba lagi.'
            ], 500);
        }
    }
    
    /**
     * Remove discount code from session
     */
    public function removeDiscount(Course $course, Request $request)
    {
        try {
            // Remove discount from session using TransactionService
            $this->transactionService->removeDiscount();
            
            // Recalculate pricing without discount
            $pricing = $this->transactionService->calculatePricingWithDiscount($course, null);
            
            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil dihapus.',
                'pricing' => $pricing,
                'formatted' => [
                    'subtotal' => 'Rp ' . number_format($pricing['subtotal'], 0, ',', '.'),
                    'discount_amount' => 'Rp ' . number_format($pricing['discount_amount'], 0, ',', '.'),
                    'admin_fee' => 'Rp ' . number_format($pricing['admin_fee'], 0, ',', '.'),
                    'grand_total' => 'Rp ' . number_format($pricing['grand_total'], 0, ',', '.'),
                    'savings' => 'Rp ' . number_format($pricing['savings'], 0, ',', '.')
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Remove discount error', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus diskon. Silakan coba lagi.'
            ], 500);
        }
    }
    
    /**
     * Handle course payment processing
     */
    public function paymentStoreCoursesMidtrans(Request $request)
    {
        try {
            // ENHANCED LOGGING: Log semua data request dan session
            Log::info('=== PAYMENT REQUEST RECEIVED ===', [
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'request_applied_discount' => $request->input('applied_discount'),
                'request_has_applied_discount' => $request->has('applied_discount'),
                'session_course_id' => session()->get('course_id'),
                'session_applied_discount' => session()->get('applied_discount'),
                'all_session_data' => session()->all(),
                'request_method' => $request->method(),
                'request_content_type' => $request->header('Content-Type'),
                'request_raw_body' => $request->getContent()
            ]);
            
            // Retrieve the course ID from request first, then fallback to session
            $courseId = $request->input('course_id') ?? session()->get('course_id');

            if (!$courseId) {
                Log::error('No course ID in request or session', [
                    'session_id' => session()->getId(),
                    'user_id' => Auth::id(),
                    'request_course_id' => $request->input('course_id'),
                    'session_course_id' => session()->get('course_id')
                ]);
                return response()->json(['error' => 'No course data found in the request or session.'], 400);
            }
            
            Log::info('Course ID resolved', [
                'course_id' => $courseId,
                'source' => $request->input('course_id') ? 'request' : 'session'
            ]);

            // Handle applied discount from frontend request
            $appliedDiscount = $request->input('applied_discount');
            Log::info('Processing discount from frontend', [
                'applied_discount_from_request' => $appliedDiscount,
                'session_discount_before' => session()->get('applied_discount')
            ]);
            
            if ($appliedDiscount) {
                // Validate and apply discount to session using injected services
                $course = $this->courseService->findCourseByIdOrFail((int) $courseId);
                
                $validation = $this->discountService->validateDiscountForCourse(
                    $appliedDiscount['code'], 
                    $course
                );
                
                if ($validation['valid']) {
                    $this->transactionService->applyDiscount($validation['discount']);
                    Log::info('Discount applied successfully', [
                        'discount_code' => $appliedDiscount['code'],
                        'session_discount_after' => session()->get('applied_discount')
                    ]);
                } else {
                    Log::warning('Invalid discount applied during payment', [
                        'discount_code' => $appliedDiscount['code'],
                        'course_id' => $courseId,
                        'validation_message' => $validation['message']
                    ]);
                }
            } else {
                Log::info('No discount in frontend request, checking session', [
                    'session_discount' => session()->get('applied_discount')
                ]);
            }

            // Log final session state before calling PaymentService
            Log::info('Final session state before PaymentService', [
                'course_id' => session()->get('course_id'),
                'applied_discount' => session()->get('applied_discount'),
                'session_id' => session()->getId()
            ]);

            // Call the PaymentService to generate the Snap token for course
            $snapToken = $this->paymentService->createCoursePayment($courseId);

            if (!$snapToken) {
                Log::error('Failed to create Midtrans transaction', [
                    'course_id' => $courseId,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Failed to create Midtrans transaction.'], 500);
            }

            Log::info('Payment token created successfully', [
                'course_id' => $courseId,
                'user_id' => Auth::id(),
                'snap_token_length' => strlen($snapToken)
            ]);

            // Return the Snap token to the frontend
            return response()->json(['snap_token' => $snapToken], 200);
        } catch (Exception $e) {
            Log::error('Payment failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'course_id' => $courseId ?? null,
                'user_id' => Auth::id()
            ]);
            // Handle any exceptions that occur during transaction creation
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Midtrans payment notification webhook
     */
    public function paymentMidtransNotification()
    {
        try {
            Log::info('Received Midtrans webhook notification');
            
            // Handle the payment notification
            $transactionStatus = $this->paymentService->handlePaymentNotification();
            
            Log::info('Payment notification processed', [
                'status' => $transactionStatus
            ]);
            
            return SuccessResponse::json(
                'Notification processed successfully',
                ['status' => $transactionStatus]
            );
            
        } catch (Exception $e) {
            // Log error, tetapi selalu kembalikan 200 agar Midtrans berhenti retry
            Log::error('Failed to process payment notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return SuccessResponse::json(
                'Notification received (processing error logged)',
                ['status' => 'error', 'message' => $e->getMessage()]
            );
        }
    }

    /**
     * Store transaksi Tripay untuk pembelian course.
     * Flow berdiri sendiri, tidak mengubah implementasi Midtrans.
     */
    public function paymentStoreCoursesTripay(\Illuminate\Http\Request $request)
    {
        \Log::info('[Tripay] paymentStoreCoursesTripay start', $request->all());

        $validated = $request->validate([
            'method' => 'required|string',
            'course_id' => 'required',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string',
            'order_items' => 'nullable|array',
        ]);

        // Gunakan kode invoice pendek: UP-XXXX (unik)
        $merchantRef = $this->generateShortMerchantRef();

        // Ambil judul course dari service/repository (WAJIB sesuai arsitektur repository)
        $course = $this->courseService->findCourseByIdOrFail((int) $validated['course_id']);

        // REKALKULASI AMOUNT DI SERVER BERDASARKAN DISKON DI SESSION (repository pattern)
        // Hindari ketergantungan pada nilai amount dari frontend.
        $computedAmount = null;
        try {
            /** @var \App\Repositories\DiscountRepositoryInterface $discountRepo */
            $discountRepo = app(\App\Repositories\DiscountRepositoryInterface::class);
            $applied = session()->get('applied_discount');
            $discountModel = null;
            if (is_array($applied) && isset($applied['id'])) {
                $discountModel = $discountRepo->findById((int) $applied['id']);
            }
            $pricing = $this->transactionService->calculatePricingWithDiscount($course, $discountModel);
            $computedAmount = (int) ($pricing['grand_total'] ?? 0);
            \Log::info('[Tripay] Amount reconciliation', [
                'frontend_amount' => is_numeric($request->input('amount')) ? (int) $request->input('amount') : null,
                'server_grand_total' => $computedAmount,
                'discount_id' => $discountModel?->id,
                'discount_amount' => $pricing['discount_amount'] ?? null,
                'admin_fee' => $pricing['admin_fee'] ?? null,
                'subtotal' => $pricing['subtotal'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Jika gagal menghitung, tetap tangani aman: gunakan harga course tanpa diskon
            \Log::warning('[Tripay] Failed to compute server-side amount, fallback to course price', [
                'error' => $e->getMessage(),
            ]);
            $computedAmount = (int) ($course->price ?? 0);
        }

        // Sinkronkan order_items agar konsisten dengan grand total yang dibayar
        $orderItems = [[
            'name' => $course->name,
            'price' => $computedAmount,
            'quantity' => 1,
        ]];

        $params = [
            'method' => $validated['method'],
            'merchant_ref' => $merchantRef,
            // Gunakan amount hasil kalkulasi server-side
            'amount' => $computedAmount,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? '',
            'order_items' => $orderItems,
            // Perbaikan: gunakan nama route yang benar untuk halaman sukses checkout
            'return_url' => route('front.checkout.success'),
        ];

        try {
            // Pelacakan lintas sistem: persist merchant_ref secara permanen
            /** @var \App\Repositories\PaymentReferenceRepositoryInterface $paymentRefRepo */
            $paymentRefRepo = app(\App\Repositories\PaymentReferenceRepositoryInterface::class);
            $paymentRefRepo->createPending([
                'merchant_ref' => $merchantRef,
                'channel' => 'tripay',
                'user_id' => auth()->id(),
                'course_id' => $course->id,
                // Persist informasi diskon agar dapat dipropagasi ke transaksi
                'discount_id' => $discountModel?->id,
                'discount_amount' => (int) ($pricing['discount_amount'] ?? 0),
                // Persist amount yang akan ditagihkan di Tripay (setelah diskon)
                'amount' => $computedAmount,
                'status' => 'UNPAID',
            ]);

            $paymentService = app(\App\Services\PaymentService::class);
            $result = $paymentService->createTripayTransaction($params);

            // Simpan reference dari gateway dan sinkronkan status
            if (!empty($result['merchant_ref'])) {
                $paymentRefRepo->attachGatewayReference($result['merchant_ref'], (string) ($result['reference'] ?? ''));
                if (!empty($result['status'])) {
                    $paymentRefRepo->updateStatus($result['merchant_ref'], (string) $result['status']);
                }
            }

            return response()->json([
                'success' => $result['success'],
                'pay_url' => $result['pay_url'],
                'reference' => $result['reference'],
                'merchant_ref' => $result['merchant_ref'],
                'status' => $result['status'],
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Tripay] Gagal membuat transaksi', [
                'error' => $e->getMessage(),
            ]);
            // Tandai PaymentReference sebagai FAILED (kegagalan teknis) agar audit jelas
            try {
                /** @var \App\Repositories\PaymentReferenceRepositoryInterface $paymentRefRepo */
                $paymentRefRepo = app(\App\Repositories\PaymentReferenceRepositoryInterface::class);
                $paymentRefRepo->updateStatus($merchantRef, 'FAILED');
            } catch (\Throwable $t) {
                // swallow to avoid masking original error
            }
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Callback/notification dari Tripay.
     */
    public function paymentTripayNotification(\Illuminate\Http\Request $request)
    {
        $signature = $request->header('X-Callback-Signature');
        $event = $request->header('X-Callback-Event');
        $raw = $request->getContent();

        $paymentService = app(\App\Services\PaymentService::class);
        $verified = $paymentService->verifyTripayCallback($raw, $signature);

        if (!$verified) {
            \Log::warning('[Tripay] Callback signature tidak valid', [
                'signature' => $signature,
                'event' => $event,
            ]);
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $data = json_decode($raw, true);
        \Log::info('[Tripay] Callback diterima', [
            'event' => $event,
            'data' => $data,
        ]);

        // Pelacakan lintas sistem: update PaymentReference berdasarkan merchant_ref
        $payload = is_array($data) ? ($data['data'] ?? $data) : [];
        $merchantRef = $payload['merchant_ref'] ?? null;
        if ($merchantRef) {
            /** @var \App\Repositories\PaymentReferenceRepositoryInterface $paymentRefRepo */
            $paymentRefRepo = app(\App\Repositories\PaymentReferenceRepositoryInterface::class);
            $updated = $paymentRefRepo->updateFromTripayCallback($merchantRef, $payload);
            \Log::info('[PaymentReference] Callback processed', [
                'merchant_ref' => $merchantRef,
                'updated' => $updated,
            ]);

            // Jika status sukses/paid, buat transaksi LMS agar akses kursus aktif
            try {
                $paymentRef = $paymentRefRepo->findByMerchantRef($merchantRef);
                if ($paymentRef && in_array(strtoupper((string) $paymentRef->status), ['PAID', 'SUCCESS'], true)) {
                    $created = $this->paymentService->createCourseTransactionFromTripay($paymentRef, $payload);
                    \Log::info('[Tripay] LMS transaction ensured after PAID/SUCCESS', [
                        'merchant_ref' => $merchantRef,
                        'transaction_id' => $created?->id,
                        'booking_trx_id' => $created?->booking_trx_id,
                        'is_paid' => $created?->is_paid,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('[Tripay] Failed to create LMS transaction from callback', [
                    'merchant_ref' => $merchantRef,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::warning('[PaymentReference] merchant_ref tidak ditemukan pada payload Tripay', [
                'payload' => $payload,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}
