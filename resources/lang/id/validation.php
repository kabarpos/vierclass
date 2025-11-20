<?php

return [
    // Pesan validasi umum
    'accepted' => 'Kolom :attribute harus diterima.',
    'active_url' => 'Kolom :attribute bukan URL yang valid.',
    'after' => 'Kolom :attribute harus tanggal setelah :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Kolom :attribute harus berupa array.',
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'date_format' => 'Kolom :attribute tidak cocok dengan format :format.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus berupa angka :digits digit.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'exists' => 'Pilihan :attribute tidak valid.',
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute tidak valid.',
    'integer' => 'Kolom :attribute harus berupa angka.',
    'json' => 'Kolom :attribute harus berupa JSON yang valid.',
    'max' => [
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'file' => 'Berkas :attribute tidak boleh lebih dari :max kilobyte.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
        'array' => 'Kolom :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes' => 'Berkas :attribute harus bertipe: :values.',
    'mimetypes' => 'Berkas :attribute harus bertipe: :values.',
    'min' => [
        'numeric' => 'Kolom :attribute minimal :min.',
        'file' => 'Berkas :attribute minimal :min kilobyte.',
        'string' => 'Kolom :attribute minimal :min karakter.',
        'array' => 'Kolom :attribute minimal memiliki :min item.',
    ],
    'not_in' => 'Pilihan :attribute tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_if' => 'Kolom :attribute wajib diisi ketika :other adalah :value.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other ada di :values.',
    'required_with' => 'Kolom :attribute wajib diisi ketika :values ada.',
    'required_with_all' => 'Kolom :attribute wajib diisi ketika :values ada.',
    'required_without' => 'Kolom :attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => 'Kolom :attribute wajib diisi ketika tidak ada :values.',
    'same' => 'Kolom :attribute dan :other harus sama.',
    'size' => [
        'numeric' => 'Kolom :attribute harus berukuran :size.',
        'file' => 'Berkas :attribute harus berukuran :size kilobyte.',
        'string' => 'Kolom :attribute harus berukuran :size karakter.',
        'array' => 'Kolom :attribute harus berisi :size item.',
    ],
    'string' => 'Kolom :attribute harus berupa teks.',
    'url' => 'Kolom :attribute harus berupa URL yang valid.',

    // Kustomisasi nama atribut agar lebih manusiawi di UI
    'attributes' => [
        'content' => 'Konten',
        'name' => 'Nama',
        'youtube_url' => 'URL YouTube',
        'is_free' => 'Pratinjau Gratis',
        'course_id' => 'Course',
        'course_section_id' => 'Bagian Course',
        'thumbnail' => 'Thumbnail',
        'price' => 'Harga',
        'email' => 'Email',
        'password' => 'Kata sandi',
    ],

    // Pesan kustom untuk field tertentu (opsional)
    'custom' => [
        'content' => [
            'required' => 'Konten tidak boleh kosong.',
        ],
        'youtube_url' => [
            'url' => 'URL YouTube tidak valid.',
        ],
    ],
];

