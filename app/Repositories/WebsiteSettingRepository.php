<?php

namespace App\Repositories;

use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Builder;

class WebsiteSettingRepository implements WebsiteSettingRepositoryInterface
{
    /**
     * Mengembalikan query standar untuk penggunaan di Filament Resource.
     */
    public function filamentTableQuery(): Builder
    {
        return WebsiteSetting::query();
    }

    /**
     * Ambil default payment gateway ('midtrans' atau 'tripay'), fallback ke 'midtrans' jika invalid.
     */
    public function getDefaultPaymentGateway(): string
    {
        try {
            $value = WebsiteSetting::get('default_payment_gateway', 'midtrans');
            return in_array($value, ['midtrans', 'tripay'], true) ? $value : 'midtrans';
        } catch (\Throwable $e) {
            \Log::warning('Failed to get default payment gateway', ['error' => $e->getMessage()]);
            return 'midtrans';
        }
    }
}
