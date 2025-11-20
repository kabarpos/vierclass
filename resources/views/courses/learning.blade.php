<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $currentContent->name }} - {{ $course->name }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])
    @stack('after-styles')
    
</head>
<body class="font-manrope antialiased">

<div x-data="{ 
    sidebarOpen: false,
    currentProgress: {{ $progressPercentage ?? 0 }},
    totalLessons: {{ $totalLessons }},
    completedLessons: {{ $completedLessons ?? 0 }},
    isLessonCompleted: {{ $isCurrentCompleted ? 'true' : 'false' }},
    isLoading: false,
    openSections: {
        @foreach($course->courseSections as $section)
        'section_{{ $section->id }}': {{ $section->id == ($currentSection->id ?? 0) ? 'true' : 'false' }},
        @endforeach
    },
    
    // Mark lesson as complete using database API
    async markLessonComplete() {
        if (this.isLessonCompleted || this.isLoading) return;
        
        this.isLoading = true;
        
        try {
            const response = await fetch('/api/lesson-progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    course_id: {{ $course->id }},
                    section_content_id: {{ $currentContent->id }},
                    time_spent: Math.floor(Math.random() * 600) + 300 // Random 5-15 minutes
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.status === 'success') {
                this.isLessonCompleted = true;
                this.completedLessons = data.data.course_progress.completed;
                this.currentProgress = data.data.course_progress.percentage;
                
                // Show success notification
                this.showNotification('✅ Lesson completed! Great progress!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to mark lesson as complete', 'error');
            }
        } catch (error) {
            console.error('Error marking lesson complete:', error);
            this.showNotification('Network error. Please try again.', 'error');
        } finally {
            this.isLoading = false;
        }
    },
    
    // Simple notification system
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}" 
class="learning-page bg-gradient-to-b from-charcoal-900 to-charcoal-800 min-h-screen">
    
    <!-- Modern Sidebar -->
    <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" 
           class="fixed inset-y-0 left-0 z-50 flex flex-col bg-charcoal-900 w-80 lg:w-96 h-screen border-r border-charcoal-800 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
        
        <!-- Back to Dashboard Button (Above Sidebar) -->
        <div class="flex-shrink-0 px-6 py-4 border-b border-charcoal-800">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-beige-300 hover:text-gold-400 transition-colors text-sm font-medium cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
        
        <!-- Mobile Close Button -->
        <div class="lg:hidden flex items-center justify-end px-4 py-3 border-b border-charcoal-800">
            <button @click="sidebarOpen = false" class="p-2 rounded-lg text-beige-400 hover:text-gold-400 hover:bg-charcoal-800 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Lesson Navigation -->
        <div class="flex-1 overflow-y-auto">
            <!-- Course Progress Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-gold-600 to-gold-500 text-charcoal-900">
                <div class="mb-3">
                    <p class="text-charcoal-800 text-sm mt-1 font-medium">Course Progress</p>
                </div>
                
                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2 text-sm">
                        <span x-text="`${completedLessons} of ${totalLessons} lessons`"></span>
                        <span x-text="`${Math.round(currentProgress)}%`"></span>
                    </div>
                    <div class="w-full bg-charcoal-900/50 rounded-full h-2">
                        <div class="bg-charcoal-900 h-2 rounded-full transition-all duration-500" 
                             :style="`width: ${currentProgress}%`"></div>
                    </div>
                </div>
                
                <!-- Progress Stats -->
                <div class="flex items-center justify-between text-xs text-charcoal-800 font-medium">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        <span x-text="completedLessons + ' completed'"></span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span x-text="(totalLessons - completedLessons) + ' remaining'"></span>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4">
                @foreach($course->courseSections as $sectionIndex => $section)
                <!-- Divider per section (brand color, higher contrast) -->
                <div class="pt-2 mb-2 last:mb-0 border-t border-charcoal-800 first:border-t-0">
                    <!-- Section Header -->
                    <button type="button" 
                            @click="openSections['section_{{ $section->id }}'] = !openSections['section_{{ $section->id }}']"
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-charcoal-800 transition-colors group cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-gold-600/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-gold-400 font-semibold text-sm">{{ $sectionIndex + 1 }}</span>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-beige-50 text-base group-hover:text-gold-400 transition-colors">{{ $section->name }}</h3>
                                <p class="text-xs text-beige-400 mt-0.5">{{ $sectionContentCounts[$section->id] ?? 0 }} lessons</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-beige-400 group-hover:text-gold-400 transition-all duration-200 transform" 
                             :class="openSections['section_{{ $section->id }}'] ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Section Content -->
                    <div x-show="openSections['section_{{ $section->id }}']" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="mt-3 ml-11 space-y-2">
                        @foreach($section->sectionContents as $contentIndex => $content)
                        @php
                            $isActive = $currentSection && $section->id == $currentSection->id && $currentContent->id == $content->id;
                            $lessonNumber = $contentIndex + 1;
                            $isCompleted = isset($userProgress[$content->id]) && $userProgress[$content->id]->is_completed;
                        @endphp
                        <a href="{{ route('dashboard.course.learning', [
                                'course' => $course->slug,
                                'courseSection' => $section->id,
                                'sectionContent' => $content->id,
                            ]) }}"
                           @click="sidebarOpen = false" 
                           class="group block cursor-pointer">
                            <div class="flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 {{ $isActive ? 'bg-gold-600/20 border border-gold-400' : 'hover:bg-charcoal-800 border border-transparent hover:border-charcoal-700' }}">
                                <!-- Lesson Status Icon -->
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 
                                    @if($isCompleted) 
                                        bg-gold-600 text-charcoal-900
                                    @elseif($isActive) 
                                        bg-gold-600 text-charcoal-900
                                    @else 
                                        bg-charcoal-800 text-beige-400 group-hover:bg-gold-600/20 group-hover:text-gold-400
                                    @endif">
                                    @if($isCompleted)
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                        </svg>
                                    @elseif($isActive)
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    @else
                                        <span class="text-xs font-semibold">{{ $lessonNumber }}</span>
                                    @endif
                                </div>
                                
                                <!-- Lesson Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-sm {{ $isActive ? 'text-gold-400' : 'text-beige-50 group-hover:text-gold-400' }} line-clamp-2 leading-tight">
                                        {{ $content->name }}
                                        @if($isCompleted)
                                            <span class="ml-2 text-gold-400 text-xs">✓</span>
                                        @endif
                                    </h4>
                                    <div class="flex items-center space-x-4 mt-1 text-xs {{ $isActive ? 'text-gold-400' : 'text-beige-400' }}">

                                        @if($isCompleted)
                                        <span class="flex items-center text-gold-400">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                            Completed
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Active Indicator -->
                                @if($isActive)
                                <div class="w-2 h-8 bg-gold-600 rounded-full flex-shrink-0"></div>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </aside>
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         class="mobile-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden cursor-pointer"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
    
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen bg-charcoal-900 lg:ml-96">
        <!-- Top Navigation Bar -->
        <header class="flex-shrink-0 bg-charcoal-900 border-b border-charcoal-800 px-4 lg:px-8 py-4">
            <div class="main-content-wrapper">
                <div class="content-inner">
                    <div class="flex items-center justify-between">
                        <!-- Mobile Menu Button (Hidden on Desktop) -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-beige-400 hover:text-gold-400 hover:bg-charcoal-800 transition-colors cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        
                        <!-- Breadcrumb Navigation -->
                        <nav class="hidden lg:flex items-center space-x-2 text-sm">
                            <a href="{{ route('dashboard') }}" class="text-beige-400 hover:text-gold-400 transition-colors cursor-pointer">Dashboard</a>
                            <svg class="w-4 h-4 text-beige-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <a href="{{ route('dashboard') }}" class="text-beige-400 hover:text-gold-400 transition-colors truncate max-w-xs cursor-pointer">{{ $course->name }}</a>
                            <svg class="w-4 h-4 text-beige-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-beige-50 font-medium truncate max-w-xs">{{ $currentContent->name }}</span>
                        </nav>
                        
                        <!-- Lesson Navigation Controls -->
                        <div class="flex items-center space-x-3">
                            <!-- Progress Info -->
                            <div class="hidden sm:flex items-center space-x-2 text-sm text-beige-300">
                                <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span x-text="`Lesson ${completedLessons + 1} of ${totalLessons}`"></span>
                            </div>
                            
                            <!-- Quick Navigation -->
                            <div class="flex items-center space-x-1">
                                @if(isset($prevContent))
                                <a href="{{ route('dashboard.course.learning', [
                                        'course' => $course->slug,
                                        'courseSection' => $prevContent->course_section_id,
                                        'sectionContent' => $prevContent->id,
                                    ]) }}" 
                                   class="p-2 rounded-lg text-beige-400 hover:text-gold-400 hover:bg-charcoal-800 transition-colors cursor-pointer" 
                                   title="Previous Lesson">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </a>
                                @endif
                                
                                @if(isset($nextContent))
                                <a href="{{ route('dashboard.course.learning', [
                                        'course' => $course->slug,
                                        'courseSection' => $nextContent->course_section_id,
                                        'sectionContent' => $nextContent->id,
                                    ]) }}" 
                                   class="p-2 rounded-lg text-beige-400 hover:text-gold-400 hover:bg-charcoal-800 transition-colors cursor-pointer" 
                                   title="Next Lesson">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content Area -->
        <main class="flex-1 bg-charcoal-900">
            <!-- Lesson Content -->
            <div class="flex-1 bg-charcoal-900">
                <article class="max-w-4xl mx-auto">
                    <div class="px-6 sm:px-8 lg:px-10 py-8 lg:py-12">
                        <!-- Lesson Header -->
                        <header class="mb-8">
                            <h1 class="text-2xl lg:text-3xl font-bold text-beige-50 mb-4 leading-tight">
                                {{ $currentContent->name }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-beige-300 mb-6">
                                <span class="inline-flex items-center px-3 py-1 bg-charcoal-800 text-beige-200 rounded-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ $currentSection->name ?? 'Section' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 bg-gold-600/20 text-gold-400 rounded-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Premium Lesson
                                </span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-charcoal-800 rounded-full h-2 mb-8">
                                <div class="bg-gradient-to-r from-gold-600 to-gold-500 h-2 rounded-full transition-all duration-500" 
                                     :style="`width: ${currentProgress}%`"></div>
                            </div>
                        </header>
                        
                        <!-- YouTube Player (if available) -->
                        @if($currentContent->youtube_url && $currentContent->getYoutubeVideoId())
                            <div class="mb-8">
                                <x-youtube-player 
                                    :videoId="$currentContent->getYoutubeVideoId()" 
                                    :title="$currentContent->name" 
                                />
                            </div>
                        @endif

                        <!-- Lesson Content -->
                        <div class="filament-rich-content prose prose-lg max-w-none content-typography mb-12 tiptap-content">
                            @php
                                // Debug: Check content rendering
                                $renderedContent = \Filament\Forms\Components\RichEditor\RichContentRenderer::make($currentContent->content ?? '')->toHtml();
                                // Optional debug output (remove in production)
                                // Log::info('Learning Content Debug', [
                                //     'raw_content' => $currentContent->content ?? 'NULL',
                                //     'rendered_length' => strlen($renderedContent),
                                //     'rendered_content' => substr($renderedContent, 0, 500)
                                // ]);
                            @endphp
                            {!! $renderedContent !!}
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-stretch gap-4 mb-8">
                            <!-- Mark Complete Button -->
                            <button 
                                @click="markLessonComplete()" 
                                :disabled="isLessonCompleted || isLoading"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-lg text-sm font-medium transition-all duration-200 min-w-[180px]"
                                :class="isLessonCompleted ? 
                                    'bg-gold-600/20 text-gold-400 border border-gold-400 cursor-not-allowed' : 
                                    isLoading ? 'bg-charcoal-800 text-beige-400 border border-charcoal-700 cursor-not-allowed' :
                                    'border-2 border-gold-400 text-gold-400 bg-charcoal-800/50 hover:bg-gold-600/20 hover:border-gold-300'"
                            >
                                <!-- Loading Spinner -->
                                <div x-show="isLoading" class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-charcoal-700 border-t-green-600"></div>
                                
                                <!-- Checkmark Icon -->
                                <svg x-show="!isLoading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                
                                <!-- Button Text -->
                                <span x-text="isLoading ? 'Saving...' : (isLessonCompleted ? 'Completed ✅' : 'Mark as Complete')"></span>
                            </button>
                            
                            <!-- Continue Learning Button -->
                            @if (!$isFinished && isset($nextContent))
                            <a href="{{ route('dashboard.course.learning', [
                                        'course' => $course->slug,
                                        'courseSection' => $nextContent->course_section_id,
                                        'sectionContent' => $nextContent->id,
                                    ]) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 text-sm font-bold rounded-lg hover:bg-gold-500 transition-all duration-200 cursor-pointer shadow-lg hover:shadow-xl">
                                <span>Continue Learning</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                            @elseif ($isFinished)
                            <a href="{{ route('dashboard.course.learning.finished', $course->slug) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gold-600 text-charcoal-900 text-sm font-bold rounded-lg hover:bg-gold-500 transition-all duration-200 cursor-pointer shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Complete Course</span>
                            </a>
                            @endif
                        </div>
                        
                        <!-- Lesson Navigation -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-8 border-t border-charcoal-700">
                            <!-- Previous Lesson -->
                            <div class="flex-1">
                                @if(isset($prevContent))
                                <a href="{{ route('dashboard.course.learning', [
                                        'course' => $course->slug,
                                        'courseSection' => $prevContent->course_section_id,
                                        'sectionContent' => $prevContent->id,
                                    ]) }}" 
                                   class="group inline-flex items-center text-sm font-medium text-beige-300 hover:text-gold-400 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    <div class="text-left">
                                        <div class="text-xs text-beige-400">Previous</div>
                                        <div class="font-semibold line-clamp-1">{{ $prevContent->name ?? 'Previous Lesson' }}</div>
                                    </div>
                                </a>
                                @endif
                            </div>
                            
                            <!-- Next Lesson -->
                            <div class="flex-1 text-right">
                                @if(isset($nextContent))
                                <a href="{{ route('dashboard.course.learning', [
                                        'course' => $course->slug,
                                        'courseSection' => $nextContent->course_section_id,
                                        'sectionContent' => $nextContent->id,
                                    ]) }}" 
                                   class="group inline-flex items-center text-sm font-medium text-beige-300 hover:text-gold-400 transition-colors cursor-pointer">
                                    <div class="text-right mr-2">
                                        <div class="text-xs text-beige-400">Next</div>
                                        <div class="font-semibold line-clamp-1">{{ $nextContent->name ?? 'Next Lesson' }}</div>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </main>
    </div>
    
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         class="mobile-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden cursor-pointer"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
</div>


    @stack('scripts')
    @stack('after-scripts')
</body>
</html>

@push('after-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <link rel="stylesheet" href="{{ asset('css/content.css') }}">
    {{-- Styles moved to resources/css/custom.css (.learning-page)
        /* Unified Layout - Clean Design */
        /* font follows body default (font-manrope), no inline overrides */
        
        /* Clean content flow - no visual separations */
        .content-card {
            background: transparent;
            border: none;
            box-shadow: none;
        }
        
        /* Fixed Sidebar Positioning */
        aside {
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 50;
        }
        
        @media (max-width: 1023px) {
            /* Mobile: Hidden by default, shows when toggled */
            aside {
                transform: translateX(-100%);
            }
            
            aside.translate-x-0 {
                transform: translateX(0) !important;
            }
        }
        
        @media (min-width: 1024px) {
            /* Desktop: Always visible and fixed */
            aside {
                transform: translateX(0) !important;
            }
        }
        
        /* TipTap Content Wrapper */
        .tiptap-content {
            line-height: 1.75;
        }
        
        /* Video wrapper for responsive YouTube embeds */
        .video-wrapper {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            margin: 2rem 0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 0.75rem;
        }
        
        /* Enhanced content processing */
        .filament-rich-content iframe {
            max-width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Ensure proper rendering hierarchy */
        .filament-rich-content.prose.prose-lg {
            max-width: none !important;
        }
        
        /* Force content visibility */
        .filament-rich-content * {
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Debug styling (remove in production) */
        .filament-rich-content[data-debug="true"] {
            border: 2px dashed #ef4444;
            padding: 1rem;
            background: rgba(239, 68, 68, 0.05);
        }
        
        .filament-rich-content[data-debug="true"]:before {
            content: "DEBUG: Content Container";
            display: block;
            color: #ef4444;
            font-size: 0.75rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        /* Filament Rich Content Specific Styling */
        .filament-rich-content {
        }
        
        /* Enhanced blockquote styling for TipTap output */
        .filament-rich-content blockquote {
            border-left: 4px solid #0f4c7a;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 0.75rem;
            font-style: italic;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            color: #374151;
            font-size: 1.125rem;
            line-height: 1.75;
        }
        
        .filament-rich-content blockquote::before {
            content: '"';
            position: absolute;
            top: -0.5rem;
            left: 1rem;
            font-size: 4rem;
            color: #0f4c7a;
            opacity: 0.3;
            font-family: Georgia, serif;
        }
        
        .filament-rich-content blockquote p {
            margin: 0;
            padding: 0;
        }
        
        /* Enhanced paragraph styling */
        .filament-rich-content p {
            margin-bottom: 1.25rem;
            color: #374151;
            line-height: 1.75;
            text-align: justify;
        }
        
        /* Enhanced list styling - Override global reset */
        .filament-rich-content ul, 
        .filament-rich-content ol {
            margin: 1.5rem 0 !important;
            padding-left: 2rem !important;
            list-style: revert !important; /* Force list styles to show */
        }
        
        .filament-rich-content ul {
            list-style-type: disc !important;
        }
        
        .filament-rich-content ol {
            list-style-type: decimal !important;
        }
        
        .filament-rich-content li {
            margin-bottom: 0.75rem !important;
            line-height: 1.75 !important;
            display: list-item !important; /* Ensure list item display */
            list-style: inherit !important; /* Inherit parent list style */
        }
        
        .filament-rich-content li::marker {
            color: #0f4c7a !important;
            font-weight: 600 !important;
        }
        
        /* Nested lists with stronger specificity */
        .filament-rich-content ul ul {
            list-style-type: circle !important;
            margin: 0.5rem 0 !important;
        }
        
        .filament-rich-content ul ul ul {
            list-style-type: square !important;
        }
        
        .filament-rich-content ol ol {
            list-style-type: lower-alpha !important;
            margin: 0.5rem 0 !important;
        }
        
        .filament-rich-content ol ol ol {
            list-style-type: lower-roman !important;
        }
        
        /* Task list styling */
        .filament-rich-content ul[data-type="taskList"] {
            list-style: none !important;
            padding-left: 0 !important;
        }
        
        .filament-rich-content ul[data-type="taskList"] li {
            display: flex !important;
            align-items: flex-start !important;
            gap: 0.5rem !important;
        }
        
        .filament-rich-content ul[data-type="taskList"] li input[type="checkbox"] {
            margin-top: 0.125rem !important;
            flex-shrink: 0 !important;
        }
        
        /* Enhanced heading styling */
        .filament-rich-content h1,
        .filament-rich-content h2,
        .filament-rich-content h3 {
            font-weight: 700;
            color: #1f2937;
            letter-spacing: -0.025em;
        }
        
        .filament-rich-content h1 {
            font-size: 2.25rem;
            line-height: 1.2;
            margin: 2rem 0 1rem;
            background: linear-gradient(135deg, #0f4c7a 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .filament-rich-content h2 {
            font-size: 1.875rem;
            line-height: 1.3;
            margin: 1.75rem 0 1rem;
            color: #0f4c7a;
        }
        
        .filament-rich-content h3 {
            font-size: 1.5rem;
            line-height: 1.4;
            margin: 1.5rem 0 0.75rem;
            color: #1e40af;
        }
        
        /* Enhanced link styling */
        .filament-rich-content a {
            color: #0f4c7a;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: all 0.2s ease;
        }
        
        .filament-rich-content a:hover {
            color: #0c3d61;
            border-bottom-color: #0f4c7a;
        }
        
        /* Enhanced code styling */
        .filament-rich-content code {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #dc2626;
            font-weight: 500;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        
        .filament-rich-content pre {
            background: #1e293b;
            color: #f1f5f9;
            padding: 1.5rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin: 2rem 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #334155;
        }
        
        .filament-rich-content pre code {
            background: transparent;
            border: none;
            color: inherit;
            padding: 0;
        }
        
        /* Typography - matching Free Preview */
        .content-typography {
            line-height: 1.75;
            font-size: 1.125rem;
        }
        
        .content-typography h1,
        .content-typography h2,
        .content-typography h3 {
            font-weight: 700;
            color: #1f2937;
        }
        
        .content-typography p {
            margin-bottom: 1.25rem;
            color: #374151;
        }
        
        .content-typography ul,
        .content-typography ol {
            margin: 1.25rem 0;
            padding-left: 1.5rem;
        }
        
        .content-typography code {
            background-color: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .content-typography pre {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        
        @media (max-width: 640px) {
            .content-typography {
                font-size: 1rem;
                line-height: 1.625;
            }
        }
        
        /* Sidebar Scrollbar Styling */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb #f9fafb;
        }
        
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #f9fafb;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 3px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
        
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            color: #1f2937;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
        .prose h1 {
            font-size: 2.25rem;
            line-height: 1.2;
            margin: 2rem 0 1rem;
            background: linear-gradient(135deg, #0f4c7a 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .prose h2 {
            font-size: 1.875rem;
            line-height: 1.3;
            margin: 1.75rem 0 1rem;
            color: #0f4c7a;
        }
        
        .prose h3 {
            font-size: 1.5rem;
            line-height: 1.4;
            margin: 1.5rem 0 0.75rem;
            color: #1e40af;
        }
        
        .prose p {
            margin-bottom: 1.25rem;
            text-align: justify;
        }
        
        .prose a {
            color: #0f4c7a;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: all 0.2s ease;
        }
        
        .prose a:hover {
            color: #0c3d61;
            border-bottom-color: #0f4c7a;
        }
        
        .prose ul, .prose ol {
            margin: 1.5rem 0;
            padding-left: 2rem;
        }
        
        .prose li {
            margin-bottom: 0.75rem;
            line-height: 1.75;
        }
        
        .prose li::marker {
            color: #0f4c7a;
            font-weight: 600;
        }
        
        .prose blockquote {
            border-left: 4px solid #0f4c7a;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 0.75rem;
            font-style: italic;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .prose blockquote::before {
            content: '\201C';
            position: absolute;
            top: -0.5rem;
            left: 1rem;
            font-size: 4rem;
            color: #0f4c7a;
            opacity: 0.3;
            font-family: Georgia, serif;
        }
        
        .prose code {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #dc2626;
            font-weight: 500;
        }
        
        .prose pre {
            background: #1e293b;
            color: #f1f5f9;
            padding: 1.5rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin: 2rem 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #334155;
        }
        
        .prose pre code {
            background: transparent;
            border: none;
            color: inherit;
            padding: 0;
        }
        
        .prose img {
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            transition: transform 0.3s ease;
        }
        
        .prose img:hover {
            transform: scale(1.02);
        }
        
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .prose th, .prose td {
            border: 1px solid #e5e7eb;
            padding: 1rem;
            text-align: left;
        }
        
        .prose th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-weight: 700;
            color: #374151;
        }
        
        .prose tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .prose tbody tr:hover {
            background-color: #f0f9ff;
        }
        
        /* Content wrapper styling */
        .content-wrapper {
            background: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            .prose {
                font-size: 1rem;
                line-height: 1.625;
            }
            
            .prose h1 {
                font-size: 1.75rem;
            }
            
            .prose h2 {
                font-size: 1.5rem;
            }
            
            .prose h3 {
                font-size: 1.25rem;
            }
            
            .prose blockquote {
                padding: 1rem;
                margin: 1.5rem 0;
            }
            
            .prose pre {
                padding: 1rem;
                margin: 1.5rem 0;
            }
        }
        
        /* Animation utilities */
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Focus states for accessibility */
        .focus\:ring-mountain-meadow:focus {
            --tw-ring-color: rgba(15, 76, 122, 0.5);
        }
        
        /* Custom shadows */
        .shadow-3xl {
            box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Line clamp utilities */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }
        
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        
        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
        
        /* Custom gradient backgrounds */
        .bg-gradient-mountain-meadow {
            background: linear-gradient(135deg, #0f4c7a 0%, #1d4ed8 100%);
        }
        
        /* Enhanced button styles */
        .btn-primary {
            background: linear-gradient(135deg, #0f4c7a 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(15, 76, 122, 0.1);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(15, 76, 122, 0.2);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
    --}}
@endpush

@push('after-scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', () => {
    
    // Debug: Check if content exists
    const contentContainer = document.querySelector('.filament-rich-content');
    if (contentContainer) {
        console.log('🎓 Learning content container found');
        console.log('Content HTML length:', contentContainer.innerHTML.length);
        
        // Log first 200 characters for debugging
        console.log('Content preview:', contentContainer.innerHTML.substring(0, 200));
    } else {
        console.error('❌ Learning content container not found');
    }
    
    // Enhanced TipTap content processing
    function processTipTapContent() {
        const contentArea = document.querySelector('.filament-rich-content');
        if (!contentArea) return;
        
        // Process YouTube embeds
        const youtubePattern = /https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/g;
        const youtubeShortPattern = /https:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/g;
        
        let content = contentArea.innerHTML;
        
        // Ganti YouTube URLs dengan struktur Plyr agar konsisten dengan komponen
        const buildPlyrEmbed = (videoId) => `
            <div class="video-wrapper my-6">
              <div class="live-video-player aspect-video cursor-pointer">
                <div class="plyr__video-embed">
                  <iframe
                    class="youtube-component-iframe"
                    src="https://www.youtube-nocookie.com/embed/${videoId}?iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1&controls=0&disablekb=1&fs=0&origin=${location.origin}"
                    title="YouTube video player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    frameborder="0"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
            </div>`;

        content = content.replace(youtubePattern, (match, videoId) => buildPlyrEmbed(videoId));
        content = content.replace(youtubeShortPattern, (match, videoId) => buildPlyrEmbed(videoId));
        
        contentArea.innerHTML = content;
        
        // Inisialisasi Plyr untuk embed yang baru dibuat
        if (window.Plyr) {
            document.querySelectorAll('.live-video-player .plyr__video-embed').forEach((el) => {
                const player = new Plyr(el, {
                    ratio: '16:9',
                    autoplay: false,
                    clickToPlay: true,
                    controls: [
                        'play-large','play','progress','current-time','duration','mute','volume','settings','fullscreen'
                    ],
                    youtube: { rel: 0, modestbranding: 1, iv_load_policy: 3 }
                });
                const wrapper = el.closest('.live-video-player');
                const setPausedUI = (paused) => {
                    if (!wrapper) return;
                    wrapper.classList.toggle('paused', !!paused);
                    const iframeEl = wrapper.querySelector('iframe');
                    if (iframeEl) iframeEl.style.pointerEvents = paused ? 'none' : 'auto';
                };

                // Paksa kualitas YouTube 1080p (fallback 720p) ketika siap/bermain
                const setPreferredQuality = (p, quality = 'hd1080') => {
                    try {
                        const embed = p && p.embed ? p.embed : null; // Instance YT.Player
                        if (embed && typeof embed.getAvailableQualityLevels === 'function') {
                            const levels = embed.getAvailableQualityLevels();
                            if (Array.isArray(levels) && levels.length) {
                                const selected = levels.includes(quality)
                                    ? quality
                                    : (levels.includes('hd720') ? 'hd720' : null);
                                if (selected && typeof embed.setPlaybackQuality === 'function') {
                                    embed.setPlaybackQuality(selected);
                                    if (typeof embed.setPlaybackQualityRange === 'function') {
                                        embed.setPlaybackQualityRange(selected);
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('Gagal set kualitas YouTube (TipTap):', e);
                    }
                };
                player.on('ready', () => setPausedUI(true));
                player.on('pause', () => setPausedUI(true));
                player.on('play', () => setPausedUI(false));
                // Pasang handler kualitas setelah event bind untuk reliabilitas
                player.on('ready', () => setPreferredQuality(player, 'hd1080'));
                player.on('play', () => setPreferredQuality(player, 'hd1080'));
                player.on('ended', () => setPausedUI(true));
            });
        }

        console.log('✅ TipTap content processing completed & Plyr initialized');
    }
    
    // Process content after DOM is ready
    processTipTapContent();
    
    // Enhanced code highlighting with multiple languages
    document.querySelectorAll('pre').forEach(pre => {
        if (!pre.querySelector('code')) {
            const code = document.createElement('code');
            code.textContent = pre.textContent.trim();
            pre.innerHTML = '';
            pre.appendChild(code);
        }
    });
    hljs.highlightAll();
    
    // Auto-close mobile sidebar when clicking on a lesson
    function handleMobileSidebarClose() {
        document.querySelectorAll('aside a[href*="/learning/"]').forEach(link => {
            link.addEventListener('click', () => {
                const alpineData = document.querySelector('[x-data]').__x?.$data;
                if (alpineData && window.innerWidth < 1024) {
                    alpineData.sidebarOpen = false;
                }
            });
        });
    }
    
    handleMobileSidebarClose();
    
    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Reading progress tracking
    function trackReadingProgress() {
        const article = document.querySelector('article');
        const progressBar = document.querySelector('[\\:style*="currentProgress"]');
        
        if (!article || !progressBar) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const scrolled = window.scrollY;
                    const articleHeight = article.offsetHeight;
                    const windowHeight = window.innerHeight;
                    const totalScroll = articleHeight - windowHeight;
                    
                    if (totalScroll > 0) {
                        const progress = Math.min((scrolled / totalScroll) * 100, 100);
                        // Update Alpine.js data if available
                        const alpineData = document.querySelector('[x-data]').__x?.$data;
                        if (alpineData) {
                            alpineData.currentProgress = Math.max(progress, alpineData.currentProgress);
                        }
                    }
                }
            });
        });
        
        observer.observe(article);
    }
    
    trackReadingProgress();
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        // Navigate with arrow keys (if no input is focused)
        if (document.activeElement.tagName !== 'INPUT' && 
            document.activeElement.tagName !== 'TEXTAREA') {
            
            if (e.key === 'ArrowLeft' && e.altKey) {
                // Previous lesson
                const prevLink = document.querySelector('a[title="Previous Lesson"]');
                if (prevLink) {
                    e.preventDefault();
                    prevLink.click();
                }
            } else if (e.key === 'ArrowRight' && e.altKey) {
                // Next lesson
                const nextLink = document.querySelector('a[title="Next Lesson"]');
                if (nextLink) {
                    e.preventDefault();
                    nextLink.click();
                }
            } else if (e.key === 's' && e.altKey) {
                // Toggle sidebar
                e.preventDefault();
                const alpineData = document.querySelector('[x-data]').__x?.$data;
                if (alpineData) {
                    alpineData.sidebarOpen = !alpineData.sidebarOpen;
                }
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        const alpineData = document.querySelector('[x-data]').__x?.$data;
        if (alpineData && window.innerWidth < 1024) {
            alpineData.sidebarOpen = false;
        }
    });
    
    // Add loading states to navigation buttons
    document.querySelectorAll('a[href*="/learning/"]').forEach(link => {
        link.addEventListener('click', () => {
            const loader = document.createElement('div');
            loader.className = 'inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2';
            
            const text = link.querySelector('span');
            if (text) {
                text.prepend(loader);
            }
        });
    });
    
    // Add copy code functionality
    document.querySelectorAll('pre').forEach(pre => {
        const button = document.createElement('button');
        button.className = 'absolute top-2 right-2 px-3 py-1 text-xs bg-gray-700 hover:bg-gray-600 text-white rounded transition-colors opacity-0 group-hover:opacity-100';
        button.textContent = 'Copy';
        
        pre.classList.add('group', 'relative');
        pre.appendChild(button);
        
        button.addEventListener('click', () => {
            const code = pre.querySelector('code')?.textContent || pre.textContent;
            navigator.clipboard.writeText(code).then(() => {
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = 'Copy';
                }, 2000);
            });
        });
    });
    
    // Add entrance animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-in');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.prose > *').forEach(el => {
        observer.observe(el);
    });
    
    console.log('🎓 Course Learning UI initialized successfully!');
});
</script>
@endpush
