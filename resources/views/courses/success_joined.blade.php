@extends('layouts.app')
@section('title', 'Success Joined - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))
@section('content')
<x-nav-guest />

<div class="min-h-screen bg-gradient-to-b from-charcoal-900 to-charcoal-800 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <main class="relative w-full max-w-2xl">
        <!-- Success Icon -->
        <div class="text-center mb-8">

            
            <!-- Success Message -->
            <div class="space-y-4 mb-8">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-beige-50 leading-tight">
                    Welcome to Class,<br class="hidden sm:inline">Upgrade Your New Skills
                </h1>
                <p class="text-base sm:text-lg text-beige-300 leading-relaxed max-w-lg mx-auto">
                    Kelas ini bersifat premium, dilarang keras menyebarluaskan materi kelas ini dengan cara dan bentuk apapun.
                </p>
            </div>
        </div>
        
        <!-- Course Card -->
        <div class="bg-charcoal-800/80 backdrop-blur-sm rounded-xl shadow-lg border border-charcoal-700 p-4 sm:p-6 mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                <!-- Course Thumbnail -->
                <div class="flex-shrink-0 w-full sm:w-40 lg:w-48">
                    <div class="aspect-video sm:aspect-[4/3] rounded-lg overflow-hidden bg-charcoal-900">
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
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gold-600 to-gold-500">
                                <span class="text-charcoal-900 font-bold text-lg sm:text-xl">{{ substr($course->name, 0, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Course Details -->
                <div class="flex-1 space-y-3 sm:space-y-4 min-w-0">
                    <h2 class="text-lg sm:text-xl font-bold text-beige-50 leading-tight">
                        {{ $course->name }}
                    </h2>
                    
                    <div class="space-y-2">
                        <!-- Category -->
                        <div class="flex items-center gap-2">
                            <div class="flex-shrink-0 w-5 h-5 bg-gold-600/20 rounded-md flex items-center justify-center">
                                <svg class="w-3 h-3 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-beige-300 font-medium">
                                {{ $course->category->name }}
                            </p>
                        </div>
                        
                        <!-- Lessons Count -->
                        <div class="flex items-center gap-2">
                            <div class="flex-shrink-0 w-5 h-5 bg-gold-600/20 rounded-md flex items-center justify-center">
                                <svg class="w-3 h-3 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-beige-300 font-medium">
                                {{ $totalLessons }} Lessons
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Get Guidelines Button -->
            <a href="{{ route('front.terms-of-service') }}" class="w-full px-6 py-3 border-2 border-charcoal-700 text-beige-200 font-semibold rounded-lg hover:border-gold-400 hover:bg-charcoal-700 transition-all duration-200 text-center cursor-pointer">
                Term Of Services
            </a>
            
            <!-- Start Learning Button -->
            <a href="{{ route('dashboard.course.learning', [
                    'course' => $course->slug,
                    'courseSection' => $firstSectionId,
                    'sectionContent' => $firstContentId,
                ]) }}"
               class="w-full px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-lg hover:bg-gold-500 transition-all duration-200 text-center cursor-pointer shadow-lg hover:shadow-xl">
                Start Learning
            </a>
        </div>
        
        <!-- Additional Info -->
        <div class="text-center mt-8">
            <p class="text-sm text-beige-400">
                Ready to begin your learning journey? Click "Start Learning" to access your first lesson.
            </p>
        </div>
    </main>
</div>
@endsection
