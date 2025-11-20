@extends('front.layouts.app')
@section('title', \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') . ' - Pemikir, Sejarawan, Trainer')
@section('content')
    
    <x-nav-guest />
    
    <!-- Hero Section - Premium & Cinematic -->
    <section class="relative overflow-hidden bg-gradient-to-br from-charcoal-800 via-charcoal-700 to-charcoal-800 min-h-screen flex items-center">
        <!-- Subtle Islamic Geometric Pattern Background -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
                <defs>
                    <pattern id="islamic-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <path d="M50 0L100 50L50 100L0 50Z" fill="none" stroke="currentColor" stroke-width="0.5" class="text-gold-500"/>
                        <circle cx="50" cy="50" r="8" fill="none" stroke="currentColor" stroke-width="0.5" class="text-gold-500"/>
                    </pattern>
                </defs>
                <rect width="800" height="800" fill="url(#islamic-pattern)"/>
            </svg>
        </div>
        
        <!-- Gradient Overlay for Depth -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-charcoal-800/50 to-charcoal-900"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Hero Content -->
                <div class="lg:col-span-7 space-y-10">
                    <!-- Badge -->
                    <div class="inline-flex items-center space-x-3 px-5 py-2.5 bg-gold-600/10 border border-gold-600/20 backdrop-blur-sm rounded-full">
                        <div class="w-2 h-2 bg-gold-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold tracking-wide text-gold-300 uppercase">Kajian & Pelatihan</span>
                    </div>
                    
                    <!-- Main Heading -->
                    <div class="space-y-6">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold text-beige-50 leading-[1.1]">
                            Pemikir, Sejarawan, <span class="text-gold-400 font-extrabold">Trainer</span>
                        </h1>
                        <p class="text-xl lg:text-2xl text-beige-200 leading-relaxed max-w-2xl font-light">
                            Menggali khazanah intelektual Islam dengan pendekatan akademis, kontemporer, dan aplikatif untuk transformasi diri dan masyarakat.
                        </p>
                    </div>
                    
                    <!-- Manifesto/Quote -->
                    <div class="relative pl-6 border-l-2 border-gold-500">
                        <p class="text-lg italic text-beige-300 leading-relaxed">
                            "Ilmu tanpa amal seperti pohon tanpa buah. Amal tanpa ilmu seperti perjalanan tanpa tujuan."
                        </p>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <a href="{{ route('front.courses') }}" 
                           class="group inline-flex items-center justify-center px-8 py-4 bg-gold-600 text-charcoal-900 font-bold text-lg rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-[0_8px_32px_rgba(201,169,97,0.3)] hover:shadow-[0_12px_48px_rgba(201,169,97,0.5)] hover:scale-105 cursor-pointer">
                            <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Jelajahi Kajian
                        </a>
                        <a href="{{ route('register') }}" 
                           class="group inline-flex items-center justify-center px-8 py-4 bg-beige-50/5 backdrop-blur-sm border-2 border-beige-200/20 text-beige-50 font-semibold text-lg rounded-xl hover:bg-beige-50/10 hover:border-beige-200/40 transition-all duration-300 cursor-pointer">
                            Bergabung Sekarang
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-8 pt-8 border-t border-beige-50/10">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl lg:text-4xl font-bold text-gold-400">{{ $totalCourses ?? 0 }}+</div>
                            <div class="text-sm text-beige-300 mt-1">Kajian Tersedia</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl lg:text-4xl font-bold text-gold-400">{{ number_format($totalStudents ?? 0) }}+</div>
                            <div class="text-sm text-beige-300 mt-1">Peserta Aktif</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl lg:text-4xl font-bold text-gold-400">15+</div>
                            <div class="text-sm text-beige-300 mt-1">Tahun Pengalaman</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Portrait/Image - Silhouette with Glassmorphism -->
                <div class="lg:col-span-5 relative">
                    <div class="relative">
                        <!-- Glassmorphism Frame -->
                        <div class="relative overflow-hidden rounded-3xl shadow-[0_24px_64px_rgba(0,0,0,0.4)] border border-gold-500/20 bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-xl">
                            <x-lazy-image 
                                src="{{ asset('assets/images/backgrounds/dashboard.webp') }}" 
                                alt="Islamic Thinker" 
                                class="w-full aspect-[3/4] object-cover opacity-90 mix-blend-luminosity"
                                loading="eager" />
                            
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-charcoal-900 via-charcoal-900/20 to-transparent"></div>
                            
                            <!-- Floating Calligraphy Accent -->
                            <div class="absolute top-8 right-8 opacity-20">
                                <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                                    <path d="M40 10 C 60 15, 70 25, 70 40 C 70 55, 60 65, 40 70 C 20 65, 10 55, 10 40 C 10 25, 20 15, 40 10 Z" stroke="currentColor" stroke-width="1" fill="none" class="text-gold-400"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Decorative Gold Accent -->
                        <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-gradient-to-br from-gold-500 to-gold-700 rounded-full blur-2xl opacity-30"></div>
                        <div class="absolute -top-4 -right-4 w-32 h-32 bg-gradient-to-br from-gold-500 to-gold-700 rounded-full blur-3xl opacity-20"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2">
            <div class="flex flex-col items-center space-y-2 animate-bounce">
                <span class="text-xs text-beige-400 uppercase tracking-wider">Scroll</span>
                <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    @if($featuredCourses->isNotEmpty())
    <section class="bg-beige-50 py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16 space-y-4">
                <div class="inline-flex items-center space-x-2 px-4 py-2 bg-gold-100 rounded-full">
                    <svg class="w-4 h-4 text-gold-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-sm font-semibold text-gold-800">Pilihan Terbaik</span>
                </div>
                <h2 class="text-3xl lg:text-5xl font-bold text-charcoal-800">Kajian Unggulan</h2>
                <p class="text-lg lg:text-xl text-charcoal-600 max-w-2xl mx-auto leading-relaxed">
                    Pilihan kajian terbaik untuk memperdalam pemahaman Islam secara akademis dan aplikatif
                </p>
            </div>
            
            <!-- Courses Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredCourses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>

            <!-- View All Link -->
            <div class="text-center mt-16">
                <a href="{{ route('front.courses') }}" 
                   class="inline-flex items-center space-x-2 px-8 py-4 bg-charcoal-800 text-beige-50 font-semibold rounded-xl hover:bg-charcoal-700 transition-all duration-300 shadow-lg hover:shadow-xl cursor-pointer">
                    <span>Lihat Semua Kajian</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Testimonials/Values Section -->
    <section class="bg-gradient-to-br from-charcoal-800 to-charcoal-900 py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-5xl font-bold text-beige-50 mb-6">Nilai-Nilai Pembelajaran</h2>
                <p class="text-lg text-beige-300 max-w-2xl mx-auto">
                    Pendekatan komprehensif dalam membangun pemahaman Islam yang mendalam dan transformatif
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Value 1 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-14 h-14 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Akademis & Kontemporer</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Kajian berbasis riset dengan metodologi akademis yang relevan dengan konteks kekinian
                        </p>
                    </div>
                </div>

                <!-- Value 2 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-14 h-14 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Transformatif & Aplikatif</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Pembelajaran yang tidak hanya konseptual, tetapi dapat diterapkan dalam kehidupan nyata
                        </p>
                    </div>
                </div>

                <!-- Value 3 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-14 h-14 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Komunitas & Kolaboratif</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Belajar bersama komunitas yang aktif dan saling mendukung dalam perjalanan spiritual
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
