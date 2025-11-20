<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_name',
        'from_email',
        'is_active',
        'api_endpoint',
        'api_login',
        'api_token',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Get active SMTP setting (singleton pattern)
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Check if SMTP is properly configured and active
     */
    public function isConfigured(): bool
    {
        return $this->is_active
            && !empty($this->host)
            && !empty($this->port)
            && !empty($this->username)
            && !empty($this->password)
            && !empty($this->from_email)
            && !empty($this->from_name);
    }
}