<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $queryCount = 0;
        $debug = (bool) config('app.debug') || (bool) env('PERFORMANCE_MONITOR_ENABLED', false);
        
        if ($debug) {
            DB::listen(function ($query) use (&$queryCount) {
                $queryCount++;
            });
        }
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $metrics = [
            'execution_time' => round(($endTime - $startTime) * 1000, 2), // milliseconds
            'memory_usage' => round(($endMemory - $startMemory) / 1024 / 1024, 2), // MB
            'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2), // MB
            'query_count' => $queryCount,
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'method' => $request->method(),
            'url' => $request->url(),
            'status_code' => $response->getStatusCode(),
            'timestamp' => now()->toISOString(),
        ];
        
        if ($debug) {
            $this->storeMetrics($metrics);
        }
        
        // Log performance issues
        $this->logPerformanceIssues($metrics);
        
        // Add performance headers for debugging
        if (config('app.debug')) {
            $response->headers->set('X-Execution-Time', $metrics['execution_time'] . 'ms');
            $response->headers->set('X-Memory-Usage', $metrics['memory_usage'] . 'MB');
            $response->headers->set('X-Query-Count', $metrics['query_count']);
        }
        
        return $response;
    }
    
    private function storeMetrics(array $metrics): void
    {
        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        $existingMetrics = $this->cacheGet($cacheKey, []);
        $existingMetrics[] = $metrics;
        
        // Keep only last 100 requests per hour
        if (count($existingMetrics) > 100) {
            $existingMetrics = array_slice($existingMetrics, -100);
        }
        
        $this->cachePut($cacheKey, $existingMetrics, now()->addHours(24));
        
        // Store aggregated metrics
        $this->storeAggregatedMetrics($metrics);
    }
    
    private function storeAggregatedMetrics(array $metrics): void
    {
        $cacheKey = 'performance_summary_' . date('Y-m-d-H');
        $summary = $this->cacheGet($cacheKey, [
            'total_requests' => 0,
            'avg_execution_time' => 0,
            'avg_memory_usage' => 0,
            'avg_query_count' => 0,
            'slow_requests' => 0,
            'error_requests' => 0,
        ]);
        
        $summary['total_requests']++;
        $summary['avg_execution_time'] = (($summary['avg_execution_time'] * ($summary['total_requests'] - 1)) + $metrics['execution_time']) / $summary['total_requests'];
        $summary['avg_memory_usage'] = (($summary['avg_memory_usage'] * ($summary['total_requests'] - 1)) + $metrics['memory_usage']) / $summary['total_requests'];
        $summary['avg_query_count'] = (($summary['avg_query_count'] * ($summary['total_requests'] - 1)) + $metrics['query_count']) / $summary['total_requests'];
        
        if ($metrics['execution_time'] > 1000) {
            $summary['slow_requests']++;
        }
        
        if ($metrics['status_code'] >= 400) {
            $summary['error_requests']++;
        }
        
        $this->cachePut($cacheKey, $summary, now()->addHours(24));

    }

    private function cacheGet(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::get($key, $default);
        } catch (\Throwable $e) {
            try { return Cache::store('file')->get($key, $default); } catch (\Throwable $e2) { return $default; }
        }
    }

    private function cachePut(string $key, mixed $value, mixed $ttl): void
    {
        try {
            Cache::put($key, $value, $ttl);
        } catch (\Throwable $e) {
            try { Cache::store('file')->put($key, $value, $ttl); } catch (\Throwable $e2) { }
        }
    }
    
    private function logPerformanceIssues(array $metrics): void
    {
        // Log slow requests (> 1 second)
        if ($metrics['execution_time'] > 1000) {
            Log::channel('daily')->warning('Slow Request Performance', [
                'type' => 'slow_request',
                'execution_time' => $metrics['execution_time'],
                'memory_usage' => $metrics['memory_usage'],
                'query_count' => $metrics['query_count'],
                'route' => $metrics['route'],
                'url' => $metrics['url'],
            ]);
        }
        
        // Log high memory usage (> 50MB)
        if ($metrics['memory_usage'] > 50) {
            Log::channel('daily')->warning('High Memory Usage', [
                'type' => 'high_memory',
                'memory_usage' => $metrics['memory_usage'],
                'peak_memory' => $metrics['peak_memory'],
                'route' => $metrics['route'],
                'url' => $metrics['url'],
            ]);
        }
        
        // Log excessive database queries (> 20 queries)
        if ($metrics['query_count'] > 20) {
            Log::channel('daily')->warning('Excessive Database Queries', [
                'type' => 'n_plus_one',
                'query_count' => $metrics['query_count'],
                'route' => $metrics['route'],
                'url' => $metrics['url'],
            ]);
        }
    }
}
