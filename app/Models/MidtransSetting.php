<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MidtransSetting extends Model
{
    protected $fillable = [
        'server_key',
        'client_key',
        'merchant_id',
        'is_production',
        'is_sanitized',
        'is_3ds',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_production' => 'boolean',
        'is_sanitized' => 'boolean',
        'is_3ds' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the active Midtrans configuration
     */
    public static function getActiveConfig(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get server key with environment prefix if needed
     */
    protected function serverKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => trim($value)
        );
    }

    /**
     * Get client key with environment prefix if needed
     */
    protected function clientKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => trim($value)
        );
    }

    /**
     * Check if configuration is valid
     */
    public function isValidConfig(): bool
    {
        return !empty($this->server_key) && !empty($this->client_key);
    }

    /**
     * Get environment text for display
     */
    public function getEnvironmentTextAttribute(): string
    {
        return $this->is_production ? 'Production' : 'Sandbox';
    }

    /**
     * Get masked server key for display
     */
    public function getMaskedServerKeyAttribute(): string
    {
        if (empty($this->server_key)) {
            return 'Not Set';
        }
        
        return substr($this->server_key, 0, 15) . '***' . substr($this->server_key, -4);
    }

    /**
     * Get masked client key for display
     */
    public function getMaskedClientKeyAttribute(): string
    {
        if (empty($this->client_key)) {
            return 'Not Set';
        }
        
        return substr($this->client_key, 0, 15) . '***' . substr($this->client_key, -4);
    }
}
