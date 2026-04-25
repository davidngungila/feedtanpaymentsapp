<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;

class PaymentController extends Controller
{
    protected ClickPesaAPIService $clickPesa;
    protected MessagingServiceAPI $messaging;

    public function __construct(ClickPesaAPIService $clickPesa, MessagingServiceAPI $messaging)
    {
        $this->clickPesa = $clickPesa;
        $this->messaging = $messaging;
    }

    /**
     * Show the payment initiation form
     */
    public function initiate()
    {
        return view('payments.create');
    }

    /**
     * Show the payment initiation form (alias for initiate)
     */
    public function create()
    {
        return view('payments.create');
    }

    /**
     * Process payment initiation
     */
    public function store(Request $request)
    {
        $request->validate([
            'payer_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'amount' => 'required|numeric|min:100|max:1000000',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            // Format phone number
            $formattedPhone = $this->clickPesa->validatePhoneNumber($request->phone_number);
            if (!$formattedPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format. Please use Tanzania format (e.g., 0622239304 or 255712345678)'
                ], 400);
            }

            // Generate order reference
            $orderReference = $this->clickPesa->generateOrderReference();

            // Preview the payment
            $preview = $this->clickPesa->previewUSSDPush($request->amount, $orderReference, $formattedPhone, true);
            
            if (empty($preview['activeMethods'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active payment methods available for this phone number'
                ], 400);
            }

            // Save transaction to database
            $transaction = Transaction::create([
                'order_reference' => $orderReference,
                'status' => 'PROCESSING',
                'amount' => $request->amount,
                'currency' => 'TZS',
                'phone' => $formattedPhone,
                'payer_name' => $request->payer_name,
                'description' => $request->description,
                'type' => 'payment',
                'user_id' => auth()->id(),
            ]);

            // Initiate the payment
            $customerDetails = [
                'customerName' => $request->payer_name,
                'description' => $request->description
            ];
            
            $payment = $this->clickPesa->initiateUSSDPush($request->amount, $orderReference, $formattedPhone, null, $customerDetails);

            // Check if we're in fallback mode
            $isFallbackMode = isset($payment['fallback_mode']) && $payment['fallback_mode'] === true;

            // Update transaction with API response
            if (isset($payment['id'])) {
                $transaction->update([
                    'transaction_id' => $payment['id'],
                    'status' => $payment['status'],
                    'payment_method' => $payment['channel'] ?? null,
                    'callback_data' => $payment,
                ]);
            }

            // Return appropriate response based on whether we used fallback mode
            if ($isFallbackMode) {
                Log::warning('Payment initiated in fallback mode - SMS not sent', [
                    'order_reference' => $orderReference,
                    'phone' => $formattedPhone,
                    'amount' => $request->amount
                ]);

                return response()->json([
                    'success' => true,
                    'order_reference' => $orderReference,
                    'message' => 'Payment queued successfully! The payment system is currently experiencing technical difficulties. Your payment will be processed when the system is restored. Reference: ' . $orderReference,
                    'fallback_mode' => true,
                    'warning' => 'API temporarily unavailable - payment queued for processing'
                ]);
            }

            // Send SMS notification only for real successful payments
            try {
                $this->sendPaymentInitiationNotification($formattedPhone, $orderReference, $request->amount, $request->payer_name);
                Log::info('SMS notification sent for successful payment', [
                    'order_reference' => $orderReference,
                    'phone' => $formattedPhone,
                    'amount' => $request->amount
                ]);
            } catch (\Exception $e) {
                Log::warning('SMS notification failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'order_reference' => $orderReference,
                'message' => 'Payment initiated successfully! Please check your phone for the STK push notification.'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            // Handle specific error types
            $errorMessage = $e->getMessage();
            $warningType = null;

            if (strpos($errorMessage, 'insufficient') !== false || strpos($errorMessage, 'balance') !== false) {
                $warningType = 'insufficient_funds';
                $errorMessage = 'Payment failed due to insufficient funds. Please check your account balance and try again.';
            } elseif (strpos($errorMessage, 'Invalid phone number') !== false) {
                $errorMessage = 'Invalid phone number format. Please use Tanzania format (e.g., 0622239304 or 255712345678)';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'warning_type' => $warningType
            ], 500);
        }
    }

    /**
     * Show payment status
     */
    public function status(Request $request)
    {
        $orderReference = $request->get('reference');
        
        if (!$orderReference) {
            return view('payments.status', [
                'error' => 'Order reference is required'
            ]);
        }

        try {
            // Get transaction from database
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            
            $paymentData = null;
            
            if ($transaction) {
                // Try to get latest status from API
                try {
                    $apiData = $this->clickPesa->queryPaymentStatus($orderReference);
                    if ($apiData && isset($apiData['id'])) {
                        $paymentData = $apiData;
                        
                        // Update database if status changed
                        if ($transaction->status !== $apiData['status']) {
                            $transaction->update([
                                'status' => $apiData['status'],
                                'payment_method' => $apiData['channel'] ?? null,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch payment status from API', [
                        'order_reference' => $orderReference,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // If API failed, use database data
                if (!$paymentData) {
                    $paymentData = [
                        'orderReference' => $transaction->order_reference,
                        'transaction_id' => $transaction->transaction_id,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'phone' => $transaction->phone,
                        'payer_name' => $transaction->payer_name,
                        'description' => $transaction->description,
                        'payment_method' => $transaction->payment_method,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                    ];
                }
            }

            return view('payments.status', compact('paymentData', 'orderReference'));

        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'order_reference' => $orderReference,
                'error' => $e->getMessage()
            ]);

            return view('payments.status', [
                'error' => 'Failed to check payment status. Please try again later.'
            ]);
        }
    }

    /**
     * Display payment history
     */
    public function history(Request $request)
    {
        try {
            // Try to sync latest data from API, but don't fail if it doesn't work
            try {
                $this->syncLatestDataFromAPI();
            } catch (\Exception $syncError) {
                Log::warning('API sync failed, continuing with existing data', ['error' => $syncError->getMessage()]);
            }
            
            // Get current account balance
            try {
                $balance = $this->clickPesa->getAccountBalance();
            } catch (\Exception $balanceError) {
                Log::warning('Balance fetch failed, using empty balance', ['error' => $balanceError->getMessage()]);
                $balance = [];
            }
            
            // Handle the correct balance structure: {"balances":[{"currency":"TZS","balance":118232}]}
            if (isset($balance['balances']) && is_array($balance['balances'])) {
                // Use the first balance (TZS) or find TZS specifically
                $tzsBalance = collect($balance['balances'])->firstWhere('currency', 'TZS');
                if ($tzsBalance) {
                    $balance = [$tzsBalance];
                } else {
                    $balance = [$balance['balances'][0]]; // fallback to first balance
                }
            } else {
                $balance = [];
            }
            
            $search = $request->get('search');
            $paymentMethod = $request->get('payment_method');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            // Only show settled transactions (SUCCESS or SETTLED)
            $query = Transaction::whereIn('status', ['SUCCESS', 'SETTLED']);

            // Apply filters
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_reference', 'like', "%{$search}%")
                      ->orWhere('payer_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($paymentMethod) {
                $query->where('payment_method', 'like', "%{$paymentMethod}%");
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Get counts for stats (only settled)
            $totalCount = $query->count();
            $successCount = $query->clone()->where('status', 'SUCCESS')->count();
            $settledCount = $query->clone()->where('status', 'SETTLED')->count();

            // Get all results without pagination
            $payments = $query->orderBy('created_at', 'desc')->get();

            return view('payments.history', compact('payments', 'totalCount', 'successCount', 'settledCount', 'balance'));

        } catch (\Exception $e) {
            Log::error('Payment history error', ['error' => $e->getMessage()]);
            
            return view('payments.history', [
                'payments' => collect([]),
                'error' => 'Failed to load payment history. Please try again.',
                'totalCount' => 0,
                'successCount' => 0,
                'settledCount' => 0,
                'balance' => [],
            ]);
        }
    }

    /**
     * Sync latest data from API
     */
    private function syncLatestDataFromAPI()
    {
        try {
            $payments = $this->clickPesa->queryAllPayments(['limit' => 100]);
            
            if (isset($payments['data']) && is_array($payments['data'])) {
                foreach ($payments['data'] as $payment) {
                    $orderReference = $payment['orderReference'] ?? null;
                    
                    if ($orderReference) {
                        $existingTransaction = Transaction::where('order_reference', $orderReference)->first();
                        
                        if (!$existingTransaction) {
                            // Create new transaction with improved data capture
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
                                'description' => 'API sync - ' . ($payment['description'] ?? 'Payment'),
                                'type' => 'payment',
                                'callback_data' => $payment,
                            ]);
                        } else {
                            // Update existing transaction with missing data
                            $payerName = $payment['customer']['customerName'] 
                                ?? $payment['payerName'] 
                                ?? $payment['customerName'] 
                                ?? $payment['sender']['accountName'] 
                                ?? $payment['sender']['name']
                                ?? $payment['customer']['name']
                                ?? $existingTransaction->payer_name;
                            
                            $phone = $payment['paymentPhoneNumber'] 
                                ?? $payment['customer']['customerPhoneNumber'] 
                                ?? $payment['phoneNumber'] 
                                ?? $payment['sender']['accountNumber']
                                ?? $payment['customer']['phone']
                                ?? $existingTransaction->phone;
                            
                            $existingTransaction->update([
                                'status' => $payment['status'] ?? $existingTransaction->status,
                                'payment_method' => $payment['channel'] ?? $payment['paymentMethod'] ?? $existingTransaction->payment_method,
                                'payer_name' => $payerName,
                                'phone' => $phone,
                                'callback_data' => $payment,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't stop the page loading
            \Log::error('API sync failed in PaymentController: ' . $e->getMessage());
        }
    }

    /**
     * Sync transactions from API
     */
    public function syncFromAPI(Request $request)
    {
        try {
            // Use the same sync logic as DashboardController
            $payments = $this->clickPesa->queryAllPayments(['limit' => 100]);
            $syncedCount = 0;
            
            if (isset($payments['data']) && is_array($payments['data'])) {
                foreach ($payments['data'] as $payment) {
                    $orderReference = $payment['orderReference'] ?? null;
                    
                    if ($orderReference) {
                        $existingTransaction = Transaction::where('order_reference', $orderReference)->first();
                        
                        if (!$existingTransaction) {
                            // Create new transaction with improved data capture
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
                                'description' => 'Manual API sync - ' . ($payment['description'] ?? 'Payment'),
                                'type' => 'payment',
                                'callback_data' => $payment,
                            ]);
                            $syncedCount++;
                        } else {
                            // Update existing transaction with missing data
                            $payerName = $payment['customer']['customerName'] 
                                ?? $payment['payerName'] 
                                ?? $payment['customerName'] 
                                ?? $payment['sender']['accountName'] 
                                ?? $payment['sender']['name']
                                ?? $payment['customer']['name']
                                ?? $existingTransaction->payer_name;
                            
                            $phone = $payment['paymentPhoneNumber'] 
                                ?? $payment['customer']['customerPhoneNumber'] 
                                ?? $payment['phoneNumber'] 
                                ?? $payment['sender']['accountNumber']
                                ?? $payment['customer']['phone']
                                ?? $existingTransaction->phone;
                            
                            $existingTransaction->update([
                                'status' => $payment['status'] ?? $existingTransaction->status,
                                'payment_method' => $payment['channel'] ?? $payment['paymentMethod'] ?? $existingTransaction->payment_method,
                                'payer_name' => $payerName,
                                'phone' => $phone,
                                'callback_data' => $payment,
                            ]);
                            $syncedCount++;
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'synced' => $syncedCount,
                'message' => "Successfully synced {$syncedCount} transactions from API"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Manual API sync failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync from API: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export payment receipt as PDF
     */
    public function exportPdf(Request $request)
    {
        $orderReference = $request->get('order_reference');
        
        if (!$orderReference) {
            return redirect()->back()->with('error', 'Order reference is required');
        }

        try {
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            
            if (!$transaction) {
                return redirect()->back()->with('error', 'Transaction not found');
            }

            if (!$transaction->isSuccessful()) {
                return redirect()->back()->with('error', 'Only successful payments can be exported');
            }

            // Generate PDF receipt
            return $this->generateReceiptPDF($transaction);

        } catch (\Exception $e) {
            Log::error('PDF export failed', [
                'order_reference' => $orderReference,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF receipt');
        }
    }

    /**
     * Send SMS notification for payment initiation
     */
    private function sendPaymentInitiationNotification(string $phoneNumber, string $orderReference, float $amount, string $payerName)
    {
        $message = "FEEDTAN: Payment Initiated\n" .
                  "Order Ref: {$orderReference}\n" .
                  "Amount: " . number_format($amount, 2) . " TZS\n" .
                  "Payer: {$payerName}\n" .
                  "Phone: {$phoneNumber}\n" .
                  "Status: USSD Push Sent\n" .
                  "Please complete payment on your phone.";

        $this->messaging->sendSMS($phoneNumber, $message);
    }

    /**
     * Generate PDF receipt
     */
    private function generateReceiptPDF(Transaction $transaction)
    {
        // Create a printer-friendly HTML receipt suitable for small receipt printers
        $html = view('payments.receipt-print', compact('transaction'))->render();
        
        // Initialize DomPDF
        $dompdf = new Dompdf();
        
        // Configure for receipt printer size (80mm width for better fit)
        $dompdf->setPaper([0, 0, 226.77, 1000], 'portrait'); // 80mm width in points
        
        // Load HTML
        $dompdf->loadHtml($html);
        
        // Render PDF
        $dompdf->render();
        
        // Generate filename
        $filename = "receipt_{$transaction->order_reference}.pdf";
        
        // Stream PDF
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
