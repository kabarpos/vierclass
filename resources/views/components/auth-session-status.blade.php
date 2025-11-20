@props(['status'])

@php
    $messagesMap = [
        'profile-updated' => __('Profil berhasil diperbarui.'),
        'verification-link-sent' => __('Link verifikasi telah dikirim ke email Anda.'),
        'password-updated' => __('Kata sandi berhasil diperbarui.'),
    ];
    $displayMessage = $status && isset($messagesMap[$status]) ? $messagesMap[$status] : $status;
@endphp

@if ($displayMessage)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $displayMessage }}
    </div>
@endif
