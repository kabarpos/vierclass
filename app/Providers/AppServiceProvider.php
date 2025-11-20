<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\Course;
use App\Models\CourseMentor;
use App\Models\SmtpSetting;
use App\Policies\CoursePolicy;
use App\Policies\CourseMentorPolicy;
use App\Policies\UserPolicy;
use App\Observers\TransactionObserver;
use App\Observers\QueryOptimizationObserver;
use App\Repositories\CourseRepository;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\RevenueRepository;
use App\Repositories\RevenueRepositoryInterface;
use App\Repositories\PaymentTempRepository;
use App\Repositories\PaymentTempRepositoryInterface;
use App\Repositories\DiscountRepository;
use App\Repositories\DiscountRepositoryInterface;
use App\Repositories\CourseMentorRepository;
use App\Repositories\CourseMentorRepositoryInterface;
use App\Repositories\SectionContentRepository;
use App\Repositories\SectionContentRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\SmtpSettingRepository;
use App\Repositories\SmtpSettingRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\RoleRepositoryInterface;
use App\Repositories\EmailMessageTemplateRepository;
use App\Repositories\EmailMessageTemplateRepositoryInterface;
use App\Repositories\MidtransSettingRepository;
use App\Repositories\MidtransSettingRepositoryInterface;
use App\Repositories\WhatsappSettingRepository;
use App\Repositories\WhatsappSettingRepositoryInterface;
use App\Repositories\WhatsappMessageTemplateRepository;
use App\Repositories\WhatsappMessageTemplateRepositoryInterface;
use App\Repositories\WebsiteSettingRepository;
use App\Repositories\WebsiteSettingRepositoryInterface;
use App\Repositories\PaymentReferenceRepository;
use App\Repositories\PaymentReferenceRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->singleton(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->singleton(RevenueRepositoryInterface::class, RevenueRepository::class);
        $this->app->singleton(PaymentTempRepositoryInterface::class, PaymentTempRepository::class);
        $this->app->singleton(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->singleton(CourseMentorRepositoryInterface::class, CourseMentorRepository::class);
        $this->app->singleton(SectionContentRepositoryInterface::class, SectionContentRepository::class);
        $this->app->singleton(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(SmtpSettingRepositoryInterface::class, SmtpSettingRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(EmailMessageTemplateRepositoryInterface::class, EmailMessageTemplateRepository::class);
        $this->app->singleton(MidtransSettingRepositoryInterface::class, MidtransSettingRepository::class);
        $this->app->singleton(WhatsappSettingRepositoryInterface::class, WhatsappSettingRepository::class);
        $this->app->singleton(WhatsappMessageTemplateRepositoryInterface::class, WhatsappMessageTemplateRepository::class);
        $this->app->singleton(WebsiteSettingRepositoryInterface::class, WebsiteSettingRepository::class);
        $this->app->singleton(\App\Repositories\TripaySettingRepositoryInterface::class, \App\Repositories\TripaySettingRepository::class);
        $this->app->singleton(PaymentReferenceRepositoryInterface::class, PaymentReferenceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Transaction::observe(TransactionObserver::class);
        
        // Boot query optimization observer for database monitoring
        QueryOptimizationObserver::boot();

        try {
            if (app()->environment(['local','development'])) {
                $cfg = config('app');
                $rawKey = $cfg['key'] ?? '';
                $key = str_starts_with($rawKey, 'base64:') ? base64_decode(substr($rawKey, 7)) : $rawKey;
                $cipher = $cfg['cipher'] ?? '';
                \Illuminate\Support\Facades\Log::info('Encryption key diagnostics', [
                    'cipher' => $cipher,
                    'key_length' => is_string($key) ? mb_strlen($key, '8bit') : 0,
                    'raw_key_prefix' => is_string($rawKey) ? substr($rawKey, 0, 15) : null,
                ]);
            }
        } catch (\Throwable $e) {}

        // Apply active SMTP configuration to mail config at runtime
        try {
            $smtp = SmtpSetting::getActive();
            if ($smtp && $smtp->isConfigured()) {
                Config::set('mail.default', 'smtp');
                // Pastikan MAIL_URL tidak menimpa konfigurasi SMTP dinamis
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.url', null);
                Config::set('mail.mailers.smtp.host', $smtp->host);
                Config::set('mail.mailers.smtp.port', (int) $smtp->port);
                Config::set('mail.mailers.smtp.username', $smtp->username);
                Config::set('mail.mailers.smtp.password', $smtp->password);
                // Infer encryption dari port bila tidak diisi untuk menghindari STARTTLS gagal saat server butuh SSL
                $enc = $smtp->encryption;
                if (empty($enc)) {
                    $enc = match ((int) $smtp->port) {
                        465 => 'ssl',
                        587 => 'tls',
                        default => null,
                    };
                }
                Config::set('mail.mailers.smtp.encryption', $enc);
                Config::set('mail.mailers.smtp.local_domain', parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
                Config::set('mail.from.address', $smtp->from_email);
                Config::set('mail.from.name', $smtp->from_name);
            }
        } catch (\Exception $e) {
            // Silent fail to avoid boot issues; logs handled elsewhere
        }

        // Register authorization policies
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(CourseMentor::class, CourseMentorPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\App\Models\Category::class, \App\Policies\CategoryPolicy::class);
        Gate::policy(\App\Models\Discount::class, \App\Policies\DiscountPolicy::class);
        Gate::policy(\App\Models\Transaction::class, \App\Policies\TransactionPolicy::class);
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);
        Gate::policy(\App\Models\SmtpSetting::class, \App\Policies\SmtpSettingPolicy::class);
        Gate::policy(\App\Models\WhatsappSetting::class, \App\Policies\WhatsappSettingPolicy::class);
        Gate::policy(\App\Models\WhatsappMessageTemplate::class, \App\Policies\WhatsappMessageTemplatePolicy::class);
        Gate::policy(\App\Models\EmailMessageTemplate::class, \App\Policies\EmailMessageTemplatePolicy::class);
        Gate::policy(\App\Models\MidtransSetting::class, \App\Policies\MidtransSettingPolicy::class);
        Gate::policy(\App\Models\WebsiteSetting::class, \App\Policies\WebsiteSettingPolicy::class);
        Gate::policy(\App\Models\SectionContent::class, \App\Policies\SectionContentPolicy::class);
        Gate::policy(\App\Models\CourseSection::class, \App\Policies\CourseSectionPolicy::class);

        // Allow admin & super-admin to bypass all checks (case-insensitive role names)
        Gate::before(function (User $user, string $ability) {
            $roleNames = $user->getRoleNames()->map(function ($name) {
                return strtolower($name);
            });
            if ($roleNames->contains('super-admin') || $roleNames->contains('admin')) {
                return true;
            }
            return null;
        });

        // Log authorization denials with context for production analysis
        Gate::after(function (User $user, string $ability, ?bool $result, array $arguments = []) {
            if ($result === false) {
                $route = optional(request()->route());
                Log::warning('Authorization denied', [
                    'user_id' => $user->id ?? null,
                    'ability' => $ability,
                    'arguments' => $arguments,
                    'roles' => $user->getRoleNames()->toArray(),
                    'route_name' => is_string(optional($route)->getName()) ? $route->getName() : null,
                    'route_uri' => optional($route)->uri(),
                ]);
            }
        });
    }
}
