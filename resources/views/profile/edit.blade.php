@extends('layouts.app')
@section('title', 'Profile Settings - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))
@section('content')
    <x-navigation-auth />
    
    <!-- Main Content -->
    <main class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-6">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-mountain-meadow-600 transition-colors cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
                
                <div class="text-center lg:text-left">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">Profile Settings</h1>
                    <p class="text-lg text-gray-600">Manage your account information and security settings</p>
                </div>
            </div>
            
            <!-- Profile Content -->
            <div class="space-y-8">
                <!-- Profile Information Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-mountain-meadow-600 to-mountain-meadow-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Profile Information</h2>
                        <p class="text-mountain-meadow-100 text-sm mt-1">Update your account's profile information and email address</p>
                    </div>
                    <div class="p-6 lg:p-8">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Password Security Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Password Security</h2>
                        <p class="text-amber-100 text-sm mt-1">Ensure your account is using a long, random password to stay secure</p>
                    </div>
                    <div class="p-6 lg:p-8">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    
@endsection
