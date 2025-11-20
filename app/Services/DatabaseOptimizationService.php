<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DatabaseOptimizationService
{
    /**
     * Analyze slow queries and provide optimization suggestions
     */
    public function analyzeSlowQueries(): array
    {
        try {
            // Enable slow query log analysis
            $slowQueries = $this->getSlowQueries();
            $analysis = [];
            
            foreach ($slowQueries as $query) {
                $analysis[] = [
                    'query' => $query['sql_text'] ?? 'N/A',
                    'execution_time' => $query['query_time'] ?? 0,
                    'rows_examined' => $query['rows_examined'] ?? 0,
                    'rows_sent' => $query['rows_sent'] ?? 0,
                    'suggestions' => $this->generateOptimizationSuggestions($query),
                ];
            }
            
            return $analysis;
        } catch (\Exception $e) {
            Log::error('Failed to analyze slow queries', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get slow queries from MySQL slow query log
     */
    private function getSlowQueries(): array
    {
        try {
            // Check if slow query log is enabled
            $slowLogStatus = DB::select("SHOW VARIABLES LIKE 'slow_query_log'");
            if (empty($slowLogStatus) || $slowLogStatus[0]->Value !== 'ON') {
                return [];
            }
            
            // Get recent slow queries from performance schema
            $queries = DB::select("
                SELECT 
                    sql_text,
                    avg_timer_wait / 1000000000 as query_time,
                    sum_rows_examined as rows_examined,
                    sum_rows_sent as rows_sent,
                    count_star as execution_count
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE avg_timer_wait > 1000000000 
                ORDER BY avg_timer_wait DESC 
                LIMIT 20
            ");
            
            return collect($queries)->map(function ($query) {
                return [
                    'sql_text' => $query->sql_text,
                    'query_time' => $query->query_time,
                    'rows_examined' => $query->rows_examined,
                    'rows_sent' => $query->rows_sent,
                    'execution_count' => $query->execution_count,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::warning('Could not retrieve slow queries', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Generate optimization suggestions for a query
     */
    private function generateOptimizationSuggestions(array $query): array
    {
        $suggestions = [];
        
        // High rows examined vs rows sent ratio
        if (isset($query['rows_examined'], $query['rows_sent']) && 
            $query['rows_examined'] > 0 && 
            ($query['rows_examined'] / max($query['rows_sent'], 1)) > 100) {
            $suggestions[] = 'Consider adding indexes to reduce rows examined';
        }
        
        // Long execution time
        if (isset($query['query_time']) && $query['query_time'] > 5) {
            $suggestions[] = 'Query execution time is very high, consider query optimization';
        }
        
        // Check for common anti-patterns in SQL
        if (isset($query['sql_text'])) {
            $sql = strtolower($query['sql_text']);
            
            if (strpos($sql, 'select *') !== false) {
                $suggestions[] = 'Avoid SELECT *, specify only needed columns';
            }
            
            if (strpos($sql, 'like \'%') !== false) {
                $suggestions[] = 'Leading wildcard LIKE queries cannot use indexes efficiently';
            }
            
            if (strpos($sql, 'order by rand()') !== false) {
                $suggestions[] = 'ORDER BY RAND() is very slow, consider alternative approaches';
            }
            
            if (strpos($sql, 'where') === false && strpos($sql, 'limit') === false) {
                $suggestions[] = 'Query without WHERE clause may scan entire table';
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Analyze table statistics and suggest optimizations
     */
    public function analyzeTableStatistics(): array
    {
        try {
            $tables = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length,
                    data_free,
                    auto_increment,
                    table_collation
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY data_length DESC
            ");
            
            $analysis = [];
            
            foreach ($tables as $table) {
                $suggestions = [];
                
                // Large data_free suggests fragmentation
                if ($table->data_free > ($table->data_length * 0.1)) {
                    $suggestions[] = 'Table may be fragmented, consider OPTIMIZE TABLE';
                }
                
                // Large table without indexes
                if ($table->table_rows > 10000 && $table->index_length < ($table->data_length * 0.1)) {
                    $suggestions[] = 'Large table with few indexes, may need index optimization';
                }
                
                // Check for unused auto_increment gaps
                if ($table->auto_increment && $table->table_rows > 0) {
                    $gapRatio = $table->auto_increment / $table->table_rows;
                    if ($gapRatio > 2) {
                        $suggestions[] = 'Large gaps in auto_increment, consider resetting if appropriate';
                    }
                }
                
                $analysis[] = [
                    'table_name' => $table->table_name,
                    'rows' => $table->table_rows,
                    'data_size_mb' => round($table->data_length / 1024 / 1024, 2),
                    'index_size_mb' => round($table->index_length / 1024 / 1024, 2),
                    'free_space_mb' => round($table->data_free / 1024 / 1024, 2),
                    'collation' => $table->table_collation,
                    'suggestions' => $suggestions,
                ];
            }
            
            return $analysis;
        } catch (\Exception $e) {
            Log::error('Failed to analyze table statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Check index usage and suggest improvements
     */
    public function analyzeIndexUsage(): array
    {
        try {
            // Get index statistics
            $indexStats = DB::select("
                SELECT 
                    table_name,
                    index_name,
                    column_name,
                    cardinality,
                    nullable,
                    index_type
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE()
                ORDER BY table_name, index_name, seq_in_index
            ");
            
            // Get unused indexes (requires performance schema)
            $unusedIndexes = [];
            try {
                $unusedIndexes = DB::select("
                    SELECT 
                        object_schema,
                        object_name,
                        index_name
                    FROM performance_schema.table_io_waits_summary_by_index_usage 
                    WHERE index_name IS NOT NULL 
                    AND index_name != 'PRIMARY'
                    AND count_star = 0
                    AND object_schema = DATABASE()
                ");
            } catch (\Exception $e) {
                // Performance schema might not be available
            }
            
            $analysis = [];
            $tableIndexes = collect($indexStats)->groupBy('table_name');
            
            foreach ($tableIndexes as $tableName => $indexes) {
                $suggestions = [];
                
                // Check for duplicate indexes
                $indexGroups = $indexes->groupBy('index_name');
                foreach ($indexGroups as $indexName => $indexColumns) {
                    if ($indexColumns->count() > 1) {
                        $columns = $indexColumns->pluck('column_name')->implode(', ');
                        
                        // Check for low cardinality
                        $avgCardinality = $indexColumns->avg('cardinality');
                        if ($avgCardinality < 10) {
                            $suggestions[] = "Index '{$indexName}' on ({$columns}) has low cardinality";
                        }
                    }
                }
                
                // Check for unused indexes
                $tableUnusedIndexes = collect($unusedIndexes)
                    ->where('object_name', $tableName)
                    ->pluck('index_name');
                
                if ($tableUnusedIndexes->isNotEmpty()) {
                    $suggestions[] = 'Unused indexes: ' . $tableUnusedIndexes->implode(', ');
                }
                
                $analysis[] = [
                    'table_name' => $tableName,
                    'index_count' => $indexGroups->count(),
                    'indexes' => $indexGroups->map(function ($columns, $indexName) {
                        return [
                            'name' => $indexName,
                            'columns' => $columns->pluck('column_name')->toArray(),
                            'type' => $columns->first()->index_type,
                            'cardinality' => $columns->sum('cardinality'),
                        ];
                    })->values()->toArray(),
                    'suggestions' => $suggestions,
                ];
            }
            
            return $analysis;
        } catch (\Exception $e) {
            Log::error('Failed to analyze index usage', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get database connection statistics
     */
    public function getConnectionStatistics(): array
    {
        try {
            $stats = [];
            
            // Get connection variables
            $variables = DB::select("SHOW VARIABLES WHERE Variable_name IN (
                'max_connections', 
                'max_user_connections', 
                'thread_cache_size',
                'table_open_cache',
                'query_cache_size',
                'innodb_buffer_pool_size'
            )");
            
            foreach ($variables as $var) {
                $stats['variables'][$var->Variable_name] = $var->Value;
            }
            
            // Get connection status
            $status = DB::select("SHOW STATUS WHERE Variable_name IN (
                'Threads_connected', 
                'Threads_running', 
                'Connections',
                'Aborted_connects',
                'Table_open_cache_hits',
                'Table_open_cache_misses',
                'Qcache_hits',
                'Qcache_inserts'
            )");
            
            foreach ($status as $stat) {
                $stats['status'][$stat->Variable_name] = $stat->Value;
            }
            
            // Calculate efficiency ratios
            if (isset($stats['status']['Table_open_cache_hits'], $stats['status']['Table_open_cache_misses'])) {
                $hits = (int) $stats['status']['Table_open_cache_hits'];
                $misses = (int) $stats['status']['Table_open_cache_misses'];
                $total = $hits + $misses;
                
                $stats['efficiency']['table_cache_hit_ratio'] = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
            }
            
            if (isset($stats['status']['Qcache_hits'], $stats['status']['Qcache_inserts'])) {
                $hits = (int) $stats['status']['Qcache_hits'];
                $inserts = (int) $stats['status']['Qcache_inserts'];
                $total = $hits + $inserts;
                
                $stats['efficiency']['query_cache_hit_ratio'] = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get connection statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Generate comprehensive database optimization report
     */
    public function generateOptimizationReport(): array
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'slow_queries' => $this->analyzeSlowQueries(),
            'table_statistics' => $this->analyzeTableStatistics(),
            'index_usage' => $this->analyzeIndexUsage(),
            'connection_statistics' => $this->getConnectionStatistics(),
        ];
        
        // Cache the report for dashboard
        Cache::put('database_optimization_report', $report, now()->addHours(1));
        
        // Log summary
        Log::channel('performance')->info('Database optimization report generated', [
            'slow_queries_count' => count($report['slow_queries']),
            'tables_analyzed' => count($report['table_statistics']),
            'indexes_analyzed' => count($report['index_usage']),
        ]);
        
        return $report;
    }
}