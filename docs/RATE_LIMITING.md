# Rate Limiting Implementation

Sistem rate limiting telah diimplementasikan untuk melindungi aplikasi dari abuse dan serangan brute force.

## Fitur yang Diimplementasikan

### 1. Custom Rate Limiters

#### Login Rate Limiter
- **Lokasi**: `app/Http/Middleware/LoginRateLimit.php`
- **Batasan**: 5 percobaan per 15 menit per IP
- **Fitur**:
  - Tracking IP address
  - Logging aktivitas mencurigakan
  - Reset counter setelah login berhasil
  - Respons JSON untuk API

#### Named Rate Limiters
- **Registration**: 3 percobaan per 60 menit
- **Password Reset**: 3 percobaan per 60 menit
- **Payment**: 10 percobaan per 60 menit
- **API**: 60 percobaan per 1 menit
- **Webhook**: 60 percobaan per 1 menit

### 2. Global Rate Limit Logger

- **Lokasi**: `app/Http/Middleware/RateLimitLogger.php`
- **Fungsi**: Mencatat semua aktivitas rate limiting
- **Data yang dicatat**:
  - IP address
  - User agent
  - Route dan method
  - Status (berhasil/terlampaui)
  - Detail rate limit dari header
  - Informasi user (jika login)

### 3. Konfigurasi Terpusat

- **Lokasi**: `config/rate_limiting.php`
- **Fitur**:
  - Pengaturan batasan untuk setiap endpoint
  - IP whitelist
  - Toggle logging dan headers
  - Konfigurasi yang mudah diubah

### 4. Service Provider

- **Lokasi**: `app/Providers/RateLimitServiceProvider.php`
- **Fungsi**: Mendefinisikan custom rate limiters
- **Registrasi**: Otomatis di `bootstrap/providers.php`

### 5. Monitoring Command

- **Command**: `php artisan rate-limit:monitor`
- **Fungsi**:
  - Menampilkan status semua rate limiters
  - Menampilkan konfigurasi aktif
  - Membersihkan data expired dengan `--clear`

## Implementasi pada Routes

### Authentication Routes (`routes/auth.php`)

```php
// Login dengan custom middleware dan named limiter
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:login', 'login.rate.limit'])
    ->name('login');

// Registration
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware(['guest', 'throttle:registration'])
    ->name('register');

// Password reset
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:password-reset'])
    ->name('password.email');
```

### Web Routes (`routes/web.php`)

```php
// Payment notification
Route::post('/booking/payment/midtrans/notification', [PaymentController::class, 'paymentMidtransNotification'])
    ->middleware('throttle:webhook')
    ->name('paymentMidtransNotification');

// Course payment
Route::post('/payment_store_courses_midtrans', [FrontController::class, 'payment_store_courses_midtrans'])
    ->middleware(['auth', 'throttle:payment'])
    ->name('front.payment_store_courses_midtrans');

// API endpoints
Route::post('/api/lesson-progress', [LessonProgressController::class, 'store'])
    ->middleware(['auth', 'throttle:api'])
    ->name('lesson.progress.store');
```

## Konfigurasi

### Rate Limiting Config (`config/rate_limiting.php`)

```php
return [
    'login' => [
        'max_attempts' => 5,
        'decay_minutes' => 15,
    ],
    'registration' => [
        'max_attempts' => 3,
        'decay_minutes' => 60,
    ],
    // ... konfigurasi lainnya
    
    'ip_whitelist' => [
        // '127.0.0.1',
        // '::1',
    ],
    
    'log_events' => true,
    'include_headers' => true,
];
```

### Middleware Registration (`bootstrap/app.php`)

```php
$middleware->alias([
    'login.rate.limit' => \App\Http\Middleware\LoginRateLimit::class,
    'rate.limit.logger' => \App\Http\Middleware\RateLimitLogger::class,
]);

// Global middleware
$middleware->append(\App\Http\Middleware\RateLimitLogger::class);
```

## Monitoring dan Maintenance

### Melihat Status Rate Limiters

```bash
php artisan rate-limit:monitor
```

### Membersihkan Data Expired

```bash
php artisan rate-limit:monitor --clear
```

### Log Files

- **Rate limiting events**: `storage/logs/laravel.log`
- **Login attempts**: Dicatat dengan context `rate_limiting`
- **Suspicious activity**: Dicatat dengan level `warning`

## Security Features

### 1. IP-based Tracking
- Menggunakan IP address untuk tracking
- Support untuk proxy headers (X-Forwarded-For)
- Whitelist IP untuk bypass

### 2. Progressive Penalties
- Waktu cooldown yang meningkat
- Reset otomatis setelah periode tertentu
- Logging untuk analisis pattern

### 3. Response Headers
- `X-RateLimit-Limit`: Batas maksimum
- `X-RateLimit-Remaining`: Sisa percobaan
- `X-RateLimit-Reset`: Waktu reset

### 4. Custom Responses
- JSON response untuk API endpoints
- HTML response untuk web endpoints
- Informative error messages

## Best Practices

### 1. Monitoring
- Pantau log secara berkala
- Set up alerts untuk aktivitas mencurigakan
- Review konfigurasi berdasarkan traffic pattern

### 2. Konfigurasi
- Sesuaikan batasan berdasarkan kebutuhan bisnis
- Test thoroughly sebelum production
- Dokumentasikan perubahan konfigurasi

### 3. Performance
- Gunakan Redis untuk cache yang lebih cepat
- Monitor memory usage
- Clean up expired data secara berkala

### 4. Security
- Jangan expose rate limit details ke public
- Gunakan HTTPS untuk semua endpoints
- Implement additional security layers

## Troubleshooting

### Rate Limit Tidak Bekerja
1. Periksa middleware registration
2. Pastikan cache driver berfungsi
3. Check log untuk error messages

### Performance Issues
1. Monitor cache hit ratio
2. Optimize cache configuration
3. Consider using Redis

### False Positives
1. Review IP whitelist
2. Adjust rate limits
3. Check for shared IP addresses

## Future Enhancements

1. **Dynamic Rate Limiting**: Adjust limits based on user behavior
2. **Geolocation-based Rules**: Different limits for different regions
3. **Machine Learning**: Detect anomalous patterns
4. **Dashboard**: Web interface for monitoring
5. **API Rate Limiting**: More granular API controls