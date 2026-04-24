<?php

namespace App\Console\Commands;

use App\Services\ClickPesaAPIService;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPaymentStatusCommand extends Command
{
    protected $signature = 'test:payment-status {order_reference}';
    protected $description = 'Check payment status by order reference';

    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle()
    {
        $orderReference = $this->argument('order_reference');

        $this->info("=== Checking Payment Status ===");
        $this->info("Order Reference: {$orderReference}");
        $this->info("");

        try {
            // Check database first
            $this->info("1. Checking database...");
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            
            if ($transaction) {
                $this->info("✅ Transaction found in database");
                $this->info("  - ID: {$transaction->id}");
                $this->info("  - Status: {$transaction->status}");
                $this->info("  - Amount: {$transaction->amount} {$transaction->currency}");
                $this->info("  - Phone: {$transaction->phone}");
                $this->info("  - Payer: {$transaction->payer_name}");
                $this->info("  - Transaction ID: " . ($transaction->transaction_id ?? 'N/A'));
                $this->info("  - Created: {$transaction->created_at}");
                $this->info("  - Updated: {$transaction->updated_at}");
            } else {
                $this->warn("⚠️  Transaction not found in database");
            }

            // Check API status
            $this->info("2. Checking API status...");
            try {
                $apiData = $this->api->queryPaymentStatus($orderReference);
                
                $this->info("✅ API response received");
                
                if (is_array($apiData) && !empty($apiData)) {
                    // Handle single payment response
                    if (isset($apiData['id'])) {
                        $this->info("  - Transaction ID: {$apiData['id']}");
                        $this->info("  - Status: {$apiData['status']}");
                        $this->info("  - Amount: " . ($apiData['collectedAmount'] ?? 'N/A') . " " . ($apiData['collectedCurrency'] ?? 'N/A'));
                        $this->info("  - Phone: " . ($apiData['paymentPhoneNumber'] ?? 'N/A'));
                        $this->info("  - Method: " . ($apiData['channel'] ?? 'N/A'));
                        $this->info("  - Message: " . ($apiData['message'] ?? 'N/A'));
                        $this->info("  - Created: " . ($apiData['createdAt'] ?? 'N/A'));
                        $this->info("  - Updated: " . ($apiData['updatedAt'] ?? 'N/A'));
                        
                        // Update database if needed
                        if ($transaction && $transaction->status !== $apiData['status']) {
                            $this->info("3. Updating database with new status...");
                            $transaction->update([
                                'status' => $apiData['status'],
                                'transaction_id' => $apiData['id'],
                                'payment_method' => isset($apiData['channel']) ? $apiData['channel'] : null,
                            ]);
                            $this->info("✅ Database updated");
                        }
                    }
                    // Handle array response (multiple payments)
                    elseif (isset($apiData[0])) {
                        $this->info("  - Found " . count($apiData) . " payment(s)");
                        foreach ($apiData as $index => $payment) {
                            $this->info("  Payment " . ($index + 1) . ":");
                            $this->info("    - ID: {$payment['id']}");
                            $this->info("    - Status: {$payment['status']}");
                            $this->info("    - Amount: " . (isset($payment['collectedAmount']) ? $payment['collectedAmount'] : 'N/A') . " " . (isset($payment['collectedCurrency']) ? $payment['collectedCurrency'] : 'N/A'));
                            $this->info("    - Phone: " . (isset($payment['paymentPhoneNumber']) ? $payment['paymentPhoneNumber'] : 'N/A'));
                            $this->info("    - Method: " . (isset($payment['channel']) ? $payment['channel'] : 'N/A'));
                        }
                    }
                } else {
                    $this->warn("⚠️  No payment data returned from API");
                }
                
            } catch (\Exception $e) {
                $this->warn("⚠️  API check failed: " . $e->getMessage());
            }

            $this->info("");
            $this->info("=== Status Check Complete ===");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Status check failed: " . $e->getMessage());
            Log::error('Payment status check failed', [
                'order_reference' => $orderReference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
