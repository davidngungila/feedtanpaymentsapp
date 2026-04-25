<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiCaptureController extends Controller
{
    protected $clickPesa;
    
    public function __construct(ClickPesaAPIService $clickPesa)
    {
        $this->clickPesa = $clickPesa;
    }
    
    /**
     * Automatic API capture for new transactions
     * This method can be called by scheduler or webhook
     */
    public function autoCapture()
    {
        try {
            Log::info('Starting automatic API capture');
            
            // Get the latest transaction timestamp from database
            $latestTransaction = Transaction::orderBy('created_at', 'desc')->first();
            $lastSyncTime = $latestTransaction ? $latestTransaction->created_at : Carbon::now()->subHours(24);
            
            // Query API for transactions since last sync
            $payments = $this->clickPesa->queryAllPayments([
                'limit' => 100,
                'fromDate' => $lastSyncTime->format('Y-m-d H:i:s')
            ]);
            
            $newTransactionsCount = 0;
            $updatedTransactionsCount = 0;
            
            if (isset($payments['data']) && is_array($payments['data'])) {
                foreach ($payments['data'] as $payment) {
                    $orderReference = $payment['orderReference'] ?? null;
                    
                    if ($orderReference) {
                        $existingTransaction = Transaction::where('order_reference', $orderReference)->first();
                        
                        if (!$existingTransaction) {
                            // New transaction detected - capture it
                            $this->captureNewTransaction($payment);
                            $newTransactionsCount++;
                            
                            Log::info('New transaction captured', [
                                'orderReference' => $orderReference,
                                'status' => $payment['status'] ?? 'UNKNOWN',
                                'amount' => $payment['collectedAmount'] ?? $payment['amount'] ?? 0
                            ]);
                        } else {
                            // Update existing transaction if status changed
                            $statusChanged = $this->updateTransactionStatus($existingTransaction, $payment);
                            if ($statusChanged) {
                                $updatedTransactionsCount++;
                                
                                Log::info('Transaction status updated', [
                                    'orderReference' => $orderReference,
                                    'oldStatus' => $existingTransaction->status,
                                    'newStatus' => $payment['status'] ?? 'UNKNOWN'
                                ]);
                            }
                        }
                    }
                }
            }
            
            Log::info('Automatic API capture completed', [
                'new_transactions' => $newTransactionsCount,
                'updated_transactions' => $updatedTransactionsCount,
                'total_processed' => $newTransactionsCount + $updatedTransactionsCount
            ]);
            
            return response()->json([
                'success' => true,
                'new_transactions' => $newTransactionsCount,
                'updated_transactions' => $updatedTransactionsCount,
                'message' => 'Automatic capture completed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Automatic API capture failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Automatic capture failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Capture a new transaction from API
     */
    private function captureNewTransaction($payment)
    {
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
            'order_reference' => $payment['orderReference'] ?? null,
            'transaction_id' => $payment['id'] ?? null,
            'status' => $payment['status'] ?? 'UNKNOWN',
            'amount' => $payment['collectedAmount'] ?? $payment['amount'] ?? 0,
            'currency' => $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS',
            'phone' => $phone,
            'payer_name' => $payerName,
            'payment_method' => $payment['channel'] ?? $payment['paymentMethod'] ?? null,
            'description' => 'Auto-captured - ' . ($payment['description'] ?? 'Payment'),
            'type' => 'payment',
            'callback_data' => $payment,
            'created_at' => isset($payment['createdAt']) ? Carbon::parse($payment['createdAt']) : now(),
            'updated_at' => isset($payment['updatedAt']) ? Carbon::parse($payment['updatedAt']) : now(),
        ]);
    }
    
    /**
     * Update existing transaction status if changed
     */
    private function updateTransactionStatus($transaction, $payment)
    {
        $newStatus = $payment['status'] ?? 'UNKNOWN';
        
        if ($transaction->status !== $newStatus) {
            $oldStatus = $transaction->status;
            $transaction->status = $newStatus;
            $transaction->callback_data = $payment;
            $transaction->updated_at = isset($payment['updatedAt']) ? Carbon::parse($payment['updatedAt']) : now();
            $transaction->save();
            
            // Trigger notification for status change
            $this->notifyStatusChange($transaction, $oldStatus, $newStatus);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Notify about transaction status changes
     */
    private function notifyStatusChange($transaction, $oldStatus, $newStatus)
    {
        // Log the status change
        Log::info('Transaction status changed', [
            'order_reference' => $transaction->order_reference,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency
        ]);
        
        // You can add additional notification methods here:
        // - Send email notifications
        // - Send SMS notifications  
        // - Trigger webhooks
        // - Send push notifications
        // - Update dashboard in real-time
    }
    
    /**
     * Manual trigger for immediate capture
     */
    public function manualCapture()
    {
        $result = $this->autoCapture();
        
        if ($result->getStatusCode() === 200) {
            return back()->with('success', 'Manual capture completed successfully');
        } else {
            return back()->with('error', 'Manual capture failed');
        }
    }
    
    /**
     * Get capture status and statistics
     */
    public function captureStatus()
    {
        $totalTransactions = Transaction::count();
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $settledTransactions = Transaction::whereIn('status', ['SUCCESS', 'SETTLED'])->count();
        
        $lastSyncTime = Transaction::orderBy('created_at', 'desc')->value('created_at');
        
        return response()->json([
            'total_transactions' => $totalTransactions,
            'today_transactions' => $todayTransactions,
            'settled_transactions' => $settledTransactions,
            'last_sync_time' => $lastSyncTime ? Carbon::parse($lastSyncTime)->format('Y-m-d H:i:s') : null,
            'auto_capture_enabled' => true
        ]);
    }
    
    /**
     * Display the API capture dashboard
     */
    public function dashboard()
    {
        return view('admin.api-capture');
    }
}
