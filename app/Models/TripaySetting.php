<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripaySetting extends Model
{
    use HasFactory;

    protected $table = 'tripay_settings';

    protected $fillable = [
        'api_key',
        'private_key',
        'merchant_code',
        'is_production',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_production' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function getActiveConfig(): ?array
    {
        $config = self::query()->where('is_active', true)->orderByDesc('id')->first();
        if (!$config) {
            return null;
        }
        return [
            'apiKey' => trim((string) $config->api_key),
            'privateKey' => trim((string) $config->private_key),
            'merchantCode' => trim((string) $config->merchant_code),
            'isProduction' => (bool) $config->is_production,
        ];
    }

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = is_string($value) ? trim($value) : $value;
    }

    public function setPrivateKeyAttribute($value): void
    {
        $this->attributes['private_key'] = is_string($value) ? trim($value) : $value;
    }

    public function setMerchantCodeAttribute($value): void
    {
        $this->attributes['merchant_code'] = is_string($value) ? trim($value) : $value;
    }

    public function isValidConfig(): bool
    {
        return !empty($this->api_key) && !empty($this->private_key) && !empty($this->merchant_code);
    }

    public function getEnvironmentTextAttribute(): string
    {
        return $this->is_production ? 'Production' : 'Sandbox';
    }

    public function getMaskedApiKeyAttribute(): string
    {
        $key = (string) $this->api_key;
        return strlen($key) > 6 ? substr($key, 0, 3) . str_repeat('*', max(strlen($key) - 6, 0)) . substr($key, -3) : $key;
    }

    public function getMaskedPrivateKeyAttribute(): string
    {
        $key = (string) $this->private_key;
        return strlen($key) > 6 ? substr($key, 0, 3) . str_repeat('*', max(strlen($key) - 6, 0)) . substr($key, -3) : $key;
    }
}

