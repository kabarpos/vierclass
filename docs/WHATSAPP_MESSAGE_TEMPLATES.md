# Template Pesan WhatsApp

Dokumen ini merangkum mekanisme pengiriman pesan WhatsApp di proyek serta contoh template siap pakai untuk setiap tipe yang tersedia di pengaturan “Template Pesan WhatsApp”.

## Mekanisme Singkat
- Konfigurasi layanan WhatsApp ada di `WhatsappSetting` dan halaman admin Filament. Wajib isi `api_key`, `base_url`, dan aktifkan `is_active`.
- Pengiriman pesan dilakukan oleh `DripsenderService::sendMessage(phone, message)` yang memanggil API Dripsender. Nomor telepon diformat ke `62xxxxxxxxxx` dan divalidasi.
- Template disimpan di tabel `whatsapp_message_templates` dan dimodelkan oleh `WhatsappMessageTemplate`. Variabel di pesan diganti melalui `parseMessage()` dengan data aktual.
- Alur notifikasi di `WhatsappNotificationService` memilih template aktif berdasarkan `type`, menyusun data variabel, lalu mengirim.
- Khusus verifikasi pendaftaran, link dibuat via route `whatsapp.verification.verify` dengan `id` dan `token` pengguna.

## Tipe Template yang Tersedia
- `registration_verification` — Verifikasi Pendaftaran (pesan link konfirmasi/verifikasi akun).
- `order_completion` — Penyelesaian Order (setelah user membuat order, berisi link pembayaran).
- `payment_received` — Pembayaran Diterima (konfirmasi sukses, akses materi).
- `course_purchase` — Pembelian Kursus Individual (akses seumur hidup untuk kursus yang dibeli).
- `password_reset` — Reset Password (link reset dengan masa berlaku).

## Variabel Per Tipe
- Verifikasi Pendaftaran: `{user_name}`, `{verification_link}`, `{app_name}`
- Penyelesaian Order: `{user_name}`, `{order_id}`, `{course_name}`, `{total_amount}`, `{payment_link}`, `{app_name}`
- Pembayaran Diterima: `{user_name}`, `{order_id}`, `{course_name}`, `{total_amount}`, `{app_name}`
- Pembelian Kursus Individual: `{user_name}`, `{course_name}`, `{course_price}`, `{transaction_id}`, `{course_url}`, `{dashboard_url}`, `{app_name}`
- Reset Password: `{user_name}`, `{reset_url}`, `{app_name}`, `{expiry_time}`

## Contoh Template Siap Pakai

### 1) Verifikasi Pendaftaran (`registration_verification`)
- Subject: `Verifikasi Akun {app_name}`
- Message:
  """
  Halo {user_name}! 👋
  
  Selamat datang di {app_name}!
  
  Untuk melengkapi proses pendaftaran, silakan verifikasi akun Anda dengan mengklik link berikut:
  
  🔗 {verification_link}
  
  ⚠️ Penting: Jika link verifikasi tidak diklik, akun Anda TIDAK AKAN AKTIF dan tidak bisa mengakses kursus.
  
  📞 Butuh bantuan? Hubungi customer service kami.
  
  Terima kasih! 🙏
  """

### 2) Penyelesaian Order (`order_completion`)
- Subject: `Order Berhasil - {order_id}`
- Message:
  """
  Halo {user_name}! 🎉
  
  Terima kasih telah melakukan pemesanan di {app_name}!
  
  📋 Detail Order:
  • ID Order: {order_id}
  • Kursus: {course_name}
  • Total Pembayaran: {total_amount}
  
  💳 Untuk menyelesaikan pembayaran, silakan klik link berikut:
  {payment_link}
  
  ⏰ Selesaikan pembayaran dalam 24 jam agar order tidak dibatalkan otomatis.
  
  📞 Ada pertanyaan? Hubungi customer service kami.
  
  Terima kasih! 🙏
  """

### 3) Pembayaran Diterima (`payment_received`)
- Subject: `Pembayaran Diterima - {order_id}`
- Message:
  """
  Halo {user_name}! ✅
  
  Kabar gembira! Pembayaran Anda telah kami terima dan dikonfirmasi.
  
  🎊 Detail Pembayaran:
  • ID Order: {order_id}
  • Kursus: {course_name}
  • Total: {total_amount}
  
  🚀 Sekarang Anda sudah bisa mengakses seluruh materi kursus!
  
  Ayo mulai belajar dan raih kesuksesan Anda! 💪
  
  📚 Akses kursus: {app_name}
  
  Selamat belajar! 🎓
  """

### 4) Pembelian Kursus Individual (`course_purchase`)
- Subject: `Kursus Berhasil Dibeli - {course_name}`
- Message:
  """
  Halo {user_name}! 🎉
  
  Selamat! Pembelian kursus Anda telah berhasil diproses dan Anda sekarang memiliki akses SEUMUR HIDUP ke kursus ini!
  
  📚 Detail Kursus:
  • Nama Kursus: {course_name}
  • Harga: {course_price}
  • ID Transaksi: {transaction_id}
  
  🚀 Mulai belajar sekarang:
  {course_url}
  
  📊 Dashboard Anda:
  {dashboard_url}
  
  💡 Tips: Manfaatkan fitur progress tracking untuk memantau kemajuan belajar Anda!
  
  Selamat belajar dan raih kesuksesan! 🎓✨
  """

### 5) Reset Password (`password_reset`)
- Subject: `Reset Password - {app_name}`
- Message:
  """
  Halo {user_name}! 🔐
  
  Anda telah meminta untuk mereset password akun Anda di {app_name}.
  
  🔗 Klik link berikut untuk mereset password:
  {reset_url}
  
  ⚠️ Link ini akan kedaluwarsa dalam {expiry_time}.
  
  🛡️ Jika Anda tidak meminta reset password, abaikan pesan ini dan akun Anda tetap aman.
  
  📞 Butuh bantuan? Hubungi customer service kami.
  
  Terima kasih! 🙏
  """

## Cara Pakai
- Buat/ubah template di halaman admin “Template Pesan WhatsApp” dan pastikan `is_active = true` untuk masing-masing tipe.
- Pastikan `WhatsappSetting` aktif dan koneksi berhasil (menu pengaturan WhatsApp memiliki tombol uji koneksi).
- Simpan nomor WhatsApp user dalam format Indonesia. Sistem akan memformat otomatis ke `62xxxxxxxxxx` dan memvalidasi panjang nomor.
- Alur kirim pesan terjadi otomatis saat event terkait dipanggil oleh `WhatsappNotificationService`.

## Catatan
- Variabel dalam `{...}` diganti berdasarkan data runtime (misal `{user_name}` menjadi nama user). Pastikan setiap variabel tersedia saat menyusun data.
- Anda bisa menambahkan variabel kustom lewat field “Variabel Kustom” di form admin; penulisan di pesan tetap menggunakan `{nama_variabel}`.