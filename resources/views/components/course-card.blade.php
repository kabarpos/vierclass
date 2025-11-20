<a href="{{ route('front.course.details', $course->slug) }}" class="group block transition-all duration-300 hover:-translate-y-2 cursor-pointer">
    <div class="course-card flex flex-col rounded-2xl border border-charcoal-700 hover:border-gold-400 hover:shadow-[0_16px_48px_rgba(218,165,32,0.3)] transition-all duration-300 bg-charcoal-800/80 backdrop-blur-sm overflow-hidden">
        <div class="thumbnail-container p-4">
            <div class="relative w-full aspect-video rounded-lg overflow-hidden bg-charcoal-900">
                @if($course->thumbnail)
                    @if(str_starts_with($course->thumbnail, 'http'))
                        <x-lazy-image 
                            src="{{ $course->thumbnail }}" 
                            alt="{{ $course->name }}" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy" />
                    @else
                        @php
                            $thumbRel = ltrim($course->thumbnail, '/');
                            $src = \Illuminate\Support\Facades\Storage::disk('public')->exists($thumbRel)
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($thumbRel)
                                : asset($thumbRel);
                        @endphp
                        <x-lazy-image 
                            src="{{ $src }}" 
                            alt="{{ $course->name }}" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy" />
                    @endif
                @else
                    <div class="w-full h-full flex items-center justify-center bg-charcoal-900">
                        <x-lazy-image 
                            src="{{ asset('assets/images/thumbnails/thumbnail-1.png') }}" 
                            alt="{{ $course->name }}" 
                            class="w-full h-full object-cover"
                            loading="lazy" />
                    </div>
                @endif
                </div>
        </div>
        <div class="flex flex-col p-6 pt-2 space-y-4">
            <h3 class="font-bold text-lg line-clamp-2 text-beige-50 group-hover:text-gold-400 transition-colors duration-300">{{ $course->name }}</h3>
            
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <div class="p-1.5 bg-gold-100 rounded-md">
                        <svg class="w-3 h-3 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-beige-300">{{ $course->category->name }}</span>
                </div>
                


            </div>
            
            <!-- Call to Action -->
            <div class="pt-2">
                <div class="flex items-center justify-between">
                    @if($course->price > 0)
                        <!-- Course Price -->
                        <div class="text-left">
                            @if($course->original_price && $course->original_price > $course->price)
                                <!-- Original Price (Strikethrough) -->
                                <div class="text-sm text-beige-400 line-through">
                                    Rp {{ number_format($course->original_price, 0, '', '.') }}
                                </div>
                                <!-- Current Price with Discount Badge -->
                                <div class="flex items-center space-x-2">
                                    <div class="text-lg font-bold text-gold-400">
                                        Rp {{ number_format($course->price, 0, '', '.') }}
                                    </div>
                                    <span class="bg-gold-600/20 text-gold-300 text-xs font-bold px-2 py-1 rounded">
                                        {{ round((($course->original_price - $course->price) / $course->original_price) * 100) }}% OFF
                                    </span>
                                </div>
                            @else
                                <!-- Regular Price -->
                                <div class="text-lg font-bold text-gold-400">
                                    Rp {{ number_format($course->price, 0, '', '.') }}
                                </div>
                            @endif
                        </div>
                        
                        @auth
                            @if(auth()->user()->hasPurchasedCourse($course->id))
                                <div class="flex items-center space-x-2">

                                    <span class="text-gold-400 font-bold text-sm group-hover:text-gold-300 transition-colors duration-300">
                                        Lanjut Belajar →
                                    </span>
                                </div>
                            @else
                                <span class="text-gold-400 font-bold text-sm group-hover:text-gold-300 transition-colors duration-300">Beli Sekarang →</span>
                            @endif
                        @else
                            <span class="text-gold-400 font-bold text-sm group-hover:text-gold-300 transition-colors duration-300">Beli Sekarang →</span>
                        @endauth
                    @else
                        <!-- Free Course -->
                        <div class="text-lg font-bold text-green-400">Free</div>
                        <span class="text-gold-400 font-bold text-sm group-hover:text-gold-300 transition-colors duration-300">Mulai Belajar →</span>
                    @endif
                </div>
                
                <!-- Course Stats -->
                <div class="flex items-center justify-between text-xs text-beige-400 mt-2 pt-2 border-t border-charcoal-700">
                    <span class="font-medium">{{ $course->course_students_count ?? ($course->relationLoaded('courseStudents') ? $course->courseStudents->count() : 0) }} students</span>
                    <span class="font-medium">{{ $course->course_sections_count ?? ($course->relationLoaded('courseSections') ? $course->courseSections->count() : 0) }} sections</span>
                </div>
            </div>
        </div>
    </div>
</a>
