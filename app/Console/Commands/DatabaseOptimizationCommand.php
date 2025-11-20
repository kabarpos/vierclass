<?php

namespace App\Console\Commands;

use App\Services\DatabaseOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseOptimizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:optimize 
                            {--analyze : Run database analysis only}
                            {--optimize : Run optimization tasks}
                            {--report : Generate optimization report}
                            {--table= : Optimize specific table}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze and optimize database performance';

    /**
     * Database optimization service
     */
    protected DatabaseOptimizationService $optimizationService;

    /**
     * Create a new command instance.
     */
    public function __construct(DatabaseOptimizationService $optimizationService)
    {
        parent::__construct();
        $this->optimizationService = $optimizationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Database Optimization Tool');
        $this->newLine();

        if ($this->option('analyze')) {
            return $this->runAnalysis();
        }

        if ($this->option('optimize')) {
            return $this->runOptimization();
        }

        if ($this->option('report')) {
            return $this->generateReport();
        }

        // Default: show menu
        return $this->showMenu();
    }

    /**
     * Show interactive menu
     */
    private function showMenu(): int
    {
        $choice = $this->choice(
            'Pilih operasi yang ingin dilakukan:',
            [
                'analyze' => 'Analisis Database',
                'optimize' => 'Optimasi Database',
                'report' => 'Generate Report',
                'exit' => 'Keluar'
            ],
            'analyze'
        );

        switch ($choice) {
            case 'analyze':
                return $this->runAnalysis();
            case 'optimize':
                return $this->runOptimization();
            case 'report':
                return $this->generateReport();
            default:
                return 0;
        }
    }

    /**
     * Run database analysis
     */
    private function runAnalysis(): int
    {
        $this->info('📊 Menjalankan analisis database...');
        $this->newLine();

        // Analyze slow queries
        $this->line('🐌 Menganalisis slow queries...');
        $slowQueries = $this->optimizationService->analyzeSlowQueries();
        
        if (empty($slowQueries)) {
            $this->info('✅ Tidak ada slow queries ditemukan');
        } else {
            $this->warn("⚠️  Ditemukan {count($slowQueries)} slow queries");
            
            $headers = ['Query', 'Time (s)', 'Rows Examined', 'Suggestions'];
            $rows = [];
            
            foreach (array_slice($slowQueries, 0, 5) as $query) {
                $rows[] = [
                    substr($query['query'], 0, 50) . '...',
                    number_format($query['execution_time'], 2),
                    number_format($query['rows_examined']),
                    implode('; ', array_slice($query['suggestions'], 0, 2))
                ];
            }
            
            $this->table($headers, $rows);
        }

        $this->newLine();

        // Analyze table statistics
        $this->line('📋 Menganalisis statistik tabel...');
        $tableStats = $this->optimizationService->analyzeTableStatistics();
        
        if (!empty($tableStats)) {
            $headers = ['Table', 'Rows', 'Data (MB)', 'Index (MB)', 'Free (MB)', 'Issues'];
            $rows = [];
            
            foreach (array_slice($tableStats, 0, 10) as $table) {
                $issues = count($table['suggestions']);
                $rows[] = [
                    $table['table_name'],
                    number_format($table['rows']),
                    $table['data_size_mb'],
                    $table['index_size_mb'],
                    $table['free_space_mb'],
                    $issues > 0 ? "⚠️ {$issues}" : '✅ 0'
                ];
            }
            
            $this->table($headers, $rows);
        }

        $this->newLine();

        // Analyze index usage
        $this->line('🔍 Menganalisis penggunaan index...');
        $indexAnalysis = $this->optimizationService->analyzeIndexUsage();
        
        if (!empty($indexAnalysis)) {
            foreach (array_slice($indexAnalysis, 0, 5) as $table) {
                if (!empty($table['suggestions'])) {
                    $this->warn("Table {$table['table_name']}:");
                    foreach ($table['suggestions'] as $suggestion) {
                        $this->line("  • {$suggestion}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info('✅ Analisis selesai');
        
        return 0;
    }

    /**
     * Run database optimization
     */
    private function runOptimization(): int
    {
        $this->info('⚡ Menjalankan optimasi database...');
        $this->newLine();

        $table = $this->option('table');
        
        if ($table) {
            return $this->optimizeTable($table);
        }

        // Get tables that need optimization
        $tableStats = $this->optimizationService->analyzeTableStatistics();
        $tablesToOptimize = collect($tableStats)
            ->filter(function ($table) {
                return !empty($table['suggestions']);
            })
            ->pluck('table_name')
            ->toArray();

        if (empty($tablesToOptimize)) {
            $this->info('✅ Tidak ada tabel yang memerlukan optimasi');
            return 0;
        }

        $this->warn("Ditemukan {count($tablesToOptimize)} tabel yang memerlukan optimasi:");
        foreach ($tablesToOptimize as $tableName) {
            $this->line("  • {$tableName}");
        }

        if (!$this->confirm('Lanjutkan optimasi?')) {
            return 0;
        }

        $bar = $this->output->createProgressBar(count($tablesToOptimize));
        $bar->start();

        foreach ($tablesToOptimize as $tableName) {
            $this->optimizeTable($tableName, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Optimasi selesai');

        return 0;
    }

    /**
     * Optimize specific table
     */
    private function optimizeTable(string $tableName, bool $verbose = true): int
    {
        try {
            if ($verbose) {
                $this->line("🔧 Mengoptimasi tabel: {$tableName}");
            }

            // Run OPTIMIZE TABLE
            DB::statement("OPTIMIZE TABLE {$tableName}");
            
            // Update table statistics
            DB::statement("ANALYZE TABLE {$tableName}");

            if ($verbose) {
                $this->info("✅ Tabel {$tableName} berhasil dioptimasi");
            }

            return 0;
        } catch (\Exception $e) {
            if ($verbose) {
                $this->error("❌ Gagal mengoptimasi tabel {$tableName}: {$e->getMessage()}");
            }
            return 1;
        }
    }

    /**
     * Generate optimization report
     */
    private function generateReport(): int
    {
        $this->info('📄 Generating optimization report...');
        $this->newLine();

        $report = $this->optimizationService->generateOptimizationReport();

        // Display summary
        $this->info('📊 Database Optimization Report Summary');
        $this->line('═══════════════════════════════════════');
        
        $this->line("🐌 Slow Queries: " . count($report['slow_queries']));
        $this->line("📋 Tables Analyzed: " . count($report['table_statistics']));
        $this->line("🔍 Indexes Analyzed: " . count($report['index_usage']));
        
        if (isset($report['connection_statistics']['efficiency'])) {
            $efficiency = $report['connection_statistics']['efficiency'];
            
            if (isset($efficiency['table_cache_hit_ratio'])) {
                $ratio = $efficiency['table_cache_hit_ratio'];
                $status = $ratio > 95 ? '✅' : ($ratio > 85 ? '⚠️' : '❌');
                $this->line("📊 Table Cache Hit Ratio: {$status} {$ratio}%");
            }
            
            if (isset($efficiency['query_cache_hit_ratio'])) {
                $ratio = $efficiency['query_cache_hit_ratio'];
                $status = $ratio > 80 ? '✅' : ($ratio > 60 ? '⚠️' : '❌');
                $this->line("🎯 Query Cache Hit Ratio: {$status} {$ratio}%");
            }
        }

        $this->newLine();

        // Show top issues
        $issues = [];
        
        foreach ($report['slow_queries'] as $query) {
            if (!empty($query['suggestions'])) {
                $issues[] = "Slow Query: " . substr($query['query'], 0, 60) . '...';
            }
        }
        
        foreach ($report['table_statistics'] as $table) {
            foreach ($table['suggestions'] as $suggestion) {
                $issues[] = "Table {$table['table_name']}: {$suggestion}";
            }
        }
        
        foreach ($report['index_usage'] as $table) {
            foreach ($table['suggestions'] as $suggestion) {
                $issues[] = "Index {$table['table_name']}: {$suggestion}";
            }
        }

        if (!empty($issues)) {
            $this->warn('⚠️  Top Issues Found:');
            foreach (array_slice($issues, 0, 10) as $issue) {
                $this->line("  • {$issue}");
            }
        } else {
            $this->info('✅ No critical issues found');
        }

        $this->newLine();
        $this->info('📄 Report cached for dashboard access');
        
        return 0;
    }
}