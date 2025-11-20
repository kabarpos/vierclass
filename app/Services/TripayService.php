<?php

namespace App\Services;

use App\Repositories\TripaySettingRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TripayService
{
    public function __construct(
        protected TripaySettingRepositoryInterface $tripaySettingRepository
    ) {}

    public function getConfig(): array
    {
        $config = $this->tripaySettingRepository->getConfig();
        if (empty($config['apiKey']) || empty($config['privateKey']) || empty($config['merchantCode'])) {
            throw new RuntimeException('Konfigurasi Tripay tidak lengkap.');
        }
        return $config;
    }

    public function getBaseUrl(): string
    {
        $config = $this->getConfig();
        return $config['isProduction'] ? 'https://tripay.co.id/api' : 'https://tripay.co.id/api-sandbox';
    }

    /**
     * Membuat transaksi close payment Tripay.
     * Referensi signature: HMAC-SHA256(merchant_code + merchant_ref + amount, private_key).
     */
    public function createCloseTransaction(array $payload): array
    {
        $config = $this->getConfig();

        foreach (['method','merchant_ref','amount','customer_name','customer_email','customer_phone','order_items'] as $key) {
            if (!array_key_exists($key, $payload)) {
                throw new RuntimeException("Payload Tripay kurang field: {$key}");
            }
        }

        $signature = hash_hmac('sha256', $config['merchantCode'] . $payload['merchant_ref'] . $payload['amount'], $config['privateKey']);
        $payload['signature'] = $signature;

        $baseUrl = $this->getBaseUrl();

        Log::info('[Tripay] createCloseTransaction request', [
            'url' => $baseUrl . '/transaction/create',
            'payload' => $payload,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['apiKey'],
            'Accept' => 'application/json',
        ])->post($baseUrl . '/transaction/create', $payload);

        if (!$response->successful()) {
            $body = null;
            try { $body = $response->json(); } catch (\Throwable $t) { $body = null; }
            Log::error('[Tripay] createCloseTransaction gagal', [
                'status' => $response->status(),
                'body' => $body ?? $response->body(),
            ]);
            $detailMessage = is_array($body) ? ($body['message'] ?? json_encode($body)) : (string) $response->body();
            throw new RuntimeException('Tripay API error: ' . $response->status() . ' - ' . ($detailMessage ?: 'Unknown error'));
        }

        $json = $response->json();
        Log::info('[Tripay] createCloseTransaction response', $json ?? []);
        return $json ?? [];
    }

    /**
     * Verifikasi signature callback Tripay dari header X-Callback-Signature.
     * Beberapa implementasi menggunakan hex digest, sebagian menggunakan base64.
     * Kita verifikasi keduanya untuk robustnes.
     */
    public function verifyCallbackSignature(string $rawBody, ?string $headerSignature): bool
    {
        if (empty($headerSignature)) {
            return false;
        }

        $config = $this->getConfig();
        $hex = hash_hmac('sha256', $rawBody, $config['privateKey']);
        $bin = hash_hmac('sha256', $rawBody, $config['privateKey'], true);
        $b64 = base64_encode($bin);

        return hash_equals($headerSignature, $hex) || hash_equals($headerSignature, $b64);
    }
}
