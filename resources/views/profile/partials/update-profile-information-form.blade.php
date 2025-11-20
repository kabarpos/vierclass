<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Photo Section -->
        <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-6 space-y-4 lg:space-y-0">
            <div class="flex-shrink-0">
                <div class="w-24 h-24 lg:w-32 lg:h-32 rounded-full overflow-hidden bg-gray-200 mx-auto lg:mx-0">
                    @if($user->photo)
                        <x-lazy-image id="profile-photo-preview" src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover" alt="Profile Photo" loading="eager" />
                    @else
                        <x-lazy-image id="profile-photo-preview" src="{{ getUserAvatarWithColor($user, 128) }}" class="w-full h-full object-cover" alt="Profile Photo" loading="eager" />
                    @endif
                </div>
                <div class="mt-4 text-center lg:text-left">
                    <label for="photo" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-mountain-meadow-500 focus:border-transparent cursor-pointer transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Change Photo
                    </label>
                    <div id="photo-status" class="mt-2 text-sm hidden"></div>
                    <input id="photo" name="photo" type="file" accept="image/*" class="hidden" onchange="previewPhoto(event)">
                </div>
            </div>
            
            <div class="flex-1 space-y-6">
                <!-- Name Field -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-gray-900">
                        Full Name
                    </label>
                    <div class="relative">
                        <input id="name" name="name" type="text" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mountain-meadow-500 focus:border-transparent transition-all duration-200 font-medium" 
                               value="{{ old('name', $user->name) }}" 
                               placeholder="Enter your full name"
                               required autofocus autocomplete="name">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-gray-900">
                        Email Address
                    </label>
                    <div class="relative">
                        <input id="email" name="email" type="email" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mountain-meadow-500 focus:border-transparent transition-all duration-200 font-medium" 
                               value="{{ old('email', $user->email) }}" 
                               placeholder="Enter your email address"
                               required autocomplete="username">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Email verification required</p>
                                    <p class="text-sm text-amber-700 mt-1">Your email address is unverified.</p>
                                    <button form="send-verification" class="mt-2 text-sm text-amber-600 underline hover:text-amber-800 transition-colors">
                                        Click here to re-send the verification email.
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm text-green-800 font-medium">A new verification link has been sent to your email address.</p>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- WhatsApp Number Field -->
                <div class="space-y-2">
                    <label for="whatsapp_number" class="block text-sm font-semibold text-gray-900">
                        WhatsApp Number
                    </label>
                    <div class="relative">
                        <input id="whatsapp_number" name="whatsapp_number" type="tel" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mountain-meadow-500 focus:border-transparent transition-all duration-200 font-medium" 
                               value="{{ old('whatsapp_number', $user->whatsapp_number ?? '') }}" 
                               placeholder="+62812345678"
                               required
                               autocomplete="tel">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    @error('whatsapp_number')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-gray-200 gap-4">
            <div class="flex items-center space-x-4">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-mountain-meadow-600 to-mountain-meadow-700 text-white font-semibold rounded-lg hover:from-mountain-meadow-700 hover:to-mountain-meadow-800 focus:outline-none focus:ring-2 focus:ring-mountain-meadow-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
                
                @if (session('status') === 'profile-updated')
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(function(){ show = false }, 3000)" 
                         class="flex items-center space-x-2 text-green-600 bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">Profile updated successfully!</span>
                    </div>
                @endif
            </div>
        </div>
    </form>
</section>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function previewPhoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('profile-photo-preview');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, JPG, or GIF)');
            event.target.value = '';
            return;
        }
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        
        reader.onerror = function() {
            alert('Error reading file');
        };
        
        reader.readAsDataURL(file);
    }
}
</script>
