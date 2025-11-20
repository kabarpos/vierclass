@extends('layouts.app')
@section('title', 'Search Courses - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))
@section('content')
    <x-navigation-auth />
    <main class="flex flex-col gap-10 pb-10 mt-[50px]">
        <div class="flex flex-col items-center gap-[10px] max-w-[500px] w-full mx-auto">
            <h1 class="font-bold text-[28px] leading-[42px] text-center">Explore Our Greatest Courses</h1>
            <form method="GET" action="{{ route('dashboard.search.courses') }}" class="relative ">
                <label class="group">
                    <input type="text" name="search" id="" class="appearance-none outline-none ring-1 ring-LMS-grey rounded-full w-[550px] py-[14px] px-5 bg-white font-bold placeholder:font-normal placeholder:text-LMS-text-secondary group-focus-within:ring-LMS-green transition-all duration-300 pr-[50px]" placeholder="Search course by name">
                    <button type="submit" class="absolute right-0 top-0 h-[52px] w-[52px] flex shrink-0 items-center justify-center">
                        <x-lazy-image 
                            src="{{ asset ('assets/images/icons/search-normal-green-fill.svg') }}" 
                            alt="" 
                            class="flex shrink-0 w-10 h-10"
                            loading="eager" />
                    </button>
                </label>
            </form>
        </div>
        <section id="result" class="flex flex-col w-full max-w-[1280px] px-[75px] gap-5 mx-auto">
            <h2 class="font-bold text-[22px] leading-[33px]">Search Result for: {{ request()->search }}</h2>
            <div id="result-list" class="tab-content grid grid-cols-3 gap-5">

                @forelse($courses as $course)
                    <x-course-card :course="$course" />
                @empty
                    <p>No courses available in this keyword.</p>
                @endforelse

            </div>
        </section>
    </main>


@endsection
