<?php

namespace App\Repositories;

use App\Models\TripaySetting;
use Illuminate\Database\Eloquent\Builder;

class TripaySettingRepository implements TripaySettingRepositoryInterface
{
    public function getActive(): ?TripaySetting
    {
        return TripaySetting::query()->where('is_active', true)->orderByDesc('id')->first();
    }

    public function getConfig(): array
    {
        $active = $this->getActive();
        if ($active && $active->isValidConfig()) {
            return TripaySetting::getActiveConfig() ?? [];
        }

        // Fallback ke env/config
        return [
            'apiKey' => (string) config('tripay.api_key', env('TRIPAY_API_KEY')),
            'privateKey' => (string) config('tripay.private_key', env('TRIPAY_PRIVATE_KEY')),
            'merchantCode' => (string) config('tripay.merchant_code', env('TRIPAY_MERCHANT_CODE')),
            'isProduction' => (bool) config('tripay.is_production', env('TRIPAY_IS_PRODUCTION', false)),
        ];
    }

    public function filamentTableQuery(): Builder
    {
        return TripaySetting::query();
    }
}
