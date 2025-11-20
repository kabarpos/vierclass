@extends('front.layouts.app')
@section('title', 'Kajian & Pelatihan - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))

@section('content')
    
    <x-nav-guest />
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-charcoal-900 py-24 lg:py-32">
        <!-- Subtle Pattern -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
                <defs>
                    <pattern id="catalog-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="2" fill="currentColor" class="text-gold-500"/>
                    </pattern>
                </defs>
                <rect width="800" height="800" fill="url(#catalog-pattern)"/>
            </svg>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center space-x-2 px-4 py-2 bg-gold-600/10 border border-gold-600/20 backdrop-blur-sm rounded-full mb-8">
                <svg class="w-4 h-4 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-semibold text-gold-300">Semua Kajian</span>
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl lg:text-6xl font-bold text-beige-50 mb-6 leading-tight">
                Pilih Kajian Anda
            </h1>
            <p class="text-xl text-beige-300 max-w-3xl mx-auto leading-relaxed">
                Jelajahi koleksi lengkap kajian Islam yang dirancang untuk memperdalam pemahaman spiritual dan intelektual Anda
            </p>

            <!-- Stats Bar -->
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
                <div class="bg-beige-50/5 backdrop-blur-sm border border-beige-50/10 rounded-xl p-6">
                    <div class="text-3xl font-bold text-gold-400 mb-1">{{ $totalCourses ?? 0 }}</div>
                    <div class="text-sm text-beige-300">Total Kajian</div>
                </div>
                <div class="bg-beige-50/5 backdrop-blur-sm border border-beige-50/10 rounded-xl p-6">
                    <div class="text-3xl font-bold text-gold-400 mb-1">{{ number_format($totalStudents ?? 0) }}</div>
                    <div class="text-sm text-beige-300">Peserta</div>
                </div>
                <div class="bg-beige-50/5 backdrop-blur-sm border border-beige-50/10 rounded-xl p-6">
                    <div class="text-3xl font-bold text-gold-400 mb-1">100%</div>
                    <div class="text-sm text-beige-300">Online</div>
                </div>
                <div class="bg-beige-50/5 backdrop-blur-sm border border-beige-50/10 rounded-xl p-6">
                    <div class="text-3xl font-bold text-gold-400 mb-1">∞</div>
                    <div class="text-sm text-beige-300">Lifetime Access</div>
                </div>
            </div>
        </div>
    </section>

    <!-- All Courses Section -->
    <section class="bg-beige-50 py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if($allCourses->isNotEmpty())
                <!-- Section Header -->
                <div class="mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-charcoal-800 mb-4">Semua Kajian Tersedia</h2>
                    <p class="text-lg text-charcoal-600 max-w-2xl">
                        Temukan kajian yang sesuai dengan minat dan kebutuhan spiritual Anda
                    </p>
                </div>

                <!-- Courses Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($allCourses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-24">
                    <div class="w-24 h-24 bg-gradient-to-br from-gold-100 to-gold-200 rounded-full flex items-center justify-center mx-auto mb-8">
                        <svg class="w-12 h-12 text-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-charcoal-800 mb-4">Belum Ada Kajian Tersedia</h3>
                    <p class="text-charcoal-600 max-w-md mx-auto mb-8">
                        Kajian baru sedang dalam persiapan. Kembali lagi nanti untuk menemukan konten pembelajaran terbaru.
                    </p>
                    <a href="{{ route('front.index') }}" 
                       class="inline-flex items-center space-x-2 px-6 py-3 bg-charcoal-800 text-beige-50 font-semibold rounded-lg hover:bg-charcoal-700 transition-all duration-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="bg-gradient-to-br from-charcoal-800 to-charcoal-900 py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-5xl font-bold text-beige-50 mb-6">Keunggulan Pembelajaran</h2>
                <p class="text-lg text-beige-300 max-w-2xl mx-auto">
                    Mengapa memilih kajian individual untuk perjalanan pembelajaran Anda
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Benefit 1 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-16 h-16 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Bayar Sekali, Akses Selamanya</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Tanpa langganan bulanan. Beli kajian spesifik yang sesuai kebutuhan Anda dengan akses seumur hidup
                        </p>
                    </div>
                </div>
                
                <!-- Benefit 2 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-16 h-16 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Belajar Sesuai Ritme Anda</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Fleksibilitas penuh untuk belajar kapan saja dan di mana saja sesuai kenyamanan Anda
                        </p>
                    </div>
                </div>
                
                <!-- Benefit 3 -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gold-900/20 to-charcoal-900/20 backdrop-blur-sm border border-gold-500/10 p-8 hover:border-gold-500/30 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all duration-300"></div>
                    <div class="relative">
                        <div class="w-16 h-16 bg-gold-600/20 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-beige-50 mb-3">Sertifikat Penyelesaian</h3>
                        <p class="text-beige-300 leading-relaxed">
                            Dapatkan pengakuan atas pencapaian Anda dengan sertifikat resmi setelah menyelesaikan kajian
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <a href="{{ route('register') }}" 
                   class="inline-flex items-center space-x-2 px-8 py-4 bg-gold-600 text-charcoal-900 font-bold text-lg rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-[0_8px_32px_rgba(201,169,97,0.3)] hover:shadow-[0_12px_48px_rgba(201,169,97,0.5)] hover:scale-105 cursor-pointer">
                    <span>Mulai Perjalanan Belajar Anda</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

@endsection
