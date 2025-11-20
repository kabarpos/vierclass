Berikut yang sudah aku kerjakan supaya development nyaman dan production tetap aman.

Yang Diubah

- CSP middleware diperbarui:
  - Development: pakai Content-Security-Policy-Report-Only, izinkan localhost:5173 dan http://[::1]:5173 (IPv6) untuk HMR, ws: / wss: untuk WebSocket, tetap longgar untuk style-src dan script-src .
  - Production: hilangkan unsafe-eval / wasm-unsafe-eval , tambahkan whitelist untuk midtrans , code.jquery.com , cdnjs.cloudflare.com , fonts.googleapis.com , fonts.bunny.net , fonts.gstatic.com . Tambah script-src-elem dan style-src-elem agar tidak ada fallback warnings.
- Hapus inline style global font di resources/views/front/layouts/app.blade.php (font dikontrol dari resources/css/app.css dan custom.css ).
- Build ulang aset ( npm run build ) sehingga manifest.json dan CSS/JS terbaru dipakai.
- Preview dibuka di http://127.0.0.1:8000/ dan tidak ada error CSP di halaman depan.
Cara Pakai di Development

- Jalankan npm run dev saat butuh HMR. CSP dev sudah mengizinkan localhost:5173 dan http://[::1]:5173 .
- Kalau berhenti dev server, hapus public/hot atau jalankan npm run build agar kembali ke aset hasil build.
Catatan Production Security

- Saat ini kita masih mengizinkan 'unsafe-inline' untuk style-src karena masih ada beberapa <style> inline (contohnya di halaman learning). Ini menjaga UI tidak rusak.
- Target berikutnya agar CSP makin ketat:
  - Pindahkan sisa <style> inline ke resources/css/custom.css .
  - Ubah <script> inline menjadi file modul yang dibundel Vite, atau tambahkan nonce dari middleware ke tag <script> inline. Middleware sudah menghasilkan nonce , dan nanti bisa kita gunakan di Blade ( nonce="{{ request()->attributes->get('csp_nonce') }}" ).
Rekomendasi Lanjutan

- Migrasikan ketergantungan CDN (jQuery, highlight.js) ke paket NPM dan import via Vite supaya script-src di production bisa dipersempit ke 'self' saja.
- Jika mau, aku bisa lanjut memindahkan inline <style> besar di halaman learning dan menambahkan nonce ke skrip inline yang kritikal, sehingga kita bisa menghapus 'unsafe-inline' dari style-src dan script-src di production.