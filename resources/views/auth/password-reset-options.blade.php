@extends('front.layouts.app')
@section('title', 'Reset Password - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'))

@section('content')
    <x-nav-guest/>
    <main class="min-h-screen flex items-center justify-center py-12 px-5">
        <section class="w-full max-w-lg">
            <div class="flex flex-col w-full rounded-[20px] border border-LMS-grey p-8 gap-6 bg-white shadow-lg">
                <div class="text-center">
                    <h1 class="font-bold text-[22px] leading-[33px] mb-2 form-title">Reset Password</h1>
                    <p class="text-sm text-gray-600 form-text">Pilih metode untuk menerima link reset password</p>
                </div>

                <!-- Email Option -->
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-LMS-green transition-all duration-300 cursor-pointer" 
                         onclick="selectResetMethod('email')">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 form-label">Reset via Email</h3>
                                <p class="text-sm text-gray-600 form-text">Kirim link reset password ke email Anda</p>
                            </div>
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-LMS-green rounded-full hidden" id="email-check"></div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Option -->
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-LMS-green transition-all duration-300 cursor-pointer" 
                         onclick="selectResetMethod('whatsapp')">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.485"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 form-label">Reset via WhatsApp</h3>
                                <p class="text-sm text-gray-600 form-text">Kirim link reset password ke WhatsApp Anda</p>
                            </div>
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-LMS-green rounded-full hidden" id="whatsapp-check"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Form -->
                <form id="email-form" method="POST" action="{{ route('password.email') }}" class="hidden space-y-4">
                    @csrf
                    <input type="hidden" name="method" value="email">
                    <div class="flex flex-col gap-2">
                        <p class="form-label">Email Address</p>
                        <label class="relative group">
                            <input name="email" type="email" required
                                class="appearance-none outline-none w-full rounded-full border border-LMS-grey py-[14px] px-5 pl-12 font-semibold placeholder:font-normal placeholder:text-LMS-text-secondary group-focus-within:border-LMS-green transition-all duration-300"
                                placeholder="Masukkan email Anda">
                            <x-lazy-image 
                                src="{{ asset('assets/images/icons/sms.svg') }}"
                                alt="email icon"
                                class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                                loading="eager" />
                        </label>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-[10px] rounded-full py-[14px] px-5 bg-LMS-green hover:drop-shadow-effect transition-all duration-300 cursor-pointer">
                        <span class="font-semibold text-white">Kirim Link Reset via Email</span>
                    </button>
                </form>

                <!-- WhatsApp Form -->
                <form id="whatsapp-form" method="POST" action="{{ route('password.whatsapp') }}" class="hidden space-y-4">
                    @csrf
                    <input type="hidden" name="method" value="whatsapp">
                    <div class="flex flex-col gap-2">
                        <p class="form-label">Email Address</p>
                        <label class="relative group">
                            <input name="email" type="email" required
                                class="appearance-none outline-none w-full rounded-full border border-LMS-grey py-[14px] px-5 pl-12 font-semibold placeholder:font-normal placeholder:text-LMS-text-secondary group-focus-within:border-LMS-green transition-all duration-300"
                                placeholder="Masukkan email Anda">
                            <x-lazy-image 
                                src="{{ asset('assets/images/icons/sms.svg') }}"
                                alt="email icon"
                                class="absolute size-5 flex shrink-0 transform -translate-y-1/2 top-1/2 left-5"
                                loading="eager" />
                        </label>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        <p class="text-xs text-gray-500 form-text-xs">Link reset akan dikirim ke nomor WhatsApp yang terdaftar pada akun email ini</p>
                    </div>
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-[10px] rounded-full py-[14px] px-5 bg-green-600 hover:bg-green-700 transition-all duration-300 cursor-pointer">
                        <span class="font-semibold text-white">Kirim Link Reset via WhatsApp</span>
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-LMS-green transition-colors cursor-pointer form-text">
                        ← Kembali ke Login
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function selectResetMethod(method) {
            // Hide all forms
            document.getElementById('email-form').classList.add('hidden');
            document.getElementById('whatsapp-form').classList.add('hidden');
            
            // Hide all checks
            document.getElementById('email-check').classList.add('hidden');
            document.getElementById('whatsapp-check').classList.add('hidden');
            
            // Reset border colors
            document.querySelectorAll('.border').forEach(el => {
                if (el.classList.contains('rounded-xl')) {
                    el.classList.remove('border-LMS-green');
                    el.classList.add('border-gray-200');
                }
            });
            
            if (method === 'email') {
                document.getElementById('email-form').classList.remove('hidden');
                document.getElementById('email-check').classList.remove('hidden');
                event.currentTarget.classList.remove('border-gray-200');
                event.currentTarget.classList.add('border-LMS-green');
            } else if (method === 'whatsapp') {
                document.getElementById('whatsapp-form').classList.remove('hidden');
                document.getElementById('whatsapp-check').classList.remove('hidden');
                event.currentTarget.classList.remove('border-gray-200');
                event.currentTarget.classList.add('border-LMS-green');
            }
        }
    </script>
@endsection