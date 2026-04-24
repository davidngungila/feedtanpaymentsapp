<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPaymentsCommand extends Command
{
    protected $signature = 'sync:payments {--limit=100} {--force}';
    protected $description = 'Sync all payment records from ClickPesa API to local database';

    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        $this->info("=== Syncing Payments from ClickPesa API ===");
        $this->info("Limit: {$limit} records");
        $this->info("Force update: " . ($force ? 'Yes' : 'No'));
        $this->info("");

        try {
            // Fetch all payments from API
            $this->info("1. Fetching payments from API...");
            $apiPayments = $this->fetchAllPayments($limit);
            
            if (empty($apiPayments)) {
                $this->info("No payments found in API.");
                return 0;
            }

            $this->info("✅ Found " . count($apiPayments) . " payments in API");

            // Sync with database
            $this->info("2. Syncing with database...");
            $syncResult = $this->syncPaymentsToDatabase($apiPayments, $force);

            $this->info("✅ Sync completed:");
            $this->info("  - New payments: {$syncResult['created']}");
            $this->info("  - Updated payments: {$syncResult['updated']}");
            $this->info("  - Skipped payments: {$syncResult['skipped']}");

            // Show summary
            $this->info("");
            $this->info("=== Sync Summary ===");
            $totalTransactions = Transaction::where('type', 'payment')->count();
            $this->info("Total payments in database: {$totalTransactions}");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            Log::error('Payment sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function fetchAllPayments(int $limit = 100): array
    {
        $allPayments = [];
        $skip = 0;
        $hasMore = true;

        while ($hasMore && count($allPayments) < $limit) {
            try {
                $params = [
                    'limit' => min(50, $limit - count($allPayments)),
                    'skip' => $skip,
                    'orderBy' => 'DESC'
                ];

                $this->info("  Fetching batch (skip: {$skip}, limit: {$params['limit']})...");
                
                $response = $this->api->queryAllPayments($params);
                
                if (!is_array($response) || empty($response)) {
                    $hasMore = false;
                    break;
                }

                // Handle the new API response format with 'data' and 'totalCount'
                $batchPayments = [];
                if (isset($response['data']) && is_array($response['data'])) {
                    $batchPayments = $response['data'];
                } elseif (is_array($response[0] ?? null)) {
                    $batchPayments = $response;
                } else {
                    $batchPayments = [$response];
                }
                
                foreach ($batchPayments as $payment) {
                    if (count($allPayments) >= $limit) {
                        break 2;
                    }
                    $allPayments[] = $payment;
                }

                $skip += count($batchPayments);
                
                // Check if we have more data based on totalCount
                if (isset($response['totalCount'])) {
                    $hasMore = $skip < $response['totalCount'];
                } elseif (count($batchPayments) < $params['limit']) {
                    $hasMore = false;
                }

                // Small delay to avoid rate limiting
                if ($hasMore) {
                    usleep(100000); // 0.1 seconds
                }

            } catch (\Exception $e) {
                $this->warn("  Warning: Failed to fetch batch - " . $e->getMessage());
                $hasMore = false;
            }
        }

        return $allPayments;
    }

    private function syncPaymentsToDatabase(array $apiPayments, bool $force): array
    {
        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0
        ];

        $progressBar = $this->output->createProgressBar(count($apiPayments));
        $progressBar->start();

        foreach ($apiPayments as $payment) {
            try {
                $orderReference = $payment['orderReference'] ?? $payment['paymentReference'] ?? null;
                
                if (!$orderReference) {
                    $result['skipped']++;
                    $progressBar->advance();
                    continue;
                }

                $existingTransaction = Transaction::where('order_reference', $orderReference)->first();

                if ($existingTransaction) {
                    // Always update existing transactions to ensure we have the latest data
                    $existingTransaction->update([
                        'transaction_id' => $payment['id'] ?? $existingTransaction->transaction_id,
                        'status' => $payment['status'] ?? $existingTransaction->status,
                        'payment_method' => $payment['channel'] ?? $existingTransaction->payment_method,
                        'callback_data' => $payment,
                    ]);
                    $result['updated']++;
                } else {
                    // Create new transaction
                    Transaction::create([
                        'order_reference' => $orderReference,
                        'transaction_id' => $payment['id'] ?? null,
                        'status' => $payment['status'] ?? 'UNKNOWN',
                        'amount' => $payment['collectedAmount'] ?? $payment['amount'] ?? 0,
                        'currency' => $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS',
                        'phone' => $payment['paymentPhoneNumber'] ?? $payment['customer']['customerPhoneNumber'] ?? null,
                        'payer_name' => $payment['customer']['customerName'] ?? 'Unknown',
                        'description' => $payment['description'] ?? 'Synced from API',
                        'type' => 'payment',
                        'payment_method' => $payment['channel'] ?? null,
                        'callback_data' => $payment,
                        'user_id' => 1, // Default to admin user
                    ]);
                    $result['created']++;
                }

            } catch (\Exception $e) {
                $this->warn("  Error syncing payment: " . $e->getMessage());
                $result['skipped']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("");

        return $result;
    }
}
