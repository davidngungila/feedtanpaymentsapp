<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiCaptureController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoApiCapture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:capture-auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically capture new transactions from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic API capture...');
        
        try {
            $apiCapture = new ApiCaptureController(app(\App\Services\ClickPesaAPIService::class));
            $result = $apiCapture->autoCapture();
            
            $data = json_decode($result->getContent(), true);
            
            if ($data['success']) {
                $this->info('✅ Automatic API capture completed successfully');
                $this->info("📊 New transactions: {$data['new_transactions']}");
                $this->info("🔄 Updated transactions: {$data['updated_transactions']}");
                $this->info("📈 Total processed: " . ($data['new_transactions'] + $data['updated_transactions']));
            } else {
                $this->error('❌ Automatic API capture failed: ' . $data['error']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Command execution failed: ' . $e->getMessage());
            Log::error('Auto API capture command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
        
        return 0;
    }
}
