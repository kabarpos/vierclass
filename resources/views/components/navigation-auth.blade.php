<nav id="nav-auth" class="bg-charcoal-900/95 backdrop-blur-md border-b border-charcoal-800 shadow-lg" x-data="{ open: false, profileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('front.index') }}" class="flex-shrink-0 cursor-pointer">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="logo" class="h-6 w-auto" />
                </a>
            </div>

            <!-- Desktop Search -->
            <div class="hidden lg:flex flex-1 max-w-lg mx-8">
                <form method="GET" action="{{ route('dashboard.search.courses') }}" class="w-full">
                    <div class="relative">
                        <input type="text" name="search" 
                               class="w-full pl-4 pr-12 py-3 bg-charcoal-800 border border-charcoal-700 text-beige-50 placeholder:text-beige-400 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all duration-200" 
                               placeholder="Search courses...">
                        <button type="submit" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gold-400 hover:text-gold-300 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Desktop Actions & Profile -->
            <div class="hidden lg:flex items-center space-x-4">

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-charcoal-800 transition-colors duration-200 cursor-pointer">
                        <div class="w-8 h-8 rounded-full overflow-hidden bg-charcoal-700 flex-shrink-0">
                            @if($user && $user->photo)
                                <x-lazy-image 
                                    src="{{ Storage::url($user->photo) }}" 
                                    alt="profile" 
                                    class="w-full h-full object-cover"
                                    loading="eager" />
                            @else
                                <x-lazy-image 
                                    src="{{ getUserAvatarWithColor($user, 32) }}" 
                                    alt="profile" 
                                    class="w-full h-full object-cover"
                                    loading="eager" />
                            @endif
                        </div>
                        @if($user)
                        <div class="hidden lg:block text-left">
                            <p class="text-sm font-medium text-beige-50">{{ $user->name }}</p>
                            <p class="text-xs text-beige-400">{{ $user->occupation ?? 'Student' }}</p>
                        </div>
                        @endif
                        <svg class="w-4 h-4 text-beige-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileOpen" @click.away="profileOpen = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-charcoal-800 rounded-lg shadow-lg border border-charcoal-700 py-1 z-50">
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-beige-200 hover:bg-charcoal-700 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                            My Courses
                        </a>

                        @php
                            $roleNames = $user?->getRoleNames()?->map(fn($n) => strtolower($n)) ?? collect();
                            if ($roleNames->isEmpty() && isset($user) && method_exists($user, 'roles')) {
                                $roleNames = $user->roles->pluck('name')->map(fn($n) => strtolower($n));
                            }
                        @endphp
                        @if($user && ($roleNames->contains('admin') || $roleNames->contains('super-admin') || $roleNames->contains('mentor')))
                            <a href="/admin" target="_blank" class="block px-4 py-2 text-sm text-beige-200 hover:bg-charcoal-700 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                                Admin Panel
                            </a>
                        @endif
                        <div class="border-t border-charcoal-700 my-1"></div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-beige-200 hover:bg-charcoal-700 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                            Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-beige-200 hover:bg-charcoal-700 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="lg:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-beige-300 hover:text-gold-400 hover:bg-charcoal-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gold-500 cursor-pointer">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden lg:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-charcoal-900 border-t border-charcoal-800">
            <!-- Mobile Search -->
            <div class="px-3 py-2">
                <form method="GET" action="{{ route('dashboard.search.courses') }}">
                    <div class="relative">
                        <input type="text" name="search" 
                               class="w-full pl-4 pr-12 py-2 bg-charcoal-800 border border-charcoal-700 text-beige-50 placeholder:text-beige-400 rounded-lg focus:ring-2 focus:ring-gold-500 focus:border-gold-500" 
                               placeholder="Search courses...">
                        <button type="submit" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 text-gold-400 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Mobile Profile Section -->
            <div class="pt-4 pb-3 border-t border-charcoal-800">
                <div class="flex items-center px-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-charcoal-700 flex-shrink-0">
                        @if($user && $user->photo)
                            <x-lazy-image 
                                src="{{ Storage::url($user->photo) }}" 
                                alt="profile" 
                                class="w-full h-full object-cover"
                                loading="eager" />
                        @else
                            <x-lazy-image 
                                src="{{ getUserAvatarWithColor($user, 40) }}" 
                                alt="profile" 
                                class="w-full h-full object-cover"
                                loading="eager" />
                        @endif
                    </div>
                    @if($user)
                    <div class="ml-3">
                        <div class="text-base font-medium text-beige-50">{{ $user->name }}</div>
                        <div class="text-sm text-beige-400">{{ $user->occupation ?? 'Student' }}</div>
                    </div>
                    @endif
                </div>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                        My Courses
                    </a>

                    @php
                        $roleNames = $user?->getRoleNames()?->map(fn($n) => strtolower($n)) ?? collect();
                        if ($roleNames->isEmpty() && isset($user) && method_exists($user, 'roles')) {
                            $roleNames = $user->roles->pluck('name')->map(fn($n) => strtolower($n));
                        }
                    @endphp
                    @if($user && ($roleNames->contains('admin') || $roleNames->contains('super-admin')))
                        <a href="/admin" target="_blank" class="block px-3 py-2 text-base text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                            Admin Panel
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-3 py-2 text-base text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 transition-colors duration-200 cursor-pointer">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
