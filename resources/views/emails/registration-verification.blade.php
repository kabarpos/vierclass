<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .btn { display:inline-block; padding:10px 16px; background:#16a34a; color:#fff; text-decoration:none; border-radius:6px; }
        .container { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verifikasi Email Akun Anda</h2>
        <p>Halo {{ $user->name }},</p>
        <p>Terima kasih telah mendaftar di {{ \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') }}. Untuk mengaktifkan akun, silakan verifikasi email Anda dengan menekan tombol di bawah:</p>
        <p>
            <a class="btn" href="{{ route('email.verification.verify', ['id' => $user->id, 'token' => $user->verification_token]) }}">Verifikasi Email</a>
        </p>
        <p>Jika tombol tidak berfungsi, salin dan buka link berikut di browser Anda:</p>
        <p>{{ route('email.verification.verify', ['id' => $user->id, 'token' => $user->verification_token]) }}</p>
        <p>Catatan: Akun akan aktif setelah Anda memverifikasi email dan nomor WhatsApp.</p>
        <p>Salam,<br>{{ \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') }}</p>
    </div>
</body>
</html>