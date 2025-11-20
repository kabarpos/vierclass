@extends('front.layouts.app')
@section('title', 'Course Checkout - ' . $course->name)

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Enhanced discount system animations and transitions */
    .discount-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .discount-loading {
        position: relative;
        overflow: hidden;
    }
    
    .discount-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .discount-input-focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        border-color: #3b82f6;
    }
    
    .discount-success-highlight {
        background-color: #f0fdf4;
        border-color: #22c55e;
        animation: successPulse 0.6s ease-out;
    }
    
    @keyframes successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .discount-error-shake {
        animation: errorShake 0.5s ease-in-out;
    }
    
    @keyframes errorShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    /* Smooth price update animation */
    .price-update {
        animation: priceUpdate 0.4s ease-in-out;
    }
    
    @keyframes priceUpdate {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.98); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
    <x-navigation-auth />
    
    <!-- Breadcrumb -->
    <div id="path" class="flex w-full bg-charcoal-900 border-b border-charcoal-800 py-4 hidden lg:block">
        <div class="flex items-center w-full max-w-7xl px-4 sm:px-6 lg:px-8 mx-auto gap-3">
            <a href="{{ route('front.index') }}" class="text-beige-300 hover:text-gold-400 cursor-pointer">Home</a>
            <div class="h-4 w-px bg-charcoal-700"></div>
            <a href="{{ route('front.course.details', $course->slug) }}" class="text-beige-300 hover:text-gold-400 cursor-pointer">{{ $course->name }}</a>
            <span class="text-beige-400">/</span>
            <span class="font-semibold text-beige-50">Checkout</span>
        </div>
    </div>

    <main class="bg-gradient-to-b from-charcoal-900 to-charcoal-800 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Course Preview -->
                <div class="space-y-8 order-2 lg:order-1">
                    <div class="bg-charcoal-800/80 backdrop-blur-sm rounded-xl shadow-lg border border-charcoal-700 overflow-hidden">
                        <!-- Course Image -->
                        <div class="aspect-video bg-charcoal-900">
                            @if($course->thumbnail)
                                @if(str_starts_with($course->thumbnail, 'http'))
                                    <x-lazy-image 
                                        src="{{ $course->thumbnail }}" 
                                        alt="{{ $course->name }}" 
                                        class="w-full h-full object-cover"
                                        loading="lazy" />
                                @else
                                    <x-lazy-image 
                                        src="{{ Storage::disk('public')->url($course->thumbnail) }}" 
                                        alt="{{ $course->name }}" 
                                        class="w-full h-full object-cover"
                                        loading="lazy" />
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-charcoal-900">
                                    <div class="text-center">
                                        <div class="text-gold-400 font-bold text-3xl mb-2">{{ substr($course->name, 0, 2) }}</div>
                                        <div class="text-gold-500 text-sm">Course Preview</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Course Info -->
                        <div class="p-6 space-y-6">
                            <!-- Course Title and Category -->
                            <div class="space-y-3">
                                @if($course->category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gold-600/20 text-gold-300">
                                        {{ $course->category->name }}
                                    </span>
                                @endif
                                <h2 class="text-xl font-bold text-beige-50">{{ $course->name }}</h2>
                                <p class="text-beige-300 leading-relaxed">{{ $course->about }}</p>
                            </div>
                            
                            <!-- Course Stats -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-charcoal-900/50 rounded-lg">
                                    <div class="text-2xl font-bold text-gold-400">{{ $totalLessons }}</div>
                                    <div class="text-sm text-beige-300">Total Lessons</div>
                                </div>
                                <div class="text-center p-4 bg-charcoal-900/50 rounded-lg">
                                    <div class="text-2xl font-bold text-gold-400">{{ $studentsCount }}</div>
                                    <div class="text-sm text-beige-300">Students Enrolled</div>
                                </div>
                            </div>
                            
                            <!-- What You Get -->
                            <div class="space-y-4">
                                <h3 class="font-semibold text-beige-50">What You'll Get:</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-beige-300">Lifetime access to all course content</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-beige-300">Certificate of completion</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-beige-300">Access from any device</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-beige-300">Learn at your own pace</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="space-y-8 order-1 lg:order-2">
                    <div class="bg-charcoal-800/80 backdrop-blur-sm rounded-xl shadow-lg border border-charcoal-700 p-8">
                        <form id="checkout-details" class="space-y-6">
                            @csrf
                            <input type="hidden" name="payment_method" value="{{ (isset($default_payment_gateway) && $default_payment_gateway === 'tripay') ? 'Tripay' : 'Midtrans' }}">
                            
                            <div class="border-b border-charcoal-700 pb-6">
                                <h1 class="text-2xl font-bold text-beige-50">Course Purchase</h1>
                                <p class="text-beige-300 mt-2">Complete your purchase to get lifetime access to this course</p>
                            </div>
                            

                            
                            <!-- Order Summary -->
                            <section class="space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-4 bg-charcoal-900/50 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-medium">Course Purchase</span>
                                        </div>
                                        <div class="text-right">
                                            @if($course->original_price && $course->original_price > $course->price)
                                                <!-- Original Price (Strikethrough) -->
                                                <div class="text-sm text-beige-400 line-through">
                                                    Rp {{ number_format($course->original_price, 0, '', '.') }}
                                                </div>
                                                <!-- Current Price with Discount Badge -->
                                                <div class="flex items-center justify-end space-x-2">
                                                    <span class="font-bold text-lg">Rp {{ number_format($course->price, 0, '', '.') }}</span>
                                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">
                                                        {{ round((($course->original_price - $course->price) / $course->original_price) * 100) }}% OFF
                                                    </span>
                                                </div>
                                            @else
                                                <!-- Regular Price -->
                                                <span class="font-bold text-lg">Rp {{ number_format($course->price, 0, '', '.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(isset($admin_fee_amount) && $admin_fee_amount > 0)
                                    <div class="flex items-center justify-between p-4 bg-charcoal-900/50 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Biaya Admin</span>
                                        </div>
                                        <span class="font-semibold text-beige-200">Rp {{ number_format($admin_fee_amount, 0, '', '.') }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="flex items-center justify-between p-4 bg-charcoal-900/50 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 2L3 7v11c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7l-7-5zM8 18v-6h4v6H8z"/>
                                            </svg>
                                            <span>Access Duration</span>
                                        </div>
                                        <span class="font-semibold text-green-600">Lifetime</span>
                                    </div>
                                </div>
                                
                                <!-- Discount Code Section -->
                                <div class="space-y-4">
                                    <div class="border-t border-charcoal-700 pt-4">
                                        <h3 class="text-sm font-medium text-beige-50 mb-3">Kode Diskon</h3>
                                        <div class="flex space-x-2 {{ isset($appliedDiscount) ? 'hidden' : '' }}" id="discount-input-section">
                                            <div class="flex-1">
                                                <input type="text" 
                                                       id="discount-code" 
                                                       name="discount_code"
                                                       placeholder="Masukkan kode diskon" 
                                                       class="w-full px-3 py-2 bg-charcoal-900 border border-charcoal-700 text-beige-50 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-gold-500 text-sm cursor-pointer placeholder:text-beige-400"
                                                       autocomplete="off">
                                            </div>
                                            <button type="button" 
                                                    id="apply-discount" 
                                                    class="px-4 py-2 bg-gold-600 text-charcoal-900 text-sm font-bold rounded-lg hover:bg-gold-500 transition-colors duration-200 cursor-pointer">
                                                Terapkan
                                            </button>
                                        </div>
                                        
                                        <!-- Discount Message -->
                                        <div id="discount-message" class="mt-2 text-sm hidden"></div>
                                        
                                        <!-- Applied Discount Display -->
                                        <div id="applied-discount" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg {{ isset($appliedDiscount) ? '' : 'hidden' }}">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-green-700 font-medium" id="discount-name">
                                                        @if(isset($appliedDiscount))
                                                            {{ $appliedDiscount['name'] }} ({{ $appliedDiscount['code'] }})
                                                        @endif
                                                    </span>
                                                </div>
                                                <button type="button" 
                                                        id="remove-discount" 
                                                        class="text-green-600 hover:text-green-800 cursor-pointer">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-1 text-sm text-green-600" id="discount-details">
                                                @if(isset($appliedDiscount) && isset($discount_amount))
                                                    Hemat Rp {{ number_format($discount_amount, 0, ',', '.') }}
                                                    @if($appliedDiscount['type'] === 'percentage')
                                                        ({{ $appliedDiscount['value'] }}% off)
                                                    @else
                                                        (diskon tetap)
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="border-charcoal-700">
                                
                                <!-- Price Breakdown -->
                                <div class="space-y-3">
                                    <!-- Subtotal -->
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-beige-300">Subtotal</span>
                                        <span class="text-beige-200" id="subtotal-amount">Rp {{ number_format($sub_total_amount, 0, '', '.') }}</span>
                                    </div>
                                    
                                    <!-- Discount Amount (show if discount exists) -->
                                    <div id="discount-amount-row" class="flex items-center justify-between text-sm text-green-600 {{ isset($discount_amount) && $discount_amount > 0 ? '' : 'hidden' }}">
                                        <span>Diskon</span>
                                        <span id="discount-amount">
                                            @if(isset($discount_amount) && $discount_amount > 0)
                                                -Rp {{ number_format($discount_amount, 0, ',', '.') }}
                                            @else
                                                -Rp 0
                                            @endif
                                        </span>
                                    </div>
                                    
                                    @if(isset($admin_fee_amount) && $admin_fee_amount > 0)
                                    <!-- Admin Fee -->
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-beige-300">Biaya Admin</span>
                                        <span class="text-beige-200">Rp {{ number_format($admin_fee_amount, 0, '', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <hr class="border-charcoal-700">
                                
                                <div class="flex items-center justify-between p-4 bg-gold-600/20 rounded-lg border border-gold-400">
                                    <span class="text-lg font-bold text-gold-300">Total Payment</span>
                                    <span class="text-2xl font-bold text-gold-300" id="total-payment">Rp {{ number_format($grand_total_amount, 0, '', '.') }}</span>
                                </div>
                                <!-- Rincian Perhitungan (Toggle) -->
                                <div class="mt-3">
                                    <div id="toggle-breakdown" class="text-sm text-gold-400 hover:text-gold-300 cursor-pointer select-none">
                                        Lihat rincian perhitungan
                                    </div>
                                    <div id="calculation-breakdown" class="mt-2 hidden rounded-lg border border-gold-400 bg-gold-600/10 p-3">
                                        <!-- Diisi oleh JavaScript: updateCalculationBreakdown -->
                                        <div class="text-sm text-beige-300">Memuat rincian…</div>
                                    </div>
                                </div>
                            </section>
                            
                            <!-- Payment Button -->
                            <button type="button" id="pay-button" 
                                    class="w-full py-4 bg-gold-600 text-charcoal-900 font-bold rounded-lg hover:bg-gold-500 transition-colors duration-200 shadow-lg hover:shadow-xl cursor-pointer">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span>
                                        {{ (isset($default_payment_gateway) && $default_payment_gateway === 'tripay') ? 'Bayar via Tripay' : 'Pay Now' }}
                                    </span>
                                </div>
                            </button>
                            
                            <hr class="border-gray-200">
                            
                            <p class="text-sm text-beige-400 text-center">
                                By purchasing this course, you agree to our 
                                <a href="{{ route('front.terms-of-service') }}" class="text-gold-400 hover:underline cursor-pointer">Terms & Conditions</a>
                            </p>
                        </form>
                    </div>
                </div>
                

            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <!-- Midtrans Snap JS -->
    @php
        $isProduction = ($midtrans_is_production ?? false) || (config('midtrans.environment') === 'production');
        $snapUrl = $isProduction ? config('midtrans.production.snapUrl') : config('midtrans.sandbox.snapUrl');
        $clientKey = $midtrans_client_key ?? ($isProduction ? config('midtrans.production.clientKey') : config('midtrans.sandbox.clientKey'));
    @endphp
    @if(($default_payment_gateway ?? 'midtrans') === 'midtrans')
        <script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
    @endif

    <script type="text/javascript" nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Default payment gateway from backend
        const DEFAULT_GATEWAY = "{{ $default_payment_gateway ?? 'midtrans' }}";

        // Global variables for discount management
        let appliedDiscount = @if(isset($appliedDiscount)) @json($appliedDiscount) @else null @endif;
        let originalPricing = {
            subtotal: {{ $sub_total_amount }},
            adminFee: {{ $admin_fee_amount ?? 0 }},
            grandTotal: {{ $grand_total_amount }}
        }
        
        // Log applied discount for debugging
        console.log('Applied discount from server:', appliedDiscount);
        
        // Progressive Enhancement: Keyboard shortcuts and accessibility
        document.addEventListener('keydown', function(e) {
            // Escape key to clear discount input or hide messages
            if (e.key === 'Escape') {
                const discountInput = document.getElementById('discount-code');
                const messageElement = document.getElementById('discount-message');
                
                if (messageElement && !messageElement.classList.contains('hidden')) {
                    hideDiscountMessage();
                } else if (discountInput && discountInput.value) {
                    discountInput.value = '';
                    discountInput.focus();
                    hideDiscountMessage();
                }
            }
            
            // Ctrl/Cmd + D to focus discount input (if visible)
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                const discountInput = document.getElementById('discount-code');
                const discountSection = document.getElementById('discount-input-section');
                
                if (discountInput && discountSection && !discountSection.classList.contains('hidden')) {
                    discountInput.focus();
                    discountInput.select();
                }
            }
        });
        
        // Progressive Enhancement: Auto-save form data to prevent loss
        const formElements = document.querySelectorAll('#checkout-details input, #checkout-details select');
        formElements.forEach(element => {
            element.addEventListener('input', function() {
                const formData = new FormData(document.getElementById('checkout-details'));
                const data = Object.fromEntries(formData.entries());
                sessionStorage.setItem('checkout_form_data', JSON.stringify(data));
            });
        });
        
        // Restore form data on page load
        const savedFormData = sessionStorage.getItem('checkout_form_data');
        if (savedFormData) {
            try {
                const data = JSON.parse(savedFormData);
                Object.keys(data).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element && element.type !== 'hidden') {
                        element.value = data[key];
                    }
                });
            } catch (e) {
                console.warn('Could not restore form data:', e);
            }
        }
        
        // Progressive Enhancement: Network status monitoring
        let isOnline = navigator.onLine;
        
        window.addEventListener('online', function() {
            isOnline = true;
            const offlineMessage = document.getElementById('offline-message');
            if (offlineMessage) {
                offlineMessage.remove();
            }
        });
        
        window.addEventListener('offline', function() {
            isOnline = false;
            showNetworkMessage('Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi.', 'warning');
        });
        
        function showNetworkMessage(message, type) {
            // Remove existing network message
            const existingMessage = document.getElementById('offline-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            // Create new network message
            const messageDiv = document.createElement('div');
            messageDiv.id = 'offline-message';
            messageDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
                type === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-800' : 'bg-red-100 border border-red-400 text-red-800'
            }`;
            messageDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(messageDiv);
            
            // Auto-remove after 5 seconds for warning messages
            if (type === 'warning') {
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.style.opacity = '0';
                        messageDiv.style.transform = 'translateX(100%)';
                        setTimeout(() => messageDiv.remove(), 300);
                    }
                }, 5000);
            }
        }
        
        // Modern fetch wrapper with proper error handling
        async function makeRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    }
                });
                
                // Always try to parse JSON response first
                const data = await response.json().catch(() => ({}));
                
                // For validation errors (422) and other client errors, return the data
                // The calling function will check data.success to determine if it's an error
                if (response.status === 422 || (response.status >= 400 && response.status < 500)) {
                    return data;
                }
                
                // For server errors (500+), throw an error
                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
                }
                
                return data;
            } catch (error) {
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    throw new Error('Koneksi terputus. Silakan periksa internet Anda.');
                }
                throw error;
            }
        }
        
        // Global variables for discount validation
        let discountValidationTimeout;
        let isValidating = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const applyDiscountBtn = document.getElementById('apply-discount');
            const discountCodeInput = document.getElementById('discount-code');
            const removeDiscountBtn = document.getElementById('remove-discount');
            
            if (payButton) {
                payButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    handlePayment();
                });
            }
            
            // Discount code validation with debouncing
        
        // Prevent accidental form submission that corrupts URL with GET params
        const checkoutForm = document.getElementById('checkout-details');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
            });
        }

        if (applyDiscountBtn && discountCodeInput) {
            applyDiscountBtn.addEventListener('click', function(e) {
                if (!isValidating) {
                    validateDiscountCode();
                }
            });
            
            discountCodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!isValidating) {
                        validateDiscountCode();
                    }
                }
            });
            // Extra guard: also prevent Enter on keydown to avoid form submission in some browsers
            discountCodeInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!isValidating) {
                        validateDiscountCode();
                    }
                }
            });
            
            // Enhanced focus and blur events for better UX
            discountCodeInput.addEventListener('focus', function(e) {
                e.target.classList.add('discount-input-focus');
                hideDiscountMessage(); // Clear any existing messages on focus
            });
            
            discountCodeInput.addEventListener('blur', function(e) {
                e.target.classList.remove('discount-input-focus');
            });
            
            // Progressive Enhancement: Auto-validate on input with debouncing and auto-save
            discountCodeInput.addEventListener('input', function(e) {
                const value = e.target.value.trim();
                
                // Progressive Enhancement: Auto-save discount code input
                sessionStorage.setItem('discount_code_input', e.target.value);
                
                // Clear previous timeout
                if (discountValidationTimeout) {
                    clearTimeout(discountValidationTimeout);
                }
                
                // Clear any existing messages if input is empty
                if (!value) {
                    hideDiscountMessage();
                    sessionStorage.removeItem('discount_code_input');
                    return;
                }
                
                // Visual feedback for typing
                e.target.classList.add('discount-loading');
                
                // Set new timeout for auto-validation (2 seconds after user stops typing)
                discountValidationTimeout = setTimeout(() => {
                    e.target.classList.remove('discount-loading');
                    if (value.length >= 3 && !isValidating) {
                        validateDiscountCode(true); // true = silent validation
                    }
                }, 2000);
            });
            
            // Progressive Enhancement: Restore discount code input on page load (without auto-validation)
            const savedDiscountCode = sessionStorage.getItem('discount_code_input');
            if (savedDiscountCode && !appliedDiscount) {
                discountCodeInput.value = savedDiscountCode;
                // Only restore input value, do not auto-focus or auto-validate
                // User must manually interact to validate
            }
        } else {
            console.error('Apply button or discount input not found!');
        }
            
            // Remove discount
            if (removeDiscountBtn) {
                removeDiscountBtn.addEventListener('click', function() {
                    removeDiscount();
                });
            }
        });
        
        async function validateDiscountCode(silent = false) {
            const discountCodeInput = document.getElementById('discount-code');
            const applyBtn = document.getElementById('apply-discount');
            const messageDiv = document.getElementById('discount-message');
            
            const discountCode = discountCodeInput.value.trim();
            
            if (!discountCode) {
                if (!silent) {
                    showDiscountMessage('Silakan masukkan kode diskon.', 'error');
                }
                return;
            }
            
            // Prevent multiple simultaneous validations
            if (isValidating) {
                return;
            }
            
            isValidating = true;
            
            // Show enhanced loading state
            if (!silent) {
                applyBtn.disabled = true;
                applyBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>Memvalidasi...';
                hideDiscountMessage();
            }
            
            // Add loading class to input for visual feedback
            discountCodeInput.classList.add('border-blue-400', 'bg-blue-50');
            
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                if (!silent) {
                    showDiscountMessage('Session expired. Please refresh the page.', 'error');
                    resetApplyButton();
                }
                isValidating = false;
                discountCodeInput.classList.remove('border-blue-400', 'bg-blue-50');
                return;
            }
            
            // Modern discount validation with clean error handling
            try {
                const data = await makeRequest("{{ route('front.course.validate-discount', $course->slug) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        discount_code: discountCode
                    })
                });
                
                if (data.success) {
                    // CRITICAL FIX: Set appliedDiscount variable untuk payment request
                    appliedDiscount = data.discount;
                    
                    // Store applied discount for session persistence
                    sessionStorage.setItem('last_valid_discount', JSON.stringify({
                        code: discountCode,
                        discount: data.discount,
                        applied_at: new Date().toISOString()
                    }));
                    
                    if (!silent) {
                        showDiscountMessage(data.message, 'success');
                    }
                    
                    // Update pricing display in real-time instead of reloading
                    if (data.pricing && data.formatted) {
                        updatePricingDisplay(data.pricing, data.formatted);
                        showAppliedDiscount(data.discount, data.formatted.savings);
                    }
                    
                    console.log('✅ Applied discount set:', appliedDiscount);
                } else {
                    if (!silent) {
                        showDiscountMessage(data.message, 'error');
                        
                        // Add error shake animation to input
                        discountCodeInput.classList.add('discount-error-shake');
                        // Use event listener for animation end instead of setTimeout
                        const handleAnimationEnd = () => {
                            discountCodeInput.classList.remove('discount-error-shake');
                            discountCodeInput.removeEventListener('animationend', handleAnimationEnd);
                        };
                        discountCodeInput.addEventListener('animationend', handleAnimationEnd);
                    }
                }
                
            } catch (error) {
                console.error('Discount validation error:', error);
                
                // Hanya tampilkan pesan error jika benar-benar ada masalah dengan kupon
                if (!silent) {
                    let errorMessage = '';
                    
                    if (!isOnline) {
                        errorMessage = 'Tidak ada koneksi internet. Silakan coba lagi.';
                    } else if (error.message.includes('422')) {
                        errorMessage = 'Kode diskon tidak valid atau sudah kedaluwarsa';
                    } else if (error.message.includes('500')) {
                        errorMessage = 'Server sedang bermasalah. Silakan coba lagi.';
                    } else if (error.message.includes('Koneksi terputus')) {
                        errorMessage = error.message;
                    }
                    
                    // Hanya tampilkan pesan jika ada error message yang spesifik
                    if (errorMessage) {
                        showDiscountMessage(errorMessage, 'error');
                        
                        // Add error shake animation to input
                        discountCodeInput.classList.add('discount-error-shake');
                        const handleAnimationEnd = () => {
                            discountCodeInput.classList.remove('discount-error-shake');
                            discountCodeInput.removeEventListener('animationend', handleAnimationEnd);
                        };
                        discountCodeInput.addEventListener('animationend', handleAnimationEnd);
                    }
                }
                
            } finally {
                isValidating = false;
                
                // Reset button state with smooth transition
                if (!silent) {
                    resetApplyButton();
                }
                
                // Remove loading classes from input
                discountCodeInput.classList.remove('border-blue-400', 'bg-blue-50');
            }
        }
        
        async function removeDiscount() {
            const removeBtn = document.getElementById('remove-discount');
            
            // Prevent multiple simultaneous removals
            if (isValidating) {
                return;
            }
            
            isValidating = true;
            
            if (removeBtn) {
                removeBtn.disabled = true;
                removeBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-red-600 border-t-transparent rounded-full"></span>';
            }
            
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                showDiscountMessage('Session expired. Please refresh the page.', 'error');
                isValidating = false;
                if (removeBtn) {
                    removeBtn.disabled = false;
                    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                }
                return;
            }
            
            // Modern discount removal with clean error handling
            try {
                const data = await makeRequest("{{ route('front.course.remove-discount', $course->slug) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                if (data.success) {
                    // CRITICAL FIX: Reset appliedDiscount variable
                    appliedDiscount = null;
                    
                    // Clear sessionStorage to prevent auto-restore
                    sessionStorage.removeItem('discount_code_input');
                    sessionStorage.removeItem('last_valid_discount');
                    
                    // Update pricing display in real-time instead of reloading
                    if (data.pricing && data.formatted) {
                        updatePricingDisplay(data.pricing, data.formatted);
                        hideAppliedDiscount();
                    }
                    
                    // Clear discount input
                    const discountCodeInput = document.getElementById('discount-code');
                    if (discountCodeInput) {
                        discountCodeInput.value = '';
                    }
                    
                    console.log('✅ Applied discount cleared:', appliedDiscount);
                    showDiscountMessage('Diskon berhasil dihapus', 'success');
                } else {
                    showDiscountMessage(data.message, 'error');
                }
                
            } catch (error) {
                console.error('Remove discount error:', error);
                
                // Hanya tampilkan pesan error jika benar-benar ada masalah
                let errorMessage = '';
                
                if (!isOnline) {
                    errorMessage = 'Tidak ada koneksi internet. Silakan coba lagi.';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Server sedang bermasalah. Silakan coba lagi.';
                } else if (error.message.includes('Koneksi terputus')) {
                    errorMessage = error.message;
                }
                
                // Hanya tampilkan pesan jika ada error message yang spesifik
                if (errorMessage) {
                    showDiscountMessage(errorMessage, 'error');
                }
                
            } finally {
                isValidating = false;
                
                if (removeBtn) {
                    removeBtn.disabled = false;
                    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                }
            }
        }
        
        function updatePricingDisplay(pricing, formatted) {
            // Add price update animation class
            const pricingElements = [
                document.getElementById('subtotal-amount'),
                document.getElementById('discount-amount'),
                document.getElementById('total-payment')
            ].filter(el => el);
            
            // Apply animation to all pricing elements
            pricingElements.forEach(el => {
                el.classList.add('price-update');
            });
            
            // Update subtotal IMMEDIATELY for real-time response
            const subtotalElement = document.getElementById('subtotal-amount');
            if (subtotalElement) {
                subtotalElement.textContent = formatted.subtotal;
            }
            
            // Show/hide discount amount with immediate update
            const discountRow = document.getElementById('discount-amount-row');
            const discountAmount = document.getElementById('discount-amount');
            
            if (pricing.discount_amount > 0) {
                // Update discount amount IMMEDIATELY
                if (discountAmount) {
                    discountAmount.textContent = '-' + formatted.discount_amount;
                }
                
                if (discountRow && discountRow.classList.contains('hidden')) {
                    discountRow.style.opacity = '0';
                    discountRow.style.transform = 'translateY(-10px)';
                    discountRow.style.transition = 'all 0.3s ease-in-out';
                    discountRow.classList.remove('hidden');
                    
                    // Use requestAnimationFrame for smooth animation
                    requestAnimationFrame(() => {
                        discountRow.style.opacity = '1';
                        discountRow.style.transform = 'translateY(0)';
                    });
                }
            } else {
                if (discountRow && !discountRow.classList.contains('hidden')) {
                    discountRow.style.transition = 'all 0.3s ease-in-out';
                    discountRow.style.opacity = '0';
                    discountRow.style.transform = 'translateY(-10px)';
                    
                    // Use event listener for animation end instead of setTimeout
                    const handleTransitionEnd = () => {
                        discountRow.classList.add('hidden');
                        discountRow.style.transform = 'translateY(0)';
                        discountRow.removeEventListener('transitionend', handleTransitionEnd);
                    };
                    discountRow.addEventListener('transitionend', handleTransitionEnd);
                }
            }
            
            // Update total payment IMMEDIATELY with emphasis
            const totalPayment = document.getElementById('total-payment');
            if (totalPayment) {
                totalPayment.textContent = formatted.grand_total;
                
                // Add emphasis animation for total payment
                totalPayment.style.color = '#059669';
                totalPayment.style.fontWeight = 'bold';
                
                // Use requestAnimationFrame for better performance
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        totalPayment.style.color = '';
                        totalPayment.style.fontWeight = '';
                    }, 1000);
                });
            }
            
            // Remove animation classes using requestAnimationFrame
            requestAnimationFrame(() => {
                setTimeout(() => {
                    pricingElements.forEach(el => {
                        el.classList.remove('price-update');
                    });
                }, 400);
            });

            // Sinkronkan state originalPricing agar request pembayaran membaca nilai terbaru
                try {
                    if (typeof originalPricing === 'object') {
                        originalPricing.grandTotal = pricing.grand_total;
                        originalPricing.subtotal = pricing.subtotal;
                        originalPricing.discountAmount = pricing.discount_amount;
                        originalPricing.adminFee = pricing.admin_fee;
                    }
                } catch (e) {
                    console.warn('Gagal menyinkronkan originalPricing state:', e);
                }

                // Perbarui panel rincian perhitungan agar pengguna paham kalkulasi
                try {
                    updateCalculationBreakdown(pricing, appliedDiscount);
                } catch (e) {
                    console.warn('Update calculation breakdown warning:', e);
                }
            }
        
        function showAppliedDiscount(discount, savings) {
            const appliedDiscountDiv = document.getElementById('applied-discount');
            const discountName = document.getElementById('discount-name');
            const discountDetails = document.getElementById('discount-details');
            
            if (appliedDiscountDiv && discountName && discountDetails) {
                // Update content IMMEDIATELY for real-time response
                discountName.textContent = discount.name + ' (' + discount.code + ')';
                
                let detailText = 'Hemat ' + savings;
                if (discount.type === 'percentage') {
                    detailText += ' (' + discount.value + '% off)';
                } else {
                    detailText += ' (diskon tetap)';
                }
                discountDetails.textContent = detailText;
                
                // Show with smooth animation
                appliedDiscountDiv.style.opacity = '0';
                appliedDiscountDiv.style.transform = 'translateY(10px)';
                appliedDiscountDiv.style.transition = 'all 0.3s ease-in-out';
                appliedDiscountDiv.classList.remove('hidden');
                
                // Trigger animation using requestAnimationFrame for better performance
                requestAnimationFrame(() => {
                    appliedDiscountDiv.style.opacity = '1';
                    appliedDiscountDiv.style.transform = 'translateY(0)';
                });
                
                // Add success highlight effect IMMEDIATELY
                appliedDiscountDiv.style.backgroundColor = '#f0fdf4';
                appliedDiscountDiv.style.borderColor = '#22c55e';
                
                // Use requestAnimationFrame for better performance
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        appliedDiscountDiv.style.backgroundColor = '';
                        appliedDiscountDiv.style.borderColor = '';
                    }, 1000);
                });
            }
        }
        
        function hideAppliedDiscount() {
            const appliedDiscountDiv = document.getElementById('applied-discount');
            if (appliedDiscountDiv && !appliedDiscountDiv.classList.contains('hidden')) {
                // Hide with smooth animation
                appliedDiscountDiv.style.transition = 'all 0.3s ease-in-out';
                appliedDiscountDiv.style.opacity = '0';
                appliedDiscountDiv.style.transform = 'translateY(-10px)';
                
                // Use event listener for animation end instead of setTimeout
                const handleTransitionEnd = () => {
                    appliedDiscountDiv.classList.add('hidden');
                    appliedDiscountDiv.style.transform = 'translateY(0)';
                    appliedDiscountDiv.removeEventListener('transitionend', handleTransitionEnd);
                };
                appliedDiscountDiv.addEventListener('transitionend', handleTransitionEnd);
            }
        }

        // Toggle panel rincian perhitungan
        (function initBreakdownToggle(){
            const toggle = document.getElementById('toggle-breakdown');
            const panel = document.getElementById('calculation-breakdown');
            if (toggle && panel) {
                toggle.addEventListener('click', () => {
                    panel.classList.toggle('hidden');
                });
            }
        })();

        // Util: format angka ke Rupiah konsisten
        function formatRupiah(num) {
            try {
                const n = Number(num || 0);
                return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            } catch {
                return 'Rp 0';
            }
        }

        // Render teks rumus untuk penjelasan diskon
        function renderDiscountFormula(subtotal, discount) {
            if (!discount || !discount.type) {
                return 'Tidak ada diskon diterapkan';
            }
            const sub = Number(subtotal || 0);
            const val = Number(discount.value || 0);
            const maxCap = discount.maximum_discount != null ? Number(discount.maximum_discount) : null;
            if (discount.type === 'percentage') {
                const percAmount = Math.floor(sub * val / 100);
                if (maxCap && percAmount > maxCap) {
                    return `min(${formatRupiah(sub)} × ${val}%, ${formatRupiah(maxCap)}) = ${formatRupiah(maxCap)}`;
                }
                return `${formatRupiah(sub)} × ${val}% = ${formatRupiah(percAmount)}`;
            }
            // Fixed nominal
            const fixed = Math.min(val, sub);
            return `min(${formatRupiah(val)}, ${formatRupiah(sub)}) = ${formatRupiah(fixed)}`;
        }

        // Perbarui panel rincian perhitungan
        function updateCalculationBreakdown(pricing, discount) {
            const panel = document.getElementById('calculation-breakdown');
            if (!panel) return;
            const subtotal = Number(pricing?.subtotal || 0);
            const discAmount = Number(pricing?.discount_amount || 0);
            const adminFee = Number(pricing?.admin_fee || 0);
            const grand = Number(pricing?.grand_total || 0);

            const discountLabel = discount && discount.name ? `${discount.name} (${discount.code})` : '—';
            const discountTypeLabel = discount && discount.type === 'percentage' ? `${discount.value}%` : (discount ? formatRupiah(discount.value) : '—');
            const formulaText = renderDiscountFormula(subtotal, discount);

            panel.innerHTML = `
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Harga Kursus</span>
                        <span class="text-gray-900">${formatRupiah(subtotal)}</span>
                    </div>
                    <div class="flex items-start justify-between">
                        <div class="text-gray-600">
                            Diskon <span class="text-mountain-meadow-700">${discountLabel}</span>
                            <span class="text-gray-500">(${discountTypeLabel})</span>
                        </div>
                        <div class="text-green-700 font-medium">- ${formatRupiah(discAmount)}</div>
                    </div>
                    <div class="text-gray-500">Rumus: ${formulaText}</div>
                    ${adminFee > 0 ? `
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Biaya Admin</span>
                            <span class="text-gray-900">${formatRupiah(adminFee)}</span>
                        </div>
                    ` : ''}
                    <hr class="border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-mountain-meadow-800 font-semibold">Total Bayar</span>
                        <span class="text-mountain-meadow-800 font-bold">${formatRupiah(grand)}</span>
                    </div>
                </div>
            `;
        }
        
        function showDiscountInput() {
            const discountInputSection = document.getElementById('discount-input-section');
            if (discountInputSection) {
                // Show with smooth animation
                discountInputSection.style.opacity = '0';
                discountInputSection.style.transform = 'translateY(10px)';
                discountInputSection.style.transition = 'all 0.3s ease-in-out';
                discountInputSection.classList.remove('hidden');
                
                // Trigger animation using requestAnimationFrame
                requestAnimationFrame(() => {
                    discountInputSection.style.opacity = '1';
                    discountInputSection.style.transform = 'translateY(0)';
                });
                
                // Focus on input for better UX using event listener
                const discountInput = document.getElementById('discount-code');
                if (discountInput) {
                    const handleTransitionEnd = () => {
                        discountInput.focus();
                        discountInputSection.removeEventListener('transitionend', handleTransitionEnd);
                    };
                    discountInputSection.addEventListener('transitionend', handleTransitionEnd);
                }
            }
        }
        
        function showDiscountMessage(message, type) {
            const messageElement = document.getElementById('discount-message');
            if (messageElement) {
                // Clear any existing timeout
                if (window.discountMessageTimeout) {
                    clearTimeout(window.discountMessageTimeout);
                }
                
                // Update content IMMEDIATELY for real-time response
                messageElement.textContent = message;
                messageElement.className = `mt-2 text-sm transition-all duration-300 ease-in-out ${
                    type === 'success' ? 'text-green-600 bg-green-50 border border-green-200 px-3 py-2 rounded-md' : 'text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded-md'
                }`;
                
                // Show with animation
                messageElement.style.opacity = '0';
                messageElement.style.transform = 'translateY(-10px)';
                messageElement.classList.remove('hidden');
                
                // Trigger animation using requestAnimationFrame for better performance
                requestAnimationFrame(() => {
                    messageElement.style.opacity = '1';
                    messageElement.style.transform = 'translateY(0)';
                });
                
                // Auto-hide success messages after 5 seconds
                if (type === 'success') {
                    window.discountMessageTimeout = setTimeout(() => {
                        hideDiscountMessage();
                    }, 5000);
                }
            }
        }
        
        function hideDiscountMessage() {
            const messageElement = document.getElementById('discount-message');
            if (messageElement && !messageElement.classList.contains('hidden')) {
                // Clear any existing timeout
                if (window.discountMessageTimeout) {
                    clearTimeout(window.discountMessageTimeout);
                }
                
                // Hide with animation
                messageElement.style.transition = 'all 0.3s ease-in-out';
                messageElement.style.opacity = '0';
                messageElement.style.transform = 'translateY(-10px)';
                
                // Use event listener for animation end instead of setTimeout
                const handleTransitionEnd = () => {
                    messageElement.classList.add('hidden');
                    messageElement.style.transform = 'translateY(0)';
                    messageElement.removeEventListener('transitionend', handleTransitionEnd);
                };
                messageElement.addEventListener('transitionend', handleTransitionEnd);
            }
        }
        
        function resetApplyButton() {
            const applyBtn = document.getElementById('apply-discount');
            if (applyBtn) {
                applyBtn.disabled = false;
                applyBtn.textContent = 'Terapkan';
            }
        }
        
        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
        
        function handlePayment() {
            // Jika default gateway adalah Tripay, gunakan alur Tripay
            if (DEFAULT_GATEWAY === 'tripay') {
                handleTripayPayment();
                return;
            }
            const payButton = document.getElementById('pay-button');
            
            // Show loading state
            payButton.disabled = true;
            payButton.innerHTML = '<div class="flex items-center justify-center space-x-2"><div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Processing...</span></div>';
            
            // Get CSRF token
            const tokenInput = document.querySelector('input[name="_token"]');
            if (!tokenInput) {
                alert('Session expired. Please refresh the page.');
                resetButton();
                return;
            }
            
            // Prepare payment data including applied discount
            let paymentData = {
                course_id: {{ $course->id }}
            };
            
            // Include applied discount if exists
            if (appliedDiscount) {
                paymentData.applied_discount = {
                    id: appliedDiscount.id,
                    code: appliedDiscount.code,
                    name: appliedDiscount.name,
                    type: appliedDiscount.type,
                    value: appliedDiscount.value
                };
                
                console.log('💰 Sending payment data with discount:', paymentData);
            } else {
                console.log('💰 Sending payment data without discount:', paymentData);
            }
            
            // Make payment request
            fetch('{{ route('front.payment_store_courses_midtrans') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tokenInput.value
                },
                body: JSON.stringify(paymentData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                resetButton();
                
                if (data.snap_token) {
                    if (typeof snap === 'undefined') {
                        alert('Payment system not ready. Please refresh the page.');
                        return;
                    }
                    
                    // Open Midtrans payment popup
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.href = "{{ route('front.checkout.success') }}";
                        },
                        onPending: function(result) {
                            alert('Payment is pending. Please complete your payment.');
                            window.location.href = "{{ route('front.course.details', $course->slug) }}";
                        },
                        onError: function(result) {
                            alert('Payment failed. Please try again.');
                            window.location.href = "{{ route('front.course.details', $course->slug) }}";
                        },
                        onClose: function() {
                            // User closed popup without completing payment
                        }
                    });
                } else {
                    alert('Error: ' + (data.error || 'Unable to process payment'));
                }
            })
            .catch(error => {
                resetButton();
                alert('Network error. Please try again.');
            });
        }
        
        function resetButton() {
            const payButton = document.getElementById('pay-button');
            if (payButton) {
                payButton.disabled = false;
                const defaultLabel = (DEFAULT_GATEWAY === 'tripay') ? 'Bayar via Tripay' : 'Pay Now';
                payButton.innerHTML = '<div class="flex items-center justify-center space-x-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 003 3v8a3 3 0 003 3z"/></svg><span>' + defaultLabel + '</span></div>';
            }
        }
    </script>
@endpush
<script>
    async function handleTripayPayment() {
        // Ambil data dari konteks halaman yang sudah tersedia
        const courseId = {{ $course->id ?? 'null' }};
        const amount = parseInt(String(originalPricing?.grandTotal ?? {{ (int) ($grand_total_amount ?? ($course->price ?? 0)) }}));
        const customerName = `{{ auth()->user()->name ?? '' }}`;
        const customerEmail = `{{ auth()->user()->email ?? '' }}`;
        const customerPhone = `{{ auth()->user()->phone ?? '' }}`;

        const payload = {
            method: 'QRIS', // default; dapat diubah sesuai pilihan pengguna
            course_id: courseId,
            amount: amount,
            customer_name: customerName,
            customer_email: customerEmail,
            customer_phone: customerPhone,
        };

        try {
            const resp = await fetch('{{ route('front.payment_store_courses_tripay') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (data && data.pay_url) {
                window.location.href = data.pay_url;
            } else {
                alert(data?.error ? `Gagal membuat transaksi Tripay: ${data.error}` : 'Gagal membuat transaksi Tripay');
                console.error(data);
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan saat memproses Tripay');
        }
    }
</script>
