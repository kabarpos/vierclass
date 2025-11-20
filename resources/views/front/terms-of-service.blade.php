@extends('front.layouts.app')
@section('title', 'Terms of Service - ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'Upversity.id'))

@section('content')
    
    <x-nav-guest />

    @php
        // Brand name: gunakan dari pengaturan website dengan fallback aman
        $brandName = \App\Helpers\WebsiteSettingHelper::get('site_name', 'Upversity.id');

        // Kontak: ambil dari Website Setting dengan fallback aman
        $email   = \App\Helpers\WebsiteSettingHelper::get('contact_email', 'marketing@upversity.id');
        $phone   = \App\Helpers\WebsiteSettingHelper::get('contact_phone', '+6285155415050');
        $address = \App\Helpers\WebsiteSettingHelper::get('contact_address', 'Jl. Sriwijaya XXVI No. 20 Sumbersari - Jember, Jawa Timur, Indonesia');

        // Normalisasi href untuk mailto/tel
        $emailHref = 'mailto:' . $email;
        $phoneHref = 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
    @endphp
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-charcoal-900 py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Trust Badge -->
            <div class="inline-flex items-center space-x-2 px-4 py-2 bg-gold-600/10 border border-gold-600/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-8">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gold-300">KEBIJAKAN & PERATURAN</span>
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl lg:text-5xl font-bold text-beige-50 mb-6">
                Ketentuan Layanan
            </h1>
            <p class="text-xl text-beige-300 max-w-3xl mx-auto leading-relaxed">
                Ketentuan dan peraturan penggunaan platform {{ $brandName }} yang harus dipahami dan disetujui oleh semua pengguna
            </p>
        </div>
    </section>
    
    <!-- Terms Content Section -->
    <section class="bg-gradient-to-b from-charcoal-800 to-charcoal-900 py-20 lg:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg max-w-none">
                
                <!-- Section 1: Pendahuluan -->
                <div class="bg-charcoal-800/50 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        1. Pendahuluan
                    </h2>
                    <p class="text-beige-300 leading-relaxed">
                        Selamat datang di {{ $brandName }}. Dengan mengakses dan menggunakan platform kami, Anda setuju untuk terikat oleh ketentuan layanan ini. Platform {{ $brandName }} adalah sistem manajemen pembelajaran yang menyediakan akses ke berbagai kursus online, materi pembelajaran, dan sertifikasi profesional.
                    </p>
                </div>

                <!-- Section 2: Definisi -->
                <div class="bg-charcoal-800/30 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        2. Definisi
                    </h2>
                    <ul class="space-y-3 text-beige-300 leading-relaxed">
                        <li><strong>Platform:</strong> Sistem {{ $brandName }} yang dapat diakses melalui website dan aplikasi mobile</li>
                        <li><strong>Pengguna:</strong> Setiap individu yang mendaftar dan menggunakan layanan kami</li>
                        <li><strong>Konten:</strong> Semua materi pembelajaran, video, teks, gambar, dan dokumen yang tersedia di platform</li>
                        <li><strong>Kursus:</strong> Program pembelajaran terstruktur yang tersedia di platform</li>
                        <li><strong>Sertifikat:</strong> Dokumen digital yang diterbitkan setelah menyelesaikan kursus</li>
                    </ul>
                </div>

                <!-- Section 3: Pendaftaran dan Akun -->
                <div class="bg-charcoal-800/50 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        3. Pendaftaran dan Akun
                    </h2>
                    <div class="space-y-4 text-beige-300 leading-relaxed">
                        <p>Untuk menggunakan layanan kami, Anda harus:</p>
                        <ul class="space-y-2">
                            <li>• Berusia minimal 17 tahun atau memiliki izin dari orang tua/wali</li>
                            <li>• Menyediakan informasi yang akurat dan lengkap saat pendaftaran</li>
                            <li>• Menjaga kerahasiaan kata sandi dan informasi akun Anda</li>
                            <li>• Bertanggung jawab atas semua aktivitas yang terjadi dalam akun Anda</li>
                            <li>• Segera memberitahu kami jika ada penggunaan akun yang tidak sah</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 4: Penggunaan Platform -->
                <div class="bg-charcoal-800/30 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        4. Penggunaan Platform
                    </h2>
                    <div class="space-y-4 text-beige-300 leading-relaxed">
                        <p><strong>Anda diperbolehkan:</strong></p>
                        <ul class="space-y-2">
                            <li>• Mengakses dan menonton konten kursus yang telah Anda beli atau berlangganan</li>
                            <li>• Mengunduh materi untuk penggunaan pribadi dan tidak komersial</li>
                            <li>• Berpartisipasi dalam forum diskusi dan komunitas pembelajaran</li>
                            <li>• Memberikan umpan balik dan ulasan yang konstruktif</li>
                        </ul>
                        
                        <p class="mt-6"><strong>Anda dilarang:</strong></p>
                        <ul class="space-y-2">
                            <li>• Membagikan, menjual, atau mendistribusikan konten kepada pihak lain</li>
                            <li>• Menggunakan konten untuk tujuan komersial tanpa izin tertulis</li>
                            <li>• Merekam, menyalin, atau menggandakan materi kursus</li>
                            <li>• Mengganggu atau merusak fungsi platform</li>
                            <li>• Mengunggah konten yang melanggar hukum atau tidak pantas</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 5: Pembayaran dan Langganan -->
                <div class="bg-charcoal-800/50 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        5. Pembayaran dan Langganan
                    </h2>
                    <div class="space-y-4 text-beige-300 leading-relaxed">
                        <ul class="space-y-3">
                            <li>• Pembayaran dilakukan melalui gateway pembayaran yang aman</li>
                            <li>• Harga dapat berubah sewaktu-waktu dengan pemberitahuan sebelumnya</li>
                            <li>• Langganan akan diperpanjang otomatis kecuali dibatalkan sebelum tanggal perpanjangan</li>
                            <li>• Pengembalian dana hanya berlaku dalam kondisi tertentu sesuai kebijakan</li>
                            <li>• Akses konten akan dihentikan jika pembayaran gagal atau langganan berakhir</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 6: Hak Kekayaan Intelektual -->
                <div class="bg-charcoal-800/30 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        6. Hak Kekayaan Intelektual
                    </h2>
                    <p class="text-beige-300 leading-relaxed">
                        Semua konten, materi, design, logo, dan elemen lainnya di platform ini adalah milik {{ $brandName }} atau mitra konten kami. Pengguna diberikan lisensi terbatas untuk mengakses dan menggunakan konten hanya untuk tujuan pembelajaran pribadi. Setiap pelanggaran hak cipta akan ditindak sesuai hukum yang berlaku.
                    </p>
                </div>

                <!-- Section 7: Privasi dan Data -->
                <div class="bg-charcoal-800/50 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        7. Privasi dan Perlindungan Data
                    </h2>
                    <div class="space-y-4 text-beige-300 leading-relaxed">
                        <p>Kami berkomitmen melindungi privasi dan data pribadi Anda:</p>
                        <ul class="space-y-2">
                            <li>• Data pribadi hanya digunakan untuk keperluan layanan dan komunikasi</li>
                            <li>• Kami tidak akan menjual atau membagikan data Anda kepada pihak ketiga tanpa izin</li>
                            <li>• Data pembelajaran digunakan untuk meningkatkan pengalaman belajar</li>
                            <li>• Anda dapat meminta penghapusan data sesuai peraturan yang berlaku</li>
                            <li>• Keamanan data dijamin dengan enkripsi dan protokol keamanan terkini</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 8: Pembatasan Tanggung Jawab -->
                <div class="bg-charcoal-800/30 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        8. Pembatasan Tanggung Jawab
                    </h2>
                    <p class="text-beige-300 leading-relaxed">
                        {{ $brandName }} tidak bertanggung jawab atas kerugian yang timbul dari penggunaan platform, termasuk namun tidak terbatas pada kehilangan data, gangguan bisnis, atau kerusakan perangkat. Platform disediakan "sebagaimana adanya" tanpa jaminan tersurat maupun tersirat. Pengguna menggunakan layanan dengan risiko sendiri.
                    </p>
                </div>

                <!-- Section 9: Penangguhan dan Penghentian -->
                <div class="bg-charcoal-800/50 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        9. Penangguhan dan Penghentian
                    </h2>
                    <div class="space-y-4 text-beige-300 leading-relaxed">
                        <p>Kami berhak menangguhkan atau menghentikan akun Anda jika:</p>
                        <ul class="space-y-2">
                            <li>• Melanggar ketentuan layanan ini</li>
                            <li>• Melakukan aktivitas yang merugikan platform atau pengguna lain</li>
                            <li>• Gagal melakukan pembayaran sesuai ketentuan</li>
                            <li>• Menggunakan platform untuk tujuan ilegal</li>
                        </ul>
                        <p>Penangguhan dapat dilakukan dengan atau tanpa pemberitahuan sebelumnya, tergantung tingkat pelanggaran.</p>
                    </div>
                </div>

                <!-- Section 10: Perubahan Ketentuan -->
                <div class="bg-charcoal-800/30 backdrop-blur-sm border border-charcoal-700 rounded-2xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-gold-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        10. Perubahan Ketentuan
                    </h2>
                    <p class="text-beige-300 leading-relaxed">
                        Kami berhak mengubah ketentuan layanan ini sewaktu-waktu. Perubahan akan diumumkan melalui platform dan email terdaftar. Dengan terus menggunakan layanan setelah perubahan diberlakukan, Anda dianggap menyetujui ketentuan yang baru. Disarankan untuk memeriksa halaman ini secara berkala.
                    </p>
                </div>

                <!-- Contact Information -->
                <div class="bg-gold-50/20 backdrop-blur-sm border border-gold-300 rounded-2xl shadow-md p-8">
                    <h2 class="text-2xl font-bold text-beige-50 mb-4 flex items-center">
                        <div class="w-8 h-8 bg-gold-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        Kontak Kami
                    </h2>
                    <p class="text-beige-300 mb-4 leading-relaxed">
                        Jika Anda memiliki pertanyaan tentang ketentuan layanan ini, silakan hubungi kami:
                    </p>
                    <div class="space-y-2 text-beige-300 leading-relaxed">
                        <p><strong>Email:</strong>
                            <a
                                href="{{ $emailHref }}"
                                class="text-gold-700 hover:text-gold-900 font-semibold underline cursor-pointer"
                            >
                                {{ $email }}
                            </a>
                        </p>
                        <p><strong>Telepon:</strong>
                            <a
                                href="{{ $phoneHref }}"
                                class="text-gold-700 hover:text-gold-900 font-semibold underline cursor-pointer"
                            >
                                {{ $phone }}
                            </a>
                        </p>
                        <p><strong>Alamat:</strong> {{ $address }}</p>
                    </div>
                </div>

                <!-- Last Updated -->
                <div class="text-center mt-12 pt-8 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        Ketentuan layanan ini terakhir diperbarui pada tanggal 28 Agustus 2024
                    </p>
                </div>

            </div>
        </div>
    </section>
@endsection
