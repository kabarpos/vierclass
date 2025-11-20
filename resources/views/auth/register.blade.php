@extends('front.layouts.app')
@section('title', 'Register - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))

@section('content')
    <x-nav-guest />
    <main class="relative min-h-screen flex items-center justify-center py-12 px-5 overflow-hidden">
        <!-- Dark Red Combination Background - Same Formula as Hero -->
        <div class="absolute inset-0 bg-gradient-to-br from-rebel-red-950/95 via-rebel-red-900/90 to-rebel-black-1000/95"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-rebel-black-1000/70 via-transparent to-rebel-black-1000/80"></div>
        <div class="absolute inset-0 bg-rebel-red-950/40"></div>
        
        <section class="relative w-full max-w-lg">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                class="flex flex-col w-full rounded-[20px] border border-rebel-red-900/30 p-8 gap-4 bg-rebel-black-1000/80 backdrop-blur-sm shadow-2xl">
                @csrf
                <h1 class="font-bold text-[22px] leading-[33px] text-center mb-4 text-beige-50">Upgrade Your Skills</h1>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200 mb-1">Complete Name</p>
                    <label class="relative group">
                        <input name="name" type="text" required
                            class="appearance-none outline-none w-full rounded-full border border-rebel-red-900/40 bg-rebel-black-900/50 py-[14px] px-5 pl-12 font-semibold text-beige-50 placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition-all duration-300"
                            placeholder="Type your complete name">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/profile.svg') }}"
                            alt="profile icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200 mb-1">WhatsApp Number</p>
                    <label class="relative group">
                        <input name="whatsapp_number" type="tel" required
                            class="appearance-none outline-none w-full rounded-full border border-rebel-red-900/40 bg-rebel-black-900/50 py-[14px] px-5 pl-12 font-semibold text-beige-50 placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition-all duration-300"
                            placeholder="e.g., +62812345678">
                        <svg class="absolute w-5 h-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5 text-beige-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </label>
                    <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200 mb-1">Email Address</p>
                    <label class="relative group">
                        <input name="email" type="email" required
                            class="appearance-none outline-none w-full rounded-full border border-rebel-red-900/40 bg-rebel-black-900/50 py-[14px] px-5 pl-12 font-semibold text-beige-50 placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition-all duration-300"
                            placeholder="Type your valid email address">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/sms.svg') }}"
                            alt="email icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200 mb-1">Password</p>
                    <label class="relative group">
                        <input name="password" type="password" required
                            class="appearance-none outline-none w-full rounded-full border border-rebel-red-900/40 bg-rebel-black-900/50 py-[14px] px-5 pl-12 font-semibold text-beige-50 placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition-all duration-300"
                            placeholder="Type your password">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/shield-security.svg') }}"
                            alt="password icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-beige-200 mb-1">Confirm Password</p>
                    <label class="relative group">
                        <input name="password_confirmation" type="password" required
                            class="appearance-none outline-none w-full rounded-full border border-rebel-red-900/40 bg-rebel-black-900/50 py-[14px] px-5 pl-12 font-semibold text-beige-50 placeholder:font-normal placeholder:text-beige-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 transition-all duration-300"
                            placeholder="Confirm your password">
                        <x-lazy-image 
                            src="{{ asset('assets/images/icons/shield-security.svg') }}"
                            alt="confirm password icon"
                            class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                            loading="eager" />
                    </label>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
                <button type="submit"
                    class="flex items-center justify-center gap-[10px] rounded-full py-[14px] px-5 bg-gold-600 hover:bg-gold-500 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer">
                    <span class="font-semibold text-charcoal-900">Create My Account</span>
                </button>
                <!-- Register via Google (dipindah ke bawah tombol default) -->
                <a href="{{ route('oauth.google.redirect') }}" class="flex items-center justify-center gap-[10px] rounded-full py-[12px] px-5 border-2 border-gold-600 text-gold-400 hover:bg-gold-600/10 transition-all duration-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="shrink-0">
                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                    </svg>
                    <span class="font-semibold">Daftar dengan Google</span>
                </a>
            </form>
            
            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-beige-300">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-gold-400 font-semibold hover:text-gold-300 hover:underline cursor-pointer">Masuk Sekarang</a>
                </p>
            </div>
        </section>
        
    </main>
@endsection
