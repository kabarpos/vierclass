@extends('front.layouts.app')
@section('title', 'Login - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))

@section('content')
    <x-nav-guest/>
    <main class="min-h-screen flex items-center justify-center py-12 px-5 bg-gradient-to-b from-charcoal-900 to-charcoal-800">
        <section class="w-full max-w-lg">
            @if(session('success') || session('warning') || session('error'))
                <div class="mb-4">
                    @if(session('success'))
                        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                            {{ session('warning') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- Rate Limit Modal -->
            <div id="rateLimitModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
                <div class="w-full max-w-md rounded-2xl bg-charcoal-800 border border-charcoal-700 shadow-xl">
                    <div class="px-6 pt-6">
                        <h3 class="text-lg font-bold text-beige-50">Terlalu Banyak Percobaan Login</h3>
                        <p class="mt-2 text-sm text-beige-300">
                            @php($blockedSeconds = session('blocked_seconds'))
                            {{ session('error') ?? 'Akun Anda sementara diblokir karena terlalu banyak percobaan login yang gagal.' }}
                            @if($blockedSeconds)
                                <br>Silakan coba lagi dalam sekitar {{ ceil($blockedSeconds/60) }} menit.
                            @endif
                        </p>
                    </div>
                    <div class="px-6 pb-6 mt-4 flex gap-3">
                        <button type="button" id="rateLimitModalClose" class="flex-1 rounded-full bg-gold-600 px-5 py-3 text-charcoal-900 font-bold">Mengerti</button>
                        <a href="{{ route('password.reset.options') }}" class="flex-1 rounded-full border border-gold-400 px-5 py-3 text-gold-400 font-semibold text-center cursor-pointer">Lupa Password?</a>
                    </div>
                </div>
            </div>
            <form  method="POST" action="{{ route('login') }}" class="flex flex-col w-full rounded-[20px] border border-charcoal-700 p-8 gap-5 bg-charcoal-800/80 backdrop-blur-sm shadow-lg">
                @csrf
                <h1 class="font-bold text-[22px] leading-[33px] mb-5 text-center text-beige-50">Welcome Back, <br>Let's Upgrade Skills</h1>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200">Email Address</p>
                    <label class="relative group">
                        <input name="email" type="email" required
                            class="appearance-none outline-none w-full rounded-full bg-charcoal-900 border border-charcoal-700 text-beige-50 py-[14px] px-5 pl-12 font-semibold placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500 transition-all duration-300"
                            placeholder="Type your valid email address">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/sms.svg') }}"
                            alt="email icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-sm font-medium text-beige-200">Password</p>
                    <label class="relative group">
                        <input name="password" type="password" required
                            class="appearance-none outline-none w-full rounded-full bg-charcoal-900 border border-charcoal-700 text-beige-50 py-[14px] px-5 pl-12 font-semibold placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500 transition-all duration-300"
                            placeholder="Type your password">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/shield-security.svg') }}"
                            alt="password icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <a href="{{ route('password.reset.options') }}" class="text-sm text-gold-400 hover:underline cursor-pointer">Forgot My Password</a>
                </div>
                <button type="submit"
                    class="flex items-center justify-center gap-[10px] rounded-full py-[14px] px-5 bg-gold-600 hover:bg-gold-500 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer">
                    <span class="font-bold text-charcoal-900">Sign In to My Account</span>
                </button>
                <!-- Login via Google (moved below default button) -->
                <a href="{{ route('oauth.google.redirect') }}" class="flex items-center justify-center gap-[10px] rounded-full py-[12px] px-5 border border-gold-400 text-gold-400 hover:bg-gold-600/10 transition-all duration-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="shrink-0">
                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                    </svg>
                    <span class="font-semibold">Masuk dengan Google</span>
                </a>
            </form>
            
            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-beige-300">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-gold-400 font-semibold hover:underline cursor-pointer">Daftar Sekarang</a>
                </p>
            </div>
            
            <!-- Resend Verification Section -->
            <div class="mt-6 p-6 bg-yellow-50 border border-yellow-200 rounded-[20px] resend-section-hidden" id="resend-section">
                <h3 class="font-bold text-lg mb-4 text-yellow-800">Kirim Ulang Verifikasi</h3>
                <p class="text-sm text-yellow-700 mb-4">Jika Anda belum menerima link verifikasi di WhatsApp, masukkan email Anda di bawah ini untuk mengirim ulang.</p>
                
                <form method="POST" action="{{ route('whatsapp.verification.resend') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="relative group">
                            <input name="email" type="email" required
                                class="appearance-none outline-none w-full rounded-full border border-yellow-300 py-[12px] px-5 pl-12 font-semibold placeholder:font-normal placeholder:text-gray-500 group-focus-within:border-yellow-500 transition-all duration-300"
                                placeholder="Masukkan email Anda">
                            <x-lazy-image src="{{ asset('assets/images/icons/sms.svg') }}"
                                class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                                alt="icon" loading="eager" />
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-[10px] rounded-full py-[12px] px-5 bg-yellow-600 hover:bg-yellow-700 transition-all duration-300 cursor-pointer">
                        <span class="font-semibold text-white">Kirim Ulang Verifikasi</span>
                    </button>
                </form>
            </div>
            
            <script nonce="{{ request()->attributes->get('csp_nonce') }}">
                // Show resend section if there's a verification error
                document.addEventListener('DOMContentLoaded', function() {
                    const errorMessages = document.querySelectorAll('.text-red-600');
                    const resendSection = document.getElementById('resend-section');
                    
                    errorMessages.forEach(function(error) {
                        if (error.textContent.includes('belum terverifikasi') || error.textContent.includes('not verified')) {
                            resendSection.style.display = 'block';
                        }
                    });

                    // Show rate limit modal if blocked
                    const isRateLimited = {{ session('rate_limit_blocked') ? 'true' : 'false' }};
                    if (isRateLimited) {
                        const modal = document.getElementById('rateLimitModal');
                        const closeBtn = document.getElementById('rateLimitModalClose');
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        closeBtn.addEventListener('click', function() {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        });
                    }
                });
            </script>
        </section>
       
    </main>
@endsection
