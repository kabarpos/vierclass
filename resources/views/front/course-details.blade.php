@extends('front.layouts.app')
@section('title', $course->name . ' - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))

@section('content')
    @auth
        <x-navigation-auth />
    @else
        <x-nav-guest />
    @endauth
    
    <!-- Clean Hero Section -->
    <section class="bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-charcoal-900 border-b border-charcoal-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Course Information -->
                <div class="space-y-8">
                    <!-- Category Badge -->
                    @if($course->category)
                        <div class="inline-flex items-center px-4 py-2 bg-gold-100 text-gold-700 rounded-full border border-gold-300">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-sm font-medium">{{ $course->category->name }}</span>
                        </div>
                    @endif
                    
                    <!-- Course Title -->
                    <div class="space-y-4">
                        <h1 class="text-3xl lg:text-4xl font-bold text-beige-50 leading-tight">
                            {{ $course->name }}
                        </h1>
                        <p class="text-lg text-beige-300 leading-relaxed max-w-2xl">
                            {{ $course->about }}
                        </p>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex flex-wrap items-center gap-6">
                        <!-- Rating -->
                        <div class="flex items-center space-x-2 bg-charcoal-800/50 backdrop-blur-sm px-4 py-2 rounded-lg border border-charcoal-700">
                            <div class="flex items-center space-x-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-semibold text-beige-200">5.0</span>
                        </div>
                        
                        <!-- Students Count -->
                        <div class="flex items-center space-x-2 bg-gold-50/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-gold-200/20">
                            <svg class="w-4 h-4 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                            <span class="text-sm font-semibold text-beige-200">{{ $studentsCount }} Students</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        @if($course->price > 0)
                            <!-- Paid Course -->
                            @auth
                                @if(auth()->user()->hasPurchasedCourse($course->id))
                                    <!-- User already owns the course -->
                                    <div class="flex flex-col sm:flex-row gap-4 w-full">
                                        <a href="{{ route('dashboard.course.join', $course->slug) }}" 
                                           class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-lg hover:bg-gold-500 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9 4h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Continue Learning
                                        </a>
                                        <div class="sm:w-auto w-full px-4 py-3 bg-gold-50 text-gold-800 font-bold rounded-lg border border-gold-300 text-center">
                                            <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Course Owned
                                        </div>
                                    </div>
                                @else
                                    <!-- User hasn't purchased, show purchase option -->
                                    <div class="flex flex-col space-y-4 w-full">
                                        <div class="bg-gold-50/20 backdrop-blur-sm border border-gold-300 rounded-xl p-6">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    @if($course->original_price && $course->original_price > $course->price)
                                                        <!-- Show original price with strikethrough -->
                                                        <div class="flex items-center space-x-2 mb-1">
                                                            <span class="text-lg text-gray-500 line-through">
                                                                Rp {{ number_format($course->original_price, 0, '', '.') }}
                                                            </span>
                                                            <span class="bg-gold-100 text-gold-800 text-xs font-bold px-2 py-1 rounded-full">
                                                                {{ round((($course->original_price - $course->price) / $course->original_price) * 100) }}% OFF
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="text-2xl font-bold text-gold-700">
                                                        Rp {{ number_format($course->price, 0, '', '.') }}
                                                    </div>
                                                    <div class="text-sm text-gold-600 font-semibold">One-time purchase • Lifetime access</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-charcoal-600">Get instant access to</div>
                                                    <div class="text-sm font-bold text-charcoal-800">{{ $totalLessons }} lessons</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <a href="{{ route('front.course.checkout', $course->slug) }}" 
                                               class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 cursor-pointer shadow-[0_8px_32px_rgba(201,169,97,0.3)] hover:shadow-[0_12px_48px_rgba(201,169,97,0.5)]">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                Buy Now - Rp {{ number_format($course->price, 0, '', '.') }}
                                            </a>
                                            <button onclick="alert('Coming soon: Add to wishlist feature!')" 
                                                    class="px-6 py-3 border-2 border-charcoal-700 text-beige-200 font-semibold rounded-xl hover:bg-charcoal-700 hover:border-gold-400 transition-all duration-300 cursor-pointer">
                                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                                Save for Later
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- Not authenticated user -->
                                <div class="flex flex-col space-y-4 w-full">
                                    <div class="bg-gold-50/20 backdrop-blur-sm border border-gold-300 rounded-xl p-6">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                @if($course->original_price && $course->original_price > $course->price)
                                                    <!-- Show original price with strikethrough -->
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <span class="text-lg text-gray-500 line-through">
                                                            Rp {{ number_format($course->original_price, 0, '', '.') }}
                                                        </span>
                                                        <span class="bg-gold-100 text-gold-800 text-xs font-bold px-2 py-1 rounded-full">
                                                            {{ round((($course->original_price - $course->price) / $course->original_price) * 100) }}% OFF
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="text-2xl font-bold text-gold-700">
                                                    Rp {{ number_format($course->price, 0, '', '.') }}
                                                </div>
                                                <div class="text-sm text-gold-600 font-semibold">One-time purchase • Lifetime access</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-charcoal-600">Get instant access to</div>
                                                <div class="text-sm font-bold text-charcoal-800">{{ $totalLessons }} lessons</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <a href="{{ route('register') }}" 
                                           class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 cursor-pointer shadow-[0_8px_32px_rgba(201,169,97,0.3)] hover:shadow-[0_12px_48px_rgba(201,169,97,0.5)]">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Sign Up to Buy
                                        </a>
                                        <a href="{{ route('login') }}" 
                                           class="px-6 py-3 border-2 border-charcoal-700 text-beige-200 font-semibold rounded-xl hover:bg-charcoal-700 hover:border-gold-400 transition-all duration-300 cursor-pointer">
                                            Already have an account? Sign In
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            <!-- Free Course -->
                            @auth
                                <a href="{{ route('dashboard.course.join', $course->slug) }}" 
                                   class="inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Start Learning for Free
                                </a>
                            @else
                                <a href="{{ route('register') }}" 
                                   class="inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Sign Up to Start Learning
                                </a>
                                <a href="{{ route('login') }}" 
                                   class="inline-flex items-center justify-center px-6 py-3 border-2 border-charcoal-300 text-charcoal-700 font-semibold rounded-xl hover:bg-beige-100 hover:border-gold-400 transition-all duration-300 cursor-pointer">
                                    Sign In
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
                
                <!-- Course Visual -->
                <div class="flex justify-center lg:justify-end">
                    <div class="relative w-full max-w-lg">
                        <div class="aspect-video rounded-xl overflow-hidden bg-charcoal-900 shadow-lg">
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
                                <div class="w-full h-full flex items-center justify-center bg-gold-100">
                                    <div class="text-center">
                                        <div class="text-gold-700 font-bold text-3xl mb-2">{{ substr($course->name, 0, 2) }}</div>
                                        <div class="text-gold-600 text-sm font-semibold">Course Preview</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
        
    <!-- Main Content Section -->
    <main class="bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-charcoal-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Course Content -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- Course Curriculum Section -->
                    <div class="bg-charcoal-800/50 backdrop-blur-sm rounded-2xl shadow-elevated border border-charcoal-700">
                        <!-- Section Header -->
                        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b border-charcoal-700">
                            <h2 class="text-xl sm:text-2xl font-bold text-beige-50">Kurikulum Kajian</h2>
                            <p class="text-beige-300 mt-2 text-sm sm:text-base">Jalur pembelajaran terstruktur yang dirancang dengan sistematis</p>
                        </div>
                        
                        <!-- Curriculum Content -->
                        <div class="p-4 sm:p-6 lg:p-8">
                            @if(!empty($sectionContentCounts))
                                <div class="space-y-4" x-data="{ openSections: {} }">
                                    @foreach($course->courseSections as $index => $section)
                                        @php
                                            $sectionId = 'section_' . $section->id;
                                            $freeContentCount = $sectionFreeCounts[$section->id] ?? 0;
                                        @endphp
                                        <div class="border border-charcoal-700 rounded-xl overflow-hidden shadow-sm">
                                            <!-- Section Header (Clickable) -->
                                            <button 
                                                @click="openSections['{{ $sectionId }}'] = !openSections['{{ $sectionId }}']"
                                                class="w-full bg-charcoal-800/80 backdrop-blur-sm px-4 sm:px-6 py-4 border-b border-charcoal-700 hover:bg-charcoal-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-inset cursor-pointer">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                    <div class="flex items-center min-w-0 flex-1">
                                                        <!-- Section Number -->
                                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gold-600 text-charcoal-900 rounded-lg flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0 shadow-md">
                                                            <span class="font-bold text-xs sm:text-sm">{{ $index + 1 }}</span>
                                                        </div>
                                                        
                                                        <!-- Section Info -->
                                                        <div class="text-left min-w-0 flex-1">
                                                            <h3 class="text-base sm:text-lg font-bold text-beige-50 truncate">{{ $section->name }}</h3>
                                                            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm text-beige-300 mt-1">
                                                                <span class="text-xs sm:text-sm">{{ $sectionContentCounts[$section->id] ?? 0 }} lessons</span>
                                                                @if($freeContentCount > 0)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold-600/20 text-gold-400">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                        <span class="hidden sm:inline">{{ $freeContentCount }} Free Preview{{ $freeContentCount > 1 ? 's' : '' }}</span>
                                                                        <span class="sm:hidden">{{ $freeContentCount }} Free</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                         <!-- Right Side: Badges and Arrow -->
                                                    <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
                                                        <!-- Dropdown Arrow -->
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-beige-400 transform transition-transform duration-300 flex-shrink-0" 
                                                             :class="openSections['{{ $sectionId }}'] ? 'rotate-180' : ''"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                    </div>
                                                    
                                                   
                                                </div>
                                            </button>
                                            
                                            <!-- Lessons List (Collapsible) -->
                                            <div x-show="openSections['{{ $sectionId }}']" 
                                                 x-collapse 
                                                 class="bg-charcoal-900/30">
                                                <div class="p-4 sm:p-6">
                                                    <div class="space-y-3">
                                                        @foreach($section->sectionContents as $contentIndex => $content)
                                                            @php
                                                                $isLocked = !$content->is_free && !auth()->check();
                                                            @endphp
                                                            <div class="group relative">
                                                                @if($content->is_free)
                                                                    <!-- Free Preview Content -->
                                                                    <a href="{{ route('front.course.preview', ['course' => $course->slug, 'sectionContent' => $content->id]) }}" 
                                                                       class="flex flex-col sm:flex-row sm:items-center p-3 sm:p-4 rounded-lg border border-charcoal-700 hover:border-gold-400 hover:bg-charcoal-700/50 transition-all duration-300 cursor-pointer gap-3 sm:gap-0">
                                                                        <div class="flex items-center flex-1 min-w-0">
                                                                            <!-- Lesson Icon -->
                                                                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gold-600/20 text-gold-400 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                                                </svg>
                                                                            </div>
                                                                            
                                                                            <!-- Lesson Info -->
                                                                            <div class="flex-1 min-w-0">
                                                                                <h4 class="font-semibold text-beige-50 group-hover:text-gold-400 text-sm sm:text-base truncate">{{ $content->name }}</h4>
                                                                    
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Free Badge -->
                                                                        <div class="flex items-center justify-between sm:justify-end space-x-2">
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold-600/20 text-gold-400">
                                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                                </svg>
                                                                                Preview
                                                                            </span>

                                                                        </div>
                                                                    </a>
                                                                @else
                                                                    <!-- Locked/Premium Content -->
                                                                    <div class="flex flex-col sm:flex-row sm:items-center p-3 sm:p-4 rounded-lg border border-charcoal-700 {{ $isLocked ? 'bg-charcoal-900/50' : 'hover:border-gold-400 hover:bg-charcoal-700/50' }} transition-all duration-300 gap-3 sm:gap-0">
                                                                        <div class="flex items-center flex-1 min-w-0">
                                                                            <!-- Lesson Icon -->
                                                                            <div class="w-6 h-6 sm:w-8 sm:h-8 {{ $isLocked ? 'bg-gold-600/10 text-gold-400' : 'bg-gold-600/20 text-gold-400' }} rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                                                @if($isLocked)
                                                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                                    </svg>
                                                                                @else
                                                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                                                    </svg>
                                                                                @endif
                                                                            </div>
                                                                            
                                                                            <!-- Lesson Info -->
                                                                            <div class="flex-1 min-w-0">
                                                                                <h4 class="font-semibold {{ $isLocked ? 'text-beige-300' : 'text-beige-50' }} text-sm sm:text-base truncate">{{ $content->name }}</h4>
                                                                               
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Status -->
                                                                        @if($isLocked)
                                                                            <div class="flex items-center justify-between sm:justify-end space-x-2">
                                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold-600/20 text-gold-400">
                                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                                    </svg>
                                                                                    Premium
                                                                                </span>
                                                                            </div>
                                                                        @else
                                                                            <div class="w-6 h-6 border-2 border-gold-400 rounded-full flex-shrink-0"></div>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                        
                                                        @if(($sectionContentCounts[$section->id] ?? 0) === 0)
                                                            <div class="text-center py-6">
                                                                <div class="w-12 h-12 bg-charcoal-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                                                    <svg class="w-6 h-6 text-charcoal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                                    </svg>
                                                                </div>
                                                                <p class="text-sm text-charcoal-500">Belum ada materi di section ini</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-charcoal-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-charcoal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-charcoal-800 mb-2">Konten Kajian Segera Hadir</h3>
                                    <p class="text-charcoal-600">Kami sedang menyiapkan kurikulum terbaik untuk Anda. Nantikan!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                        
                    <!-- What You'll Learn Section -->
                    @if($course->benefits->count() > 0)
                    <div class="bg-charcoal-800/50 backdrop-blur-sm rounded-2xl shadow-elevated border border-charcoal-700">
                        <div class="px-8 py-6 border-b border-charcoal-700">
                            <h2 class="text-2xl font-bold text-beige-50">Yang Akan Anda Pelajari</h2>
                            <p class="text-beige-300 mt-2">Keterampilan dan pengetahuan kunci yang akan Anda peroleh dari kajian ini</p>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($course->benefits as $index => $benefit)
                                    <div class="flex items-start space-x-3 p-4 rounded-lg border border-beige-200 hover:border-gold-400 hover:bg-gold-50/10 transition-all duration-300">
                                        <div class="w-6 h-6 bg-gold-100 text-gold-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-beige-50">{{ $benefit->name }}</h3>
                                            <p class="text-sm text-beige-300 mt-1">Kuasai keterampilan penting ini melalui praktik langsung</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-6">
                        <!-- Course Stats -->
                        <div class="bg-charcoal-800/50 backdrop-blur-sm rounded-2xl shadow-elevated border border-charcoal-700 p-6">
                            <h3 class="text-lg font-bold text-beige-50 mb-4">Detail Kajian</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between py-2 border-b border-charcoal-700">
                                    <span class="text-beige-300">Total Materi</span>
                                    <span class="font-bold text-beige-50">{{ $totalLessons }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-charcoal-700">
                                    <span class="text-beige-300">Peserta Terdaftar</span>
                                    <span class="font-bold text-beige-50">{{ $studentsCount }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-charcoal-700">
                                    <span class="text-beige-300">Level Kajian</span>
                                    <span class="font-bold text-beige-50">Pemula</span>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-beige-300">Sertifikat</span>
                                    <span class="font-bold text-gold-400">Ya</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="bg-charcoal-800/50 backdrop-blur-sm rounded-2xl shadow-elevated border border-charcoal-700 p-6">
                            <h3 class="text-lg font-bold text-beige-50 mb-4">Aksi Cepat</h3>
                            <div class="space-y-3">
                                @auth
                                    <a href="{{ route('dashboard.course.join', $course->slug) }}" 
                                       class="w-full flex items-center justify-center px-4 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Start Learning
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" 
                                       class="w-full flex items-center justify-center px-4 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-xl hover:bg-gold-500 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Create Account
                                    </a>
                                @endauth
                                
                                <!-- Share Dropdown -->
                                <div class="relative" x-data="{ shareOpen: false }">
                                    <button @click="shareOpen = !shareOpen" class="w-full flex items-center justify-center px-4 py-3 border-2 border-charcoal-700 text-beige-200 font-semibold rounded-xl hover:bg-charcoal-700 hover:border-gold-400 transition-all duration-300 cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                        </svg>
                                        Share Course
                                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': shareOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div x-show="shareOpen" 
                                         @click.away="shareOpen = false"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute top-full left-0 right-0 mt-2 bg-charcoal-800 rounded-xl shadow-elevated border border-charcoal-700 z-50">
                                        <div class="py-2">
                                            <!-- WhatsApp -->
                                            <button onclick="shareToWhatsApp()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer">
                                                <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.315"/>
                                                </svg>
                                                Share to WhatsApp
                                            </button>
                                            
                                            <!-- Telegram -->
                                            <button onclick="shareToTelegram()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer">
                                                <svg class="w-5 h-5 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                                </svg>
                                                Share to Telegram
                                            </button>
                                            
                                            <!-- Facebook -->
                                            <button onclick="shareToFacebook()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer">
                                                <svg class="w-5 h-5 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                                Share to Facebook
                                            </button>
                                            
                                            <!-- X (Twitter) -->
                                            <button onclick="shareToX()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer">
                                                <svg class="w-5 h-5 mr-3 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                </svg>
                                                Share to X
                                            </button>
                                            
                                            <!-- Threads -->
                                            <button onclick="shareToThreads()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer">
                                                <svg class="w-5 h-5 mr-3 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.781 3.631 2.695 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 0 1 3.02.142c-.126-.742-.375-1.332-.734-1.74-.369-.42-.986-.721-1.832-.897a9.158 9.158 0 0 0-1.675-.146c-.953.021-1.805.193-2.53.511-.727.317-1.342.817-1.828 1.484-.394.543-.622 1.178-.678 1.887l-1.8-.336c.08-.931.383-1.77.9-2.497.649-.909 1.482-1.617 2.475-2.107.999-.492 2.101-.744 3.273-.748 2.446-.007 4.255.621 5.378 1.867 1.24 1.375 1.786 3.185 1.624 5.375a11.99 11.99 0 0 1 3.11-2.53c.299-.168.646-.284 1.032-.343l.449 1.92c-.229.053-.424.133-.584.238a9.977 9.977 0 0 0-2.538 2.24c.146.326.249.678.307 1.056.485 3.176-.836 5.313-3.288 6.344-.653.274-1.378.413-2.157.413z"/>
                                                </svg>
                                                Share to Threads
                                            </button>
                                            
                                            <!-- Copy Link -->
                                            <button onclick="copyLink()" class="w-full flex items-center px-4 py-3 text-sm text-beige-200 font-medium hover:bg-charcoal-700 transition-all duration-200 cursor-pointer border-t border-charcoal-700">
                                                <svg class="w-5 h-5 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                Copy Link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category Info -->
                        @if($course->category)
                        <div class="bg-charcoal-800/50 backdrop-blur-sm rounded-2xl shadow-elevated border border-charcoal-700 p-6">
                            <h3 class="text-lg font-bold text-beige-50 mb-4">Kategori</h3>
                            <div class="flex items-center p-3 bg-gold-50/20 backdrop-blur-sm rounded-xl border border-gold-300">
                                <div class="w-10 h-10 bg-gold-100 text-gold-600 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-beige-50">{{ $course->category->name }}</h4>
                                    <p class="text-sm text-beige-300 font-medium">Jelajahi kajian lainnya</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('after-scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Get course data
    const courseData = {
        name: @json($course->name),
        url: window.location.href,
        description: @json($course->about),
        category: @json($course->category ? $course->category->name : '')
    };
    
    // Generate share text
    function getShareText() {
        return `🎓 ${courseData.name}\n\n${courseData.description}\n\n📚 Category: ${courseData.category}\n\n🔗 Enroll now:`;
    }
    
    // WhatsApp Share
    function shareToWhatsApp() {
        const text = encodeURIComponent(getShareText());
        const url = encodeURIComponent(courseData.url);
        const whatsappUrl = `https://wa.me/?text=${text}%20${url}`;
        window.open(whatsappUrl, '_blank', 'width=600,height=600');
    }
    
    // Telegram Share
    function shareToTelegram() {
        const text = encodeURIComponent(getShareText());
        const url = encodeURIComponent(courseData.url);
        const telegramUrl = `https://t.me/share/url?url=${url}&text=${text}`;
        window.open(telegramUrl, '_blank', 'width=600,height=600');
    }
    
    // Facebook Share
    function shareToFacebook() {
        const url = encodeURIComponent(courseData.url);
        const quote = encodeURIComponent(getShareText());
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${quote}`;
        window.open(facebookUrl, '_blank', 'width=600,height=600');
    }
    
    // X (Twitter) Share
    function shareToX() {
        const text = encodeURIComponent(`🎓 ${courseData.name}\n\n${courseData.description}\n\n#OnlineLearning #Course`);
        const url = encodeURIComponent(courseData.url);
        const twitterUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
        window.open(twitterUrl, '_blank', 'width=600,height=600');
    }
    
    // Threads Share
    function shareToThreads() {
        const text = encodeURIComponent(`🎓 ${courseData.name}\n\n${courseData.description}\n\n${courseData.url}`);
        const threadsUrl = `https://threads.net/intent/post?text=${text}`;
        window.open(threadsUrl, '_blank', 'width=600,height=600');
    }
    
    // Copy Link to Clipboard
    async function copyLink() {
        try {
            await navigator.clipboard.writeText(courseData.url);
            
            // Show success feedback
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = `
                <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Link Copied!
            `;
            
            button.classList.add('text-green-600');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('text-green-600');
            }, 2000);
            
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = courseData.url;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                alert('Link copied to clipboard!');
            } catch (err) {
                alert('Failed to copy link. Please copy manually: ' + courseData.url);
            }
            
            document.body.removeChild(textArea);
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('[x-data*="shareOpen"]');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target)) {
                // Force close Alpine.js dropdown
                const alpineData = Alpine.$data(dropdown);
                if (alpineData && alpineData.shareOpen) {
                    alpineData.shareOpen = false;
                }
            }
        });
    });
</script>
@endpush
