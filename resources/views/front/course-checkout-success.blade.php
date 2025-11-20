@extends('front.layouts.app')
@section('title', 'Purchase Successful - ' . $course->name)
@section('content')
    <x-navigation-auth />

    <main class="bg-gradient-to-b from-charcoal-900 to-charcoal-800 min-h-screen py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-charcoal-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-charcoal-700 overflow-hidden">
                <!-- Success Header -->
                <div class="bg-gradient-to-r from-gold-600 to-gold-500 px-8 py-12 text-center">
                    <div class="w-20 h-20 bg-charcoal-900 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-charcoal-900 mb-4">Purchase Successful!</h1>
                    <p class="text-charcoal-800 text-lg">You now have lifetime access to this course</p>
                </div>
                
                <!-- Course Info -->
                <div class="p-8">
                    <div class="flex items-center space-x-6 p-6 bg-charcoal-900/50 rounded-xl border border-charcoal-700">
                        <!-- Course Thumbnail -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-lg overflow-hidden bg-charcoal-900">
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
                                    <div class="w-full h-full flex items-center justify-center bg-charcoal-800">
                                        <span class="text-gold-400 font-bold text-lg">{{ substr($course->name, 0, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Course Details -->
                        <div class="flex-1 min-w-0">
                            <div class="space-y-2">
                                @if($course->category)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold-600/20 text-gold-300">
                                        {{ $course->category->name }}
                                    </span>
                                @endif
                                <h2 class="text-xl font-bold text-beige-50 truncate">{{ $course->name }}</h2>
                                <div class="flex items-center space-x-4 text-sm text-beige-300">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $totalLessons }} Lessons</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Lifetime Access</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- What's Next -->
                    <div class="mt-8 space-y-6">
     
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <a href="{{ route('dashboard.course.join', $course->slug) }}" 
                               class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 font-bold rounded-lg hover:bg-gold-500 transition-colors duration-200 shadow-lg hover:shadow-xl cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9 4h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Start Learning Now
                            </a>
 
                        </div>
                        
                        <!-- Additional Info -->
                        <div class="text-center mt-8 p-4 bg-gold-600/10 rounded-lg border border-gold-400">
                            <p class="text-sm text-beige-200">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <strong>Lifetime Access:</strong> This course is now permanently available in your account. You can access it anytime from any device.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
