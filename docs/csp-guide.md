# Panduan CSP dan Refactor Halaman Learning

Dokumen ini menjelaskan perubahan yang dilakukan untuk memperketat Content Security Policy (CSP), memindahkan style inline dari halaman Learning, menambahkan nonce ke script inline, serta cara penggunaan di development dan production.

## Ringkasan Perubahan

- Style inline di `resources/views/courses/learning.blade.php` dipindahkan ke `resources/css/custom.css` dan di-scope dengan kelas wrapper `.learning-page`.
- Wrapper utama halaman Learning ditambahkan kelas `learning-page` untuk mengaktifkan style baru.
- Script inline pada blok `@push('after-scripts')` di halaman Learning diberi `nonce` agar lolos CSP tanpa `'unsafe-inline'` di production.
- Middleware `app/Http/Middleware/SecurityHeaders.php` diperbarui:
  - Production: menambahkan `'nonce-<value>'` ke `script-src`/`script-src-elem` dan `style-src`/`style-src-elem`, serta menghapus `'unsafe-inline'`.
  - Development: tetap menggunakan `Content-Security-Policy-Report-Only` dan mengizinkan `'unsafe-inline'` serta `localhost:5173` dan `http://[::1]:5173` untuk HMR Vite (termasuk `ws:`/`wss:`).

## Lokasi File dan Perubahan

- `resources/views/courses/learning.blade.php`
  - Menghapus `<style>` kecil di `<head>` (font-family)
  - Menambahkan `class="learning-page"` pada wrapper utama
  - Membungkus blok `<style>` dalam `@push('after-styles')` dengan komentar Blade `{{-- ... --}}` agar tidak dirender
  - Menambahkan `nonce` pada `<script>` inline di `@push('after-scripts')`

- `resources/css/custom.css`
  - Menambahkan blok style besar yang sebelumnya inline, di-scope dengan prefix `.learning-page`

- `app/Http/Middleware/SecurityHeaders.php`
  - Menambahkan pembuatan directive CSP berbasis environment:
    - Production: `'nonce-<value>'` untuk `script-src`/`style-src` dan menghapus `'unsafe-inline'`
    - Development: `Report-Only`, tetap `'unsafe-inline'`, whitelist dev server/HMR

## Cara Menambahkan Nonce di View

- Pastikan middleware menyuntikkan nilai nonce ke request: `request()->attributes->get('csp_nonce')`
- Untuk script inline:
  ```html
  <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // kode Anda di sini
  </script>
  ```
- Untuk style inline (disarankan dipindahkan ke file CSS). Jika terpaksa inline, gunakan:
  ```html
  <style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* style Anda */
  </style>
  ```

## Petunjuk Penggunaan

- Development
  - Jalankan `npm run dev` untuk HMR Vite
  - Saat menghentikan dev server, hapus `public/hot` atau jalankan `npm run build`
  - CSP berjalan dalam mode `Report-Only` sehingga tidak memblokir resource

- Production
  - Jalankan `npm run build` untuk menghasilkan aset di `public/build`
  - CSP ketat: tidak ada `'unsafe-inline'`; gunakan nonce pada script/style inline
  - Pastikan dependensi CDN yang diperlukan sudah di-whitelist di middleware

## Verifikasi dan Troubleshooting

- Setelah build, jalankan server lokal: `php artisan serve` lalu buka `http://127.0.0.1:8000`
- Periksa Console browser untuk error CSP, dan terminal untuk log kesalahan
- Jika ada script inline tanpa nonce yang diblokir:
  - Tambahkan attribute `nonce` seperti contoh di atas
  - Atau pindahkan script ke berkas JS yang dibundel oleh Vite
- Jika style rusak di halaman Learning:
  - Pastikan wrapper memiliki kelas `learning-page`
  - Periksa bahwa `resources/css/custom.css` termuat via `@vite` dan bahwa selector sesuai

## Catatan Keamanan Lanjutan

- Pertimbangkan memigrasi dependensi CDN ke NPM untuk memperketat `script-src` tanpa CDN eksternal
- Untuk inline script yang harus tetap ada, pertimbangkan menggunakan `nonce` atau `hash` berbasis konten
- Hindari `unsafe-eval` dan `wasm-unsafe-eval` di production

## Checklist Implementasi

- [x] Style inline dipindahkan ke `custom.css` dengan `.learning-page`
- [x] Nonce ditambahkan pada script inline halaman Learning
- [x] CSP production diperketat dan dev tetap fleksibel
- [x] Build aset berhasil (`npm run build`)
- [x] Preview tanpa error CSP

## Referensi

- MDN: Content Security Policy (CSP)
- Vite: Security considerations untuk dev server & HMR