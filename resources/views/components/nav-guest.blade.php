<nav id="nav-guest" class="relative backdrop-blur-md border-b border-rebel-red-900/30 shadow-lg sticky top-0 z-50" x-data="{ open: false }">
    <!-- Dark Red Radial Combination Background -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-rebel-red-950/95 via-rebel-red-900/70 to-rebel-black-1000/95"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-rebel-black-1000/60 via-transparent to-transparent"></div>
    <div class="absolute inset-0 bg-rebel-red-950/30"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('front.index') }}" class="flex-shrink-0 cursor-pointer">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-8 w-auto" alt="logo" />
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('front.index') }}" class="{{ request()->routeIs('front.index') ? 'text-gold-400 font-bold' : 'text-beige-200 hover:text-gold-400 font-semibold' }} transition-colors duration-300 cursor-pointer">
                    Beranda
                </a>
                <a href="{{ route('front.courses') }}" class="{{ request()->routeIs('front.courses') ? 'text-gold-400 font-bold' : 'text-beige-200 hover:text-gold-400 font-semibold' }} transition-colors duration-300 cursor-pointer">
                    Kajian
                </a>
                <a href="{{ route('front.terms-of-service') }}" class="{{ request()->routeIs('front.terms-of-service') ? 'text-gold-400 font-bold' : 'text-beige-200 hover:text-gold-400 font-semibold' }} transition-colors duration-300 cursor-pointer">
                    Peraturan
                </a>

            </div>

            <!-- Desktop Action Buttons -->
            <div class="hidden lg:flex items-center space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 text-sm font-bold text-charcoal-900 bg-gold-500 rounded-lg hover:bg-gold-400 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2.5 text-sm font-bold text-charcoal-900 bg-gold-500 rounded-lg hover:bg-gold-400 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer">
                        Masuk
                    </a>
                @endauth
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
    <div :class="{'block': open, 'hidden': !open}" class="hidden lg:hidden relative">
        <div class="px-2 pt-2 pb-3 space-y-1 border-t border-rebel-red-900/30">
            <a href="{{ route('front.index') }}" class="{{ request()->routeIs('front.index') ? 'bg-gold-600/20 text-gold-400 font-bold' : 'text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 font-semibold' }} block px-3 py-2 rounded-md text-base transition-colors duration-300 cursor-pointer">
                Beranda
            </a>
            <a href="{{ route('front.courses') }}" class="{{ request()->routeIs('front.courses') ? 'bg-gold-600/20 text-gold-400 font-bold' : 'text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 font-semibold' }} block px-3 py-2 rounded-md text-base transition-colors duration-300 cursor-pointer">
                Kajian
            </a>
            <a href="{{ route('front.terms-of-service') }}" class="{{ request()->routeIs('front.terms-of-service') ? 'bg-gold-600/20 text-gold-400 font-bold' : 'text-beige-200 hover:bg-charcoal-800 hover:text-gold-400 font-semibold' }} block px-3 py-2 rounded-md text-base transition-colors duration-300 cursor-pointer">
                Peraturan
            </a>

            <div class="pt-4 pb-3 border-t border-charcoal-800">
                <div class="px-3 space-y-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block text-center px-4 py-2 text-sm font-bold text-charcoal-900 bg-gold-500 rounded-lg hover:bg-gold-400 transition-all duration-300 cursor-pointer">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="block text-center px-4 py-2 text-sm font-semibold text-beige-300 hover:text-gold-400 transition-colors duration-300 cursor-pointer">
                            Belum punya akun? Daftar di sini
                        </a>
                        <a href="{{ route('login') }}" class="block text-center px-4 py-2 text-sm font-bold text-charcoal-900 bg-gold-500 rounded-lg hover:bg-gold-400 transition-all duration-300 cursor-pointer">
                            Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</nav>
