@extends('layouts.app')
@section('title', 'My Courses - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))
@section('content')
    <x-navigation-auth />
    
    <!-- Main Content -->
    <main class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">            
            <!-- Course Catalog Section -->
            <section class="space-y-6">
                <div class="text-center space-y-4">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">My Learning Dashboard</h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Continue your learning journey with your purchased courses</p>
                </div>
                
                <!-- Course Content -->
                <div class="w-full bg-white rounded-lg shadow-md p-6">
                 @if($coursesByCategory->isEmpty() || $coursesByCategory->flatten()->isEmpty())
                        <div class="w-full text-center py-16">
                            <div class="w-20 h-20 bg-gradient-to-br from-mountain-meadow-100 to-mountain-meadow-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-mountain-meadow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">No Purchased Courses</h3>
                            <p class="text-gray-600 max-w-md mx-auto mb-8">You haven't purchased any courses yet. Explore our course catalog to start your learning journey.</p>
                            <div class="space-y-4">
                                <a href="{{ route('front.index') }}" class="inline-flex items-center px-6 py-3 bg-mountain-meadow-600 text-white font-semibold rounded-lg hover:bg-mountain-meadow-700 transition-colors duration-200 cursor-pointer">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Browse Courses
                                </a>
                                <div class="text-sm text-gray-500">
                                    <p>Start your learning journey by purchasing your first course!</p>
                                </div>
                            </div>
                        </div>
                    @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($coursesByCategory as $category => $courses)
                        @foreach($courses as $course)
                            <div class="w-full">
                                <x-course-card :course="$course" />
                            </div>
                        @endforeach
                    @endforeach
                    
                </div>
                </div>
            </section>
        </div>
    </main>
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Responsive grid layout for course cards */
    .course-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    @media (min-width: 768px) {
        .course-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (min-width: 1024px) {
        .course-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    </style>

@endsection
