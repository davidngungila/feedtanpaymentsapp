<?php

namespace App\Console\Commands;

use App\Services\SmsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:sync {--limit=500 : Number of messages to sync} {--from= : Start date (YYYY-MM-DD)} {--to= : End date (YYYY-MM-DD)} {--all : Sync all messages from beginning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync SMS messages from external API to local database';

    /**
     * Execute the console command.
     */
    public function handle(SmsSyncService $smsSyncService)
    {
        $limit = $this->option('limit');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $fetchAll = $this->option('all');
        
        if ($fetchAll) {
            $this->info('🚀 Starting FULL HISTORICAL SMS sync...');
            $this->info("   From date: " . ($fromDate ?: 'Beginning'));
            $this->info("   To date: " . ($toDate ?: 'Now'));
            $this->info("   This will sync ALL messages from the API account!");
            
            if (!$this->confirm('Do you want to continue? This may take a long time.')) {
                $this->info('Sync cancelled by user.');
                return 0;
            }
        } else {
            $this->info('Starting SMS sync...');
            $this->info("   Limit: {$limit}");
            if ($fromDate) $this->info("   From: {$fromDate}");
            if ($toDate) $this->info("   To: {$toDate}");
        }
        
        $result = $smsSyncService->syncSmsMessages($limit, $fromDate, $toDate, $fetchAll);
        
        if ($result['success']) {
            if ($fetchAll) {
                $this->info("✅ FULL HISTORICAL sync completed successfully!");
                $this->info("   Total iterations: " . ($result['iterations'] ?? 1));
            } else {
                $this->info("✅ Sync completed successfully!");
            }
            
            $this->info("   New messages: {$result['synced']}");
            $this->info("   Updated messages: {$result['updated']}");
            
            if (isset($result['errors']) && $result['errors'] > 0) {
                $this->warn("Errors encountered: {$result['errors']}");
            }
            
            if (!empty($result['errors']) && is_array($result['errors'])) {
                $this->warn("Error details:");
                foreach ($result['errors'] as $error) {
                    $this->line("   - {$error}");
                }
            }
        } else {
            $this->error("❌ Sync failed: {$result['message']}");
            return 1;
        }
        
        return 0;
    }
}
