<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DripsenderService
{
    protected $whatsappSetting;

    public function __construct()
    {
        $this->whatsappSetting = WhatsappSetting::getActive();
    }

    /**
     * Check if WhatsApp service is available and configured
     */
    public function isAvailable(): bool
    {
        return $this->whatsappSetting && $this->whatsappSetting->isConfigured();
    }

    /**
     * Send WhatsApp message via Dripsender API
     */
    public function sendMessage(string $phone, string $message, ?string $mediaUrl = null): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('WhatsApp service is not properly configured');
        }

        // Format phone number (ensure starts with country code without +)
        $formattedPhone = $this->formatPhoneNumber($phone);

        $payload = [
            'api_key' => $this->whatsappSetting->api_key,
            'phone' => $formattedPhone,
            'text' => $message,
        ];

        // Add media URL if provided
        if ($mediaUrl) {
            $payload['media_url'] = $mediaUrl;
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying() // Handle SSL issues in development
                ->post($this->whatsappSetting->getApiEndpoint('send'), $payload);

            Log::info('Dripsender API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $formattedPhone
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'response' => $response->body()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to send message',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Dripsender API Error', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone,
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'API call failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send message to group
     */
    public function sendToGroup(string $groupId, string $message, ?string $mediaUrl = null): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('WhatsApp service is not properly configured');
        }

        $payload = [
            'api_key' => $this->whatsappSetting->api_key,
            'group_id' => $groupId,
            'text' => $message,
        ];

        // Add media URL if provided
        if ($mediaUrl) {
            $payload['media_url'] = $mediaUrl;
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying() // Handle SSL issues in development
                ->post($this->whatsappSetting->getApiEndpoint('send'), $payload);

            Log::info('Dripsender Group API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'group_id' => $groupId
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent to group successfully',
                    'response' => $response->body()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to send message to group',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Dripsender Group API Error', [
                'error' => $e->getMessage(),
                'group_id' => $groupId,
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'API call failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test API connection with detailed feedback
     */
    public function testConnection(): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'WhatsApp service belum dikonfigurasi. Periksa API key dan status aktif.',
                'details' => [
                    'api_key' => $this->whatsappSetting ? 'Ada' : 'Tidak ada',
                    'is_active' => $this->whatsappSetting ? $this->whatsappSetting->is_active : false,
                    'base_url' => $this->whatsappSetting ? $this->whatsappSetting->base_url : 'Tidak ada'
                ]
            ];
        }

        try {
            // Test API endpoint with a simple request
            $response = Http::timeout(10)
                ->withoutVerifying() // Handle SSL issues in development
                ->withHeaders(['api-key' => $this->whatsappSetting->api_key])
                ->get($this->whatsappSetting->getApiEndpoint('lists/'));

            Log::info('Dripsender Connection Test', [
                'status' => $response->status(),
                'body' => $response->body(),
                'api_endpoint' => $this->whatsappSetting->getApiEndpoint('lists/')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil! API key valid dan aktif.',
                    'details' => [
                        'status_code' => $response->status(),
                        'endpoint' => $this->whatsappSetting->getApiEndpoint('lists/'),
                        'response_data' => is_array($data) ? count($data) . ' lists ditemukan' : 'Data diterima'
                    ]
                ];
            } else {
                $errorMessage = 'HTTP ' . $response->status();
                $responseBody = $response->body();
                
                if ($response->status() === 401) {
                    $errorMessage = 'API key tidak valid atau tidak memiliki akses';
                } elseif ($response->status() === 403) {
                    $errorMessage = 'API key tidak memiliki permission untuk mengakses endpoint ini';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Endpoint tidak ditemukan. Periksa base URL';
                } elseif ($response->status() >= 500) {
                    $errorMessage = 'Server Dripsender sedang bermasalah. Coba lagi nanti';
                }

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => [
                        'status_code' => $response->status(),
                        'endpoint' => $this->whatsappSetting->getApiEndpoint('lists/'),
                        'error_response' => $responseBody
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Dripsender Connection Test Exception', [
                'error' => $e->getMessage(),
                'api_endpoint' => $this->whatsappSetting->getApiEndpoint('lists/')
            ]);

            $errorMessage = 'Tidak dapat terhubung ke Dripsender';
            
            if (str_contains($e->getMessage(), 'timeout')) {
                $errorMessage = 'Koneksi timeout. Periksa koneksi internet atau base URL';
            } elseif (str_contains($e->getMessage(), 'Could not resolve host')) {
                $errorMessage = 'Tidak dapat menemukan server Dripsender. Periksa base URL';
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'details' => [
                    'exception' => $e->getMessage(),
                    'endpoint' => $this->whatsappSetting ? $this->whatsappSetting->getApiEndpoint('lists/') : 'N/A'
                ]
            ];
        }
    }

    /**
     * Get all lists
     */
    public function getLists(): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('WhatsApp service is not properly configured');
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying() // Handle SSL issues in development
                ->withHeaders(['api-key' => $this->whatsappSetting->api_key])
                ->get($this->whatsappSetting->getApiEndpoint('lists/'));

            Log::info('Dripsender Get Lists Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get lists',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Dripsender Get Lists Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'API call failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get contacts from specific list
     */
    public function getListContacts(string $listId): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception('WhatsApp service is not properly configured');
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying() // Handle SSL issues in development
                ->withHeaders(['api-key' => $this->whatsappSetting->api_key])
                ->get($this->whatsappSetting->getApiEndpoint("lists/{$listId}"));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get list contacts',
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Dripsender Get List Contacts Error', [
                'error' => $e->getMessage(),
                'list_id' => $listId
            ]);

            return [
                'success' => false,
                'message' => 'API call failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to Indonesian format without + prefix
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add 62 prefix
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        
        // Indonesian phone number should start with 62 and have 10-13 digits total
        return preg_match('/^62[0-9]{8,11}$/', $formattedPhone);
    }
}