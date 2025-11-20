<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'base_url',
        'is_active',
        'webhook_url',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Get active WhatsApp setting (singleton pattern)
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Check if WhatsApp service is properly configured and active
     */
    public function isConfigured(): bool
    {
        return $this->is_active && !empty($this->api_key) && !empty($this->base_url);
    }

    /**
     * Get the Dripsender API endpoint URL
     */
    public function getApiEndpoint(string $endpoint = 'send'): string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');
    }
}
