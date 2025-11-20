<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QueryOptimizationObserver
{
    /**
     * Slow query threshold in seconds
     */
    private const SLOW_QUERY_THRESHOLD = 1.0;
    
    /**
     * High memory usage threshold in MB
     */
    private const HIGH_MEMORY_THRESHOLD = 50;
    
    /**
     * Maximum queries per request threshold
     */
    private const MAX_QUERIES_THRESHOLD = 50;

    /**
     * Boot the observer
     */
    public static function boot(): void
    {
        // Listen for database queries
        DB::listen(function ($query) {
            static::analyzeQuery($query);
        });
    }

    /**
     * Analyze individual query performance
     */
    public static function analyzeQuery($query): void
    {
        $executionTime = $query->time / 1000; // Convert to seconds
        $sql = $query->sql;
        $bindings = $query->bindings;
        
        // Track query statistics
        static::trackQueryStatistics($sql, $executionTime);
        
        // Check for slow queries
        if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
            static::handleSlowQuery($sql, $bindings, $executionTime);
        }
        
        // Check for N+1 queries
        static::detectNPlusOneQueries($sql);
        
        // Check for inefficient queries
        static::detectInefficientQueries($sql, $executionTime);
    }

    /**
     * Track query statistics for monitoring
     */
    private static function trackQueryStatistics(string $sql, float $executionTime): void
    {
        $cacheKey = 'query_stats_' . date('Y-m-d-H');
        
        $stats = Cache::get($cacheKey, [
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries' => 0,
            'query_types' => [],
        ]);
        
        $stats['total_queries']++;
        $stats['total_time'] += $executionTime;
        
        if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
            $stats['slow_queries']++;
        }
        
        // Track query types
        $queryType = static::getQueryType($sql);
        $stats['query_types'][$queryType] = ($stats['query_types'][$queryType] ?? 0) + 1;
        
        Cache::put($cacheKey, $stats, now()->addHours(2));
    }

    /**
     * Handle slow query detection
     */
    private static function handleSlowQuery(string $sql, array $bindings, float $executionTime): void
    {
        $logData = [
            'sql' => $sql,
            'bindings' => $bindings,
            'execution_time' => $executionTime,
            'memory_usage' => memory_get_usage(true) / 1024 / 1024, // MB
            'suggestions' => static::generateQuerySuggestions($sql),
        ];
        
        Log::channel('performance')->warning('Slow query detected', $logData);
        
        // Store for analysis
        $cacheKey = 'slow_queries_' . date('Y-m-d');
        $slowQueries = Cache::get($cacheKey, []);
        
        $slowQueries[] = [
            'timestamp' => now()->toISOString(),
            'sql' => $sql,
            'execution_time' => $executionTime,
            'suggestions' => $logData['suggestions'],
        ];
        
        // Keep only last 100 slow queries per day
        if (count($slowQueries) > 100) {
            $slowQueries = array_slice($slowQueries, -100);
        }
        
        Cache::put($cacheKey, $slowQueries, now()->addDay());
    }

    /**
     * Detect N+1 query patterns
     */
    private static function detectNPlusOneQueries(string $sql): void
    {
        static $queryPatterns = [];
        static $requestQueries = [];
        
        // Reset on new request
        if (empty($requestQueries) || (microtime(true) - ($requestQueries['start_time'] ?? 0)) > 30) {
            $requestQueries = [
                'start_time' => microtime(true),
                'patterns' => [],
                'count' => 0,
            ];
        }
        
        $requestQueries['count']++;
        
        // Extract query pattern (remove specific values)
        $pattern = preg_replace('/\b\d+\b/', '?', $sql);
        $pattern = preg_replace('/\'[^\']*\'/', '?', $pattern);
        $pattern = preg_replace('/\"[^\"]*\"/', '?', $pattern);
        
        if (!isset($requestQueries['patterns'][$pattern])) {
            $requestQueries['patterns'][$pattern] = 0;
        }
        
        $requestQueries['patterns'][$pattern]++;
        
        // Check for N+1 pattern (same query executed multiple times)
        if ($requestQueries['patterns'][$pattern] > 5) {
            Log::channel('performance')->warning('Potential N+1 query detected', [
                'pattern' => $pattern,
                'count' => $requestQueries['patterns'][$pattern],
                'total_queries' => $requestQueries['count'],
                'suggestion' => 'Consider using eager loading or batch queries',
            ]);
        }
        
        // Check for too many queries in single request
        if ($requestQueries['count'] > self::MAX_QUERIES_THRESHOLD) {
            Log::channel('performance')->error('Too many queries in single request', [
                'query_count' => $requestQueries['count'],
                'suggestion' => 'Consider query optimization and caching',
            ]);
        }
    }

    /**
     * Detect inefficient query patterns
     */
    private static function detectInefficientQueries(string $sql, float $executionTime): void
    {
        $sql = strtolower($sql);
        $issues = [];
        
        // Check for SELECT *
        if (strpos($sql, 'select *') !== false) {
            $issues[] = 'Using SELECT * - specify only needed columns';
        }
        
        // Check for LIKE with leading wildcard
        if (preg_match('/like\s+[\'"]%/', $sql)) {
            $issues[] = 'Leading wildcard LIKE query - cannot use indexes efficiently';
        }
        
        // Check for ORDER BY RAND()
        if (strpos($sql, 'order by rand()') !== false) {
            $issues[] = 'ORDER BY RAND() is very slow - consider alternative approaches';
        }
        
        // Check for queries without WHERE clause on large tables
        if (!preg_match('/\bwhere\b/', $sql) && !preg_match('/\blimit\b/', $sql)) {
            if (preg_match('/\bfrom\s+(\w+)/', $sql, $matches)) {
                $tableName = $matches[1];
                // Check if it's a potentially large table
                if (in_array($tableName, ['users', 'courses', 'enrollments', 'transactions', 'course_contents'])) {
                    $issues[] = "Query on large table '{$tableName}' without WHERE clause";
                }
            }
        }
        
        // Check for subqueries that could be JOINs
        if (preg_match('/\bin\s*\(\s*select/', $sql)) {
            $issues[] = 'Subquery in WHERE IN - consider using JOIN instead';
        }
        
        // Check for functions in WHERE clause
        if (preg_match('/where\s+\w+\s*\(/', $sql)) {
            $issues[] = 'Function in WHERE clause - may prevent index usage';
        }
        
        if (!empty($issues)) {
            Log::channel('performance')->info('Inefficient query pattern detected', [
                'sql' => $sql,
                'execution_time' => $executionTime,
                'issues' => $issues,
            ]);
        }
    }

    /**
     * Generate optimization suggestions for a query
     */
    private static function generateQuerySuggestions(string $sql): array
    {
        $suggestions = [];
        $sql = strtolower($sql);
        
        if (strpos($sql, 'select *') !== false) {
            $suggestions[] = 'Replace SELECT * with specific column names';
        }
        
        if (preg_match('/like\s+[\'"]%/', $sql)) {
            $suggestions[] = 'Consider full-text search or different indexing strategy';
        }
        
        if (strpos($sql, 'order by rand()') !== false) {
            $suggestions[] = 'Use application-level randomization or pre-computed random order';
        }
        
        if (!preg_match('/\bwhere\b/', $sql) && !preg_match('/\blimit\b/', $sql)) {
            $suggestions[] = 'Add WHERE clause to limit result set';
        }
        
        if (preg_match('/\bjoin\b.*\bjoin\b.*\bjoin\b/', $sql)) {
            $suggestions[] = 'Multiple JOINs detected - consider query splitting or denormalization';
        }
        
        if (preg_match('/group\s+by.*order\s+by/', $sql)) {
            $suggestions[] = 'GROUP BY with ORDER BY - ensure proper indexing';
        }
        
        return $suggestions;
    }

    /**
     * Get query type from SQL
     */
    private static function getQueryType(string $sql): string
    {
        $sql = strtolower(trim($sql));
        
        if (strpos($sql, 'select') === 0) return 'SELECT';
        if (strpos($sql, 'insert') === 0) return 'INSERT';
        if (strpos($sql, 'update') === 0) return 'UPDATE';
        if (strpos($sql, 'delete') === 0) return 'DELETE';
        if (strpos($sql, 'create') === 0) return 'CREATE';
        if (strpos($sql, 'alter') === 0) return 'ALTER';
        if (strpos($sql, 'drop') === 0) return 'DROP';
        
        return 'OTHER';
    }

    /**
     * Get query statistics for monitoring dashboard
     */
    public static function getQueryStatistics(): array
    {
        $currentHour = date('Y-m-d-H');
        $stats = Cache::get("query_stats_{$currentHour}", []);
        
        // Get slow queries for today
        $slowQueries = Cache::get('slow_queries_' . date('Y-m-d'), []);
        
        return [
            'current_hour' => $stats,
            'slow_queries_today' => count($slowQueries),
            'recent_slow_queries' => array_slice($slowQueries, -10),
        ];
    }
}