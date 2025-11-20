<x-filament::section>
    <x-filament::section.heading>
        Pintasan
    </x-filament::section.heading>
    <x-filament::section.description>
        Aksi cepat ke fitur penting.
    </x-filament::section.description>

    @php($items = [
        ['label' => 'Users', 'href' => '/admin/users', 'desc' => 'Kelola pengguna'],
        ['label' => 'Kursus', 'href' => '/admin/courses', 'desc' => 'Kelola kursus'],
        ['label' => 'Kategori', 'href' => '/admin/categories', 'desc' => 'Kelola kategori'],
        ['label' => 'Mentor Kursus', 'href' => '/admin/course-mentors', 'desc' => 'Kelola mentor'],
        ['label' => 'Konten Bagian', 'href' => '/admin/section-contents', 'desc' => 'Kelola konten'],
        ['label' => 'Diskon', 'href' => '/admin/discounts', 'desc' => 'Kelola diskon'],
        ['label' => 'Midtrans', 'href' => '/admin/midtrans-settings', 'desc' => 'Pengaturan pembayaran'],
        ['label' => 'WhatsApp', 'href' => '/admin/whatsapp-settings', 'desc' => 'Pengaturan WhatsApp'],
        ['label' => 'Template WhatsApp', 'href' => '/admin/whatsapp-message-templates', 'desc' => 'Kelola template pesan'],
    ])

    @foreach($items as $item)
        <x-filament::section>
            <x-filament::section.heading>
                {{ $item['label'] }}
            </x-filament::section.heading>
            <x-filament::section.description>
                {{ $item['desc'] }}
            </x-filament::section.description>
            <div>
                <x-filament::button tag="a" href="{{ $item['href'] }}" icon="heroicon-o-arrow-right" icon-position="after">
                    Buka
                </x-filament::button>
            </div>
        </x-filament::section>
    @endforeach
</x-filament::section>