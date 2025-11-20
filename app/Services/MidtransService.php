<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;
use App\Models\MidtransSetting;
use Exception;

class MidtransService {
    
    private ?MidtransSetting $config = null;
    
    public function __construct()
    {
        $this->loadConfiguration();
    }
    
    /**
     * Load Midtrans configuration with flexible environment switching
     */
    private function loadConfiguration(): void
    {
        try {
            // Try to get active configuration from database first
            $this->config = MidtransSetting::getActiveConfig();
            
            if ($this->config && $this->config->isValidConfig()) {
                // Use database configuration
                Config::$serverKey = $this->config->server_key;
                Config::$isProduction = $this->config->is_production;
                Config::$isSanitized = $this->config->is_sanitized;
                Config::$is3ds = $this->config->is_3ds;
                
                Log::info('Midtrans configuration loaded from database', [
                    'environment' => $this->config->is_production ? 'production' : 'sandbox',
                    'merchant_id' => $this->config->merchant_id,
                ]);
            } else {
                // Use flexible environment configuration from config
                $this->loadEnvironmentConfig();
                
                Log::warning('Midtrans configuration loaded from env (fallback)', [
                    'reason' => $this->config ? 'invalid_config' : 'no_active_config',
                    'environment' => config('midtrans.environment')
                ]);
            }
        } catch (Exception $e) {
            // If database is not available, fallback to env
            $this->loadEnvironmentConfig();
            
            Log::error('Failed to load Midtrans config from database, using env fallback', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Load configuration from environment with flexible switching
     */
    private function loadEnvironmentConfig(): void
    {
        $environment = config('midtrans.environment', 'sandbox');
        
        if ($environment === 'production') {
            // Use production configuration
            Config::$serverKey = config('midtrans.production.serverKey');
            Config::$isProduction = true;
        } else {
            // Use sandbox configuration (default)
            Config::$serverKey = config('midtrans.sandbox.serverKey');
            Config::$isProduction = false;
        }
        
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
        
        Log::info('Midtrans environment configuration loaded', [
            'environment' => $environment,
            'is_production' => Config::$isProduction
        ]);
    }
    
    /**
     * Get current configuration info
     */
    public function getConfigInfo(): array
    {
        return [
            'source' => $this->config ? 'database' : 'environment',
            'environment' => Config::$isProduction ? 'production' : 'sandbox',
            'merchant_id' => $this->config?->merchant_id ?? 'N/A',
            'has_server_key' => !empty(Config::$serverKey),
            'is_sanitized' => Config::$isSanitized,
            'is_3ds' => Config::$is3ds,
        ];
    }
    
    /**
     * Test connection to Midtrans API
     */
    public function testConnection(): array
    {
        try {
            // Create a test transaction to validate API keys
            $testParams = [
                'transaction_details' => [
                    'order_id' => 'TEST-CONNECTION-' . time(),
                    'gross_amount' => 1000,
                ],
                'customer_details' => [
                    'first_name' => 'Test',
                    'email' => 'test@connection.local',
                ],
            ];
            
            $token = Snap::getSnapToken($testParams);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'token_created' => !empty($token),
                'config_info' => $this->getConfigInfo(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'config_info' => $this->getConfigInfo(),
            ];
        }
    }

    public function createSnapToken(array $params): string
    {
        try {
            // Ensure configuration is loaded
            if (empty(Config::$serverKey)) {
                throw new Exception('Midtrans server key not configured');
            }
            
            return Snap::getSnapToken($params);
        } catch (Exception $e) {
            Log::error('Failed to create Snap token: ' . $e->getMessage(), [
                'config_info' => $this->getConfigInfo(),
                'params' => $params
            ]);
            throw $e;
        }
    }

    public function handleNotification(): array
    {
        try {
            $notification = new Notification();
            return [
                'order_id' => $notification->order_id,
                'transaction_status' => $notification->transaction_status,
                'gross_amount' => $notification->gross_amount,
                'status_code' => $notification->status_code,
                'signature_key' => $notification->signature_key,
                'custom_field1' => $notification->custom_field1, // User ID
                'custom_field2' => $notification->custom_field2, // Course ID
                'custom_field3' => $notification->custom_field3 ?? null, // Purchase type (course)
                'custom_expiry' => $notification->custom_expiry ?? null, // Discount data
            ];
        } catch (Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage(), [
                'config_info' => $this->getConfigInfo()
            ]);
            throw $e;
        }
    }

    /**
     * Verify Midtrans notification signature
     */
    public function verifySignature(array $notification): bool
    {
        try {
            if (empty(Config::$serverKey)) {
                Log::error('Midtrans server key is not configured');
                return false;
            }
            $rawSignature = hash('sha512', $notification['order_id'] . $notification['status_code'] . $notification['gross_amount'] . Config::$serverKey);
            $isValid = hash_equals($rawSignature, $notification['signature_key'] ?? '');
            if (!$isValid) {
                Log::warning('Invalid Midtrans signature detected', [
                    'order_id' => $notification['order_id'] ?? null,
                ]);
            }
            return $isValid;
        } catch (\Throwable $e) {
            Log::error('Error verifying Midtrans signature', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get Midtrans client key for Snap JS (from DB if valid, else env config)
     */
    public function getClientKey(): ?string
    {
        try {
            if ($this->config && $this->config->isValidConfig() && !empty($this->config->client_key)) {
                return $this->config->client_key;
            }

            // Fallback ke konfigurasi environment yang fleksibel
            $environment = config('midtrans.environment', 'sandbox');
            if ($environment === 'production') {
                return config('midtrans.production.clientKey');
            }
            return config('midtrans.sandbox.clientKey');
        } catch (\Throwable $e) {
            Log::error('Failed to get Midtrans client key', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if current environment is production
     */
    public function isProduction(): bool
    {
        try {
            // Prioritaskan nilai yang sudah di-set oleh Config
            if (isset(Config::$isProduction)) {
                return (bool) Config::$isProduction;
            }
            return (config('midtrans.environment', 'sandbox') === 'production');
        } catch (\Throwable $e) {
            Log::warning('Failed to determine Midtrans environment, defaulting to sandbox', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get Snap JS URL based on environment
     */
    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
