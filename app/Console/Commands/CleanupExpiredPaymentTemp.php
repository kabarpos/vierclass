<?php

namespace App\Console\Commands;

use App\Repositories\PaymentTempRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredPaymentTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired payment temporary records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired payment temporary records...');
        
        try {
            $deletedCount = app(PaymentTempRepositoryInterface::class)->cleanupExpired();
            
            $this->info("Successfully cleaned up {$deletedCount} expired payment records.");
            
            Log::info('Payment temp cleanup completed', [
                'deleted_count' => $deletedCount,
                'executed_at' => now()
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired payment records: ' . $e->getMessage());
            
            Log::error('Payment temp cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
