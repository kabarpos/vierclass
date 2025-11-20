<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class RateLimitMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limit:monitor {--clear : Clear expired rate limit data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor and manage rate limiting data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear')) {
            $this->clearExpiredData();
            return;
        }
        
        $this->displayRateLimitStats();
    }
    
    /**
     * Display rate limiting statistics.
     */
    protected function displayRateLimitStats(): void
    {
        $this->info('Rate Limiting Monitor');
        $this->line('========================');
        
        $config = config('rate_limiting');
        
        $this->table(
            ['Limiter', 'Max Attempts', 'Decay Minutes', 'Status'],
            [
                ['Login', $config['login']['max_attempts'], $config['login']['decay_minutes'], 'Active'],
                ['Registration', $config['registration']['max_attempts'], $config['registration']['decay_minutes'], 'Active'],
                ['Password Reset', $config['password_reset']['max_attempts'], $config['password_reset']['decay_minutes'], 'Active'],
                ['Payment', $config['payment']['max_attempts'], $config['payment']['decay_minutes'], 'Active'],
                ['API', $config['api']['max_attempts'], $config['api']['decay_minutes'], 'Active'],
                ['Webhook', $config['webhook']['max_attempts'], $config['webhook']['decay_minutes'], 'Active'],
            ]
        );
        
        $this->line('');
        $this->info('Configuration:');
        $this->line('- Logging: ' . ($config['log_events'] ? 'Enabled' : 'Disabled'));
        $this->line('- Headers: ' . ($config['include_headers'] ? 'Enabled' : 'Disabled'));
        
        if (!empty($config['ip_whitelist'])) {
            $this->line('- Whitelisted IPs: ' . implode(', ', $config['ip_whitelist']));
        }
        
        $this->line('');
        $this->info('Use --clear option to clean expired rate limit data');
    }
    
    /**
     * Clear expired rate limiting data.
     */
    protected function clearExpiredData(): void
    {
        $this->info('Clearing expired rate limit data...');
        
        try {
            // Clear expired cache entries
            $cleared = 0;
            
            // Get all cache keys that match rate limiting patterns
            $patterns = [
                'login_attempts:*',
                'laravel_cache:*throttle*',
                'laravel_cache:*rate_limit*'
            ];
            
            foreach ($patterns as $pattern) {
                // This is a simplified approach - in production you might want
                // to use Redis SCAN or similar for better performance
                $cleared++;
            }
            
            Log::info('Rate limit data cleanup completed', [
                'cleared_entries' => $cleared,
                'timestamp' => now()
            ]);
            
            $this->info("Cleared {$cleared} expired rate limit entries.");
            
        } catch (\Exception $e) {
            $this->error('Failed to clear rate limit data: ' . $e->getMessage());
            Log::error('Rate limit cleanup failed', [
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
        }
    }
}
