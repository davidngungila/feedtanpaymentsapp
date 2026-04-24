<?php

namespace App\Jobs;

use App\Services\SmsSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSmsMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Execute the job.
     */
    public function handle(SmsSyncService $smsSyncService)
    {
        try {
            Log::info('Starting SMS sync job');
            
            $result = $smsSyncService->syncSmsMessages(500);
            
            if ($result['success']) {
                Log::info('SMS sync job completed successfully', [
                    'synced' => $result['synced'],
                    'updated' => $result['updated']
                ]);
            } else {
                Log::error('SMS sync job failed', [
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('SMS sync job exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SMS sync job failed permanently', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
