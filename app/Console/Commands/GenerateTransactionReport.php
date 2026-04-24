<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\ClickPesaAPIService;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('transactions:report')]
#[Description('Get full transaction data with balances and generate comprehensive reports')]
class GenerateTransactionReport extends Command
{
    protected ClickPesaAPIService $clickPesa;

    public function __construct(ClickPesaAPIService $clickPesa)
    {
        parent::__construct();
        $this->clickPesa = $clickPesa;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Transaction Report Generator ===");
        $this->info("Fetching full transaction data from ClickPesa API...");
        $this->info("");

        try {
            // Get account balance first
            $this->info("1. Fetching account balance...");
            $balance = $this->getAccountBalance();
            $this->displayBalance($balance);

            // Get account statement
            $this->info("\n2. Fetching account statement...");
            $statement = $this->getAccountStatement();
            $this->displayStatement($statement);

            // Get all transactions
            $this->info("\n3. Fetching all transactions...");
            $transactions = $this->getAllTransactions();
            $this->displayTransactionSummary($transactions);

            // Sync to database
            $this->info("\n4. Syncing to database...");
            $syncedCount = $this->syncToDatabase($transactions);
            $this->info("✅ Synced {$syncedCount} transactions to database");

            // Generate reports
            $this->info("\n5. Generating reports...");
            $this->generateReports();

            $this->info("\n=== Report Generation Complete ===");
            $this->info("✅ All data fetched and reports generated successfully");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Report generation failed: " . $e->getMessage());
            Log::error('Transaction report command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function getAccountBalance()
    {
        try {
            $balance = $this->clickPesa->getAccountBalance();
            $this->info("✅ Balance fetched successfully");
            return $balance;
        } catch (\Exception $e) {
            $this->warn("⚠️  Failed to fetch balance: " . $e->getMessage());
            return [];
        }
    }

    private function getAccountStatement()
    {
        try {
            // Get statement for the last 30 days
            $startDate = now()->subDays(30)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            
            $statement = $this->clickPesa->getAccountStatement([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currency' => 'TZS'
            ]);
            
            $this->info("✅ Statement fetched successfully");
            return $statement;
        } catch (\Exception $e) {
            $this->warn("⚠️  Failed to fetch statement: " . $e->getMessage());
            return [];
        }
    }

    private function getAllTransactions()
    {
        try {
            $allTransactions = [];
            $limit = 50; // Reduced limit to avoid too many transactions
            $offset = 0;
            $hasMore = true;
            $maxBatches = 10; // Limit to 10 batches max (500 transactions)

            while ($hasMore && ($offset / $limit) < $maxBatches) {
                $this->info("   Fetching transactions batch " . ($offset / $limit + 1) . "...");
                
                $payments = $this->clickPesa->queryAllPayments([
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                if (isset($payments['data']) && is_array($payments['data'])) {
                    $allTransactions = array_merge($allTransactions, $payments['data']);
                    
                    if (count($payments['data']) < $limit) {
                        $hasMore = false;
                    } else {
                        $offset += $limit;
                    }
                } else {
                    $hasMore = false;
                }

                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 seconds
            }

            $this->info("✅ Fetched " . count($allTransactions) . " total transactions");
            return $allTransactions;
        } catch (\Exception $e) {
            $this->warn("⚠️  Failed to fetch transactions: " . $e->getMessage());
            return [];
        }
    }

    private function displayBalance($balance)
    {
        if (empty($balance)) {
            $this->line("   No balance data available");
            return;
        }

        foreach ($balance as $account) {
            $this->line("   Currency: " . ($account['currency'] ?? 'N/A'));
            $this->line("   Balance: " . number_format($account['balance'] ?? 0, 2) . " " . ($account['currency'] ?? ''));
            $this->line("");
        }
    }

    private function displayStatement($statement)
    {
        if (empty($statement)) {
            $this->line("   No statement data available");
            return;
        }

        // Display account details
        if (isset($statement['accountDetails'])) {
            $details = $statement['accountDetails'];
            $this->line("   Account Details:");
            $this->line("   - Currency: " . ($details['currency'] ?? 'N/A'));
            $this->line("   - Opening Balance: " . number_format($details['openingBalance'] ?? 0, 2));
            $this->line("   - Closing Balance: " . number_format($details['closingBalance'] ?? 0, 2));
            $this->line("   - Total Credits: " . number_format($details['totalCredits'] ?? 0, 2));
            $this->line("   - Total Debits: " . number_format($details['totalDebits'] ?? 0, 2));
            $this->line("");
        }

        // Display transactions summary
        if (isset($statement['transactions']) && is_array($statement['transactions'])) {
            $this->line("   Statement Transactions: " . count($statement['transactions']));
        }
    }

    private function displayTransactionSummary($transactions)
    {
        if (empty($transactions)) {
            $this->line("   No transactions found");
            return;
        }

        $summary = [
            'total' => count($transactions),
            'success' => 0,
            'settled' => 0,
            'pending' => 0,
            'failed' => 0,
            'total_amount' => 0,
            'success_amount' => 0,
            'settled_amount' => 0
        ];

        foreach ($transactions as $transaction) {
            $status = $transaction['status'] ?? 'UNKNOWN';
            $amount = floatval($transaction['collectedAmount'] ?? $transaction['amount'] ?? 0);

            $summary['total_amount'] += $amount;

            if ($status === 'SUCCESS') {
                $summary['success']++;
                $summary['success_amount'] += $amount;
            } elseif ($status === 'SETTLED') {
                $summary['settled']++;
                $summary['settled_amount'] += $amount;
            } elseif (in_array($status, ['PROCESSING', 'PENDING'])) {
                $summary['pending']++;
            } elseif ($status === 'FAILED') {
                $summary['failed']++;
            }
        }

        $this->line("   Transaction Summary:");
        $this->line("   - Total Transactions: " . $summary['total']);
        $this->line("   - Successful: " . $summary['success'] . " (" . number_format($summary['success_amount'], 2) . " TZS)");
        $this->line("   - Settled: " . $summary['settled'] . " (" . number_format($summary['settled_amount'], 2) . " TZS)");
        $this->line("   - Pending: " . $summary['pending']);
        $this->line("   - Failed: " . $summary['failed']);
        $this->line("   - Total Amount: " . number_format($summary['total_amount'], 2) . " TZS");
    }

    private function syncToDatabase($transactions)
    {
        $syncedCount = 0;

        foreach ($transactions as $payment) {
            $orderReference = $payment['orderReference'] ?? null;
            
            if ($orderReference) {
                $existingTransaction = Transaction::where('order_reference', $orderReference)->first();
                
                if (!$existingTransaction) {
                    // Create new transaction
                    $payerName = $payment['customer']['customerName'] 
                        ?? $payment['payerName'] 
                        ?? $payment['customerName'] 
                        ?? $payment['sender']['accountName'] 
                        ?? $payment['sender']['name']
                        ?? $payment['customer']['name']
                        ?? 'Unknown';
                    
                    $phone = $payment['paymentPhoneNumber'] 
                        ?? $payment['customer']['customerPhoneNumber'] 
                        ?? $payment['phoneNumber'] 
                        ?? $payment['sender']['accountNumber']
                        ?? $payment['customer']['phone']
                        ?? null;
                    
                    Transaction::create([
                        'order_reference' => $orderReference,
                        'transaction_id' => $payment['id'] ?? null,
                        'status' => $payment['status'] ?? 'UNKNOWN',
                        'amount' => $payment['collectedAmount'] ?? $payment['amount'] ?? 0,
                        'currency' => $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS',
                        'phone' => $phone,
                        'payer_name' => $payerName,
                        'payment_method' => $payment['channel'] ?? $payment['paymentMethod'] ?? null,
                        'description' => 'Command sync - ' . ($payment['description'] ?? 'Payment'),
                        'type' => 'payment',
                        'callback_data' => $payment,
                    ]);
                    $syncedCount++;
                } else {
                    // Update existing transaction
                    $existingTransaction->update([
                        'status' => $payment['status'] ?? $existingTransaction->status,
                        'payment_method' => $payment['channel'] ?? $payment['paymentMethod'] ?? $existingTransaction->payment_method,
                        'callback_data' => $payment,
                    ]);
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }

    private function generateReports()
    {
        // Generate monthly report
        $this->generateMonthlyReport();
        
        // Generate payment method report
        $this->generatePaymentMethodReport();
        
        // Generate status report
        $this->generateStatusReport();
    }

    private function generateMonthlyReport()
    {
        $this->info("   Generating monthly report...");
        
        $monthlyData = Transaction::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(CASE WHEN status = "SUCCESS" THEN amount ELSE 0 END) as success_amount'),
            DB::raw('SUM(CASE WHEN status = "SETTLED" THEN amount ELSE 0 END) as settled_amount'),
            DB::raw('SUM(CASE WHEN status IN ("PROCESSING", "PENDING") THEN amount ELSE 0 END) as pending_amount'),
            DB::raw('SUM(CASE WHEN status = "FAILED" THEN amount ELSE 0 END) as failed_amount')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();

        $this->line("   Monthly Report (Last 12 months):");
        foreach ($monthlyData as $month) {
            $this->line("   - " . $month->month . ": " . $month->transaction_count . " transactions, " . 
                       number_format($month->total_amount, 2) . " TZS total");
        }
    }

    private function generatePaymentMethodReport()
    {
        $this->info("   Generating payment method report...");
        
        $methodData = Transaction::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get();

        $this->line("   Payment Method Report:");
        foreach ($methodData as $method) {
            $this->line("   - " . $method->payment_method . ": " . $method->count . " transactions, " . 
                       number_format($method->total, 2) . " TZS total");
        }
    }

    private function generateStatusReport()
    {
        $this->info("   Generating status report...");
        
        $statusData = Transaction::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $this->line("   Status Report:");
        foreach ($statusData as $status) {
            $this->line("   - " . $status->status . ": " . $status->count . " transactions, " . 
                       number_format($status->total, 2) . " TZS total");
        }
    }
}
