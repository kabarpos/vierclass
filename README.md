# LMS-Ebook Drip - Per-Course Purchase System

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-4.0-FDB62F?style=flat)](https://filamentphp.com)

A modern Laravel-based Learning Management System with **per-course purchase model**, Midtrans payment integration, and comprehensive notification system.

## 🎯 Business Model

### Per-Course Purchase System
- **Individual Course Purchases**: Buy only the courses you need
- **Lifetime Access**: Once purchased, courses are yours forever
- **No Subscriptions**: No recurring fees or time limitations
- **Flexible Pricing**: Each course has its own price point

### Key Features
- 🛒 Individual course purchasing
- 💳 Midtrans payment integration
- 📧 Email & WhatsApp notifications
- 🎓 Lifetime course access
- 📊 Admin analytics & reporting
- 📱 Responsive design
- 🔐 Role-based access control

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/SQLite

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd lms-ebook

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Frontend build
npm run dev

# Start development server
php artisan serve --port=8000
```

## 📚 System Architecture

### Core Components
- **Models**: Course, User, Transaction, WhatsappMessageTemplate
- **Services**: PaymentService, CourseService, WhatsappNotificationService
- **Middleware**: CheckCourseAccess for course ownership validation
- **Admin Panel**: Filament-powered admin interface

### Database Schema
```sql
-- Courses table (with pricing)
courses: id, name, slug, price, thumbnail, description, ...

-- Transactions table (course purchases only)
transactions: id, user_id, course_id, booking_trx_id, is_paid, ...

-- Users table (with course relationships)
users: id, name, email, whatsapp_number, ...
```

## 🛍️ Course Purchase Flow

### 1. Course Discovery
```php
// Browse courses with pricing
Route::get('/courses', [FrontController::class, 'courses']);
```

### 2. Course Purchase
```php
// Initiate purchase
Route::post('/course/{course}/checkout', [FrontController::class, 'courseCheckout']);
```

### 3. Payment Processing
```php
// Midtrans payment notification
Route::post('/booking/payment/midtrans/notification', 
    [FrontController::class, 'paymentMidtransNotification']);
```

### 4. Course Access
```php
// Access purchased course
Route::get('/course/{course}', [FrontController::class, 'course'])
    ->middleware('check.course.access');
```

## 🔧 Configuration

### Payment Gateway (Midtrans)
```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
```

### WhatsApp Notifications (Dripsender)
```env
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🧪 Testing

### Email Testing
```bash
# Test course purchase email notification
php artisan test:course-email {user_id} {course_id}

# Example:
php artisan test:course-email 1 1
# ✅ Output: Course purchase email sent successfully!
```

### WhatsApp Testing
```bash
# Test course purchase WhatsApp notification
php artisan test:course-whatsapp {user_id} {course_id}

# Example:
php artisan test:course-whatsapp 1 1
# ✅ Output: Course purchase WhatsApp notification sent successfully!
```

### Course Purchase Testing
```bash
# Analyze course purchase data
php artisan analyze:course-purchases

# Detailed analysis with CSV export
php artisan analyze:course-purchases --detailed --export-csv
```

### Unit Tests
```bash
php artisan test
```

## 📋 Admin Panel

Access the admin panel at `/admin` with admin credentials.

### Features
- **Course Management**: Set prices, track sales
- **Transaction Management**: Monitor purchases
- **User Management**: View customer data
- **WhatsApp Templates**: Customize notifications
- **Analytics**: Revenue and engagement tracking

## 🔄 Course Purchase System

This system operates with a **per-course purchase model only**:
- All transactions are processed as individual course purchases
- Users buy courses one at a time with lifetime access
- No subscription or recurring payment options

### ✅ Testing Results
- **Email Notifications**: ✅ Successfully tested
- **WhatsApp Notifications**: ✅ Successfully tested
- **Payment Flow**: ✅ Fully implemented and ready

## 📖 Documentation

- **[Full System Documentation](./PER_COURSE_PURCHASE_DOCUMENTATION.md)**
- **[WhatsApp Integration Guide](./WHATSAPP_DRIPSENDER_IMPLEMENTATION.md)**
- **[API Documentation](./docs/api.md)** (if available)

## 🛡️ Security Features

- Course ownership validation
- Secure payment processing
- CSRF protection
- Rate limiting
