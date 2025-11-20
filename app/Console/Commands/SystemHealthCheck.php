<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:health-check {--alert : Send alerts if thresholds are exceeded}';

    /**
     * The console command description.
     */
    protected $description = 'Perform system health check and optionally send alerts';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoringService): int
    {
        $this->info('Starting system health check...');
        
        try {
            $health = $monitoringService->getSystemHealth();
            
            $this->displayHealthStatus($health);
            
            if ($this->option('alert')) {
                $this->info('Checking system thresholds...');
                $monitoringService->checkSystemThresholds();
                $this->info('Threshold check completed.');
            }
            
            // Log health check
            Log::channel('daily')->info('System health check completed', $health);
            
            $this->info('System health check completed successfully.');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('System health check failed: ' . $e->getMessage());
            Log::error('System health check failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
    
    private function displayHealthStatus(array $health): void
    {
        $this->info('=== System Health Status ===');
        
        // Database status
        $dbStatus = $health['database']['status'] ?? 'unknown';
        $dbIcon = $dbStatus === 'healthy' ? '✅' : '❌';
        $this->line("Database: {$dbIcon} {$dbStatus}");
        
        if (isset($health['database']['response_time_ms'])) {
            $this->line("  Response Time: {$health['database']['response_time_ms']}ms");
        }
        
        if (isset($health['database']['connections'])) {
            $conn = $health['database']['connections'];
            $this->line("  Connections: {$conn['current']}/{$conn['max']} ({$conn['usage_percentage']}%)");
        }
        
        // Cache status
        $cacheStatus = $health['cache']['status'] ?? 'unknown';
        $cacheIcon = $cacheStatus === 'healthy' ? '✅' : '❌';
        $this->line("Cache: {$cacheIcon} {$cacheStatus}");
        
        if (isset($health['cache']['response_time_ms'])) {
            $this->line("  Response Time: {$health['cache']['response_time_ms']}ms");
        }
        
        // Storage status
        $storageStatus = $health['storage']['status'] ?? 'unknown';
        $storageIcon = $storageStatus === 'healthy' ? '✅' : '❌';
        $this->line("Storage: {$storageIcon} {$storageStatus}");
        
        if (isset($health['storage']['usage_percentage'])) {
            $storage = $health['storage'];
            $this->line("  Usage: {$storage['used_space_gb']}GB / {$storage['total_space_gb']}GB ({$storage['usage_percentage']}%)");
        }
        
        // Memory usage
        if (isset($health['memory'])) {
            $memory = $health['memory'];
            $this->line("Memory: {$memory['current_mb']}MB (Peak: {$memory['peak_mb']}MB, Limit: {$memory['limit']})");
        }

        // Image processing
        if (isset($health['image_processing'])) {
            $img = $health['image_processing'];
            $this->line("Image Processing (GD/WebP): {$img['status']}");
            $this->line("  GD Loaded: " . (($img['gd_loaded'] ?? false) ? 'yes' : 'no'));
            $this->line("  WebP Supported: " . (($img['webp_supported'] ?? false) ? 'yes' : 'no'));
            if (isset($img['functions'])) {
                $this->line("  Functions: imagewebp=" . ($img['functions']['imagewebp'] ? 'yes' : 'no') . ", jpeg=" . ($img['functions']['imagecreatefromjpeg'] ? 'yes' : 'no') . ", png=" . ($img['functions']['imagecreatefrompng'] ? 'yes' : 'no'));
            }
        }
        
        // Performance metrics
        if (isset($health['performance']['current_hour']) && !empty($health['performance']['current_hour'])) {
            $perf = $health['performance']['current_hour'];
            $this->line("Performance (Current Hour):");
            $this->line("  Total Requests: {$perf['total_requests']}");
            $this->line("  Avg Response Time: " . round($perf['avg_execution_time'], 2) . "ms");
            $this->line("  Avg Memory Usage: " . round($perf['avg_memory_usage'], 2) . "MB");
            $this->line("  Avg Query Count: " . round($perf['avg_query_count'], 2));
            $this->line("  Slow Requests: {$perf['slow_requests']}");
            $this->line("  Error Requests: {$perf['error_requests']}");
        }
        
        $this->info('=== End Health Status ===');
    }
}
