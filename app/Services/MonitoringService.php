<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MonitoringService
{
    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->getMemoryUsage(),
            'performance' => $this->getPerformanceMetrics(),
            'image_processing' => $this->checkImageProcessingHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }
    
    /**
     * Check database connectivity and performance
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            $connectionCount = DB::select("SHOW STATUS LIKE 'Threads_connected'")[0]->Value ?? 0;
            $maxConnections = DB::select("SHOW VARIABLES LIKE 'max_connections'")[0]->Value ?? 0;
            
            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'connections' => [
                    'current' => (int) $connectionCount,
                    'max' => (int) $maxConnections,
                    'usage_percentage' => round(($connectionCount / $maxConnections) * 100, 2),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check cache system health
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            $start = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => $retrieved === $testValue ? 'healthy' : 'unhealthy',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check storage health
     */
    private function checkStorageHealth(): array
    {
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedBytes = $totalBytes - $freeBytes;
            
            return [
                'status' => 'healthy',
                'free_space_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
                'total_space_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'used_space_gb' => round($usedBytes / 1024 / 1024 / 1024, 2),
                'usage_percentage' => round(($usedBytes / $totalBytes) * 100, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check GD/WebP image processing capability
     */
    private function checkImageProcessingHealth(): array
    {
        try {
            $gdLoaded = extension_loaded('gd');
            $hasImageWebp = function_exists('imagewebp');
            $webpSupported = $gdLoaded && $hasImageWebp && (imagetypes() & IMG_WEBP) === IMG_WEBP;
            $hasJpeg = function_exists('imagecreatefromjpeg');
            $hasPng = function_exists('imagecreatefrompng');

            $status = $webpSupported ? 'healthy' : ($gdLoaded ? 'warning' : 'unhealthy');

            return [
                'status' => $status,
                'gd_loaded' => $gdLoaded,
                'webp_supported' => $webpSupported,
                'functions' => [
                    'imagewebp' => $hasImageWebp,
                    'imagecreatefromjpeg' => $hasJpeg,
                    'imagecreatefrompng' => $hasPng,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Image processing health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get current memory usage
     */
    private function getMemoryUsage(): array
    {
        return [
            'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit'),
        ];
    }
    
    /**
     * Get performance metrics from cache
     */
    private function getPerformanceMetrics(): array
    {
        $currentHour = date('Y-m-d-H');
        $summary = Cache::get('performance_summary_' . $currentHour, []);
        
        return [
            'current_hour' => $summary,
            'last_24_hours' => $this->getLast24HoursMetrics(),
        ];
    }
    
    /**
     * Get last 24 hours performance metrics
     */
    private function getLast24HoursMetrics(): array
    {
        $metrics = [];
        
        for ($i = 0; $i < 24; $i++) {
            $hour = date('Y-m-d-H', strtotime("-{$i} hours"));
            $hourlyMetrics = Cache::get('performance_summary_' . $hour, []);
            
            if (!empty($hourlyMetrics)) {
                $metrics[$hour] = $hourlyMetrics;
            }
        }
        
        return $metrics;
    }
    
    /**
     * Send alert notification
     */
    public function sendAlert(string $type, string $message, array $data = []): void
    {
        $alertData = [
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'server' => gethostname(),
            'environment' => config('app.env'),
        ];
        
        // Log alert
        Log::channel('slack_critical')->critical($message, $alertData);
        
        // Store alert in cache for dashboard
        $this->storeAlert($alertData);
    }
    
    /**
     * Store alert in cache
     */
    private function storeAlert(array $alertData): void
    {
        $cacheKey = 'system_alerts_' . date('Y-m-d');
        $alerts = Cache::get($cacheKey, []);
        $alerts[] = $alertData;
        
        // Keep only last 100 alerts per day
        if (count($alerts) > 100) {
            $alerts = array_slice($alerts, -100);
        }
        
        Cache::put($cacheKey, $alerts, now()->addDays(7));
    }
    
    /**
     * Get recent alerts
     */
    public function getRecentAlerts(int $days = 1): array
    {
        $alerts = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayAlerts = Cache::get('system_alerts_' . $date, []);
            $alerts = array_merge($alerts, $dayAlerts);
        }
        
        return array_reverse($alerts); // Most recent first
    }
    
    /**
     * Check system thresholds and send alerts if needed
     */
    public function checkSystemThresholds(): void
    {
        $health = $this->getSystemHealth();
        
        // Check database response time
        if (isset($health['database']['response_time_ms']) && $health['database']['response_time_ms'] > 500) {
            $this->sendAlert('database_slow', 'Database response time is high', [
                'response_time' => $health['database']['response_time_ms'],
            ]);
        }
        
        // Check database connections
        if (isset($health['database']['connections']['usage_percentage']) && $health['database']['connections']['usage_percentage'] > 80) {
            $this->sendAlert('database_connections', 'Database connection usage is high', [
                'usage_percentage' => $health['database']['connections']['usage_percentage'],
            ]);
        }
        
        // Check storage usage
        if (isset($health['storage']['usage_percentage']) && $health['storage']['usage_percentage'] > 85) {
            $this->sendAlert('storage_full', 'Storage usage is high', [
                'usage_percentage' => $health['storage']['usage_percentage'],
            ]);
        }
        
        // Check memory usage
        if (isset($health['memory']['current_mb']) && $health['memory']['current_mb'] > 500) {
            $this->sendAlert('memory_high', 'Memory usage is high', [
                'current_mb' => $health['memory']['current_mb'],
            ]);
        }
    }
}
