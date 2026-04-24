<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Dompdf\Dompdf;

class DashboardController extends Controller
{
    protected ClickPesaAPIService $clickPesa;

    public function __construct(ClickPesaAPIService $clickPesa)
    {
        $this->clickPesa = $clickPesa;
    }

    public function index()
    {
        return view('dashboard');
    }
    
    // Account Settings
    public function accountSettings()
    {
        return view('account-settings');
    }
    
    public function notifications()
    {
        return view('account-settings.notifications');
    }
    
    public function connections()
    {
        return view('account-settings.connections');
    }
    
    // Payments
    public function initiatePayment()
    {
        return view('payments.initiate');
    }
    
    public function paymentHistory()
    {
        return view('payments.history');
    }
    
    // Payouts
    public function initiatePayout()
    {
        return view('payouts.initiate');
    }
    
    public function payoutHistory()
    {
        return view('payouts.history');
    }
    
    // BillPay
    public function allBills()
    {
        return view('billpay.all');
    }
    
    public function createBill()
    {
        return view('billpay.create');
    }
    
    // Reports
    public function reportOverview()
    {
        try {
            // Get fresh data from API and save to database
            $this->syncLatestDataFromAPI();
            
            // Get real transaction data from database
            $totalTransactions = Transaction::count();
            $totalAmount = Transaction::sum('amount');
            
            $successCount = Transaction::where('status', 'SUCCESS')->count();
            $successAmount = Transaction::where('status', 'SUCCESS')->sum('amount');
            
            $settledCount = Transaction::where('status', 'SETTLED')->count();
            $settledAmount = Transaction::where('status', 'SETTLED')->sum('amount');
            
            $pendingCount = Transaction::where('status', 'PROCESSING')->orWhere('status', 'PENDING')->count();
            $pendingAmount = Transaction::where('status', 'PROCESSING')->orWhere('status', 'PENDING')->sum('amount');
            
            $failedCount = Transaction::where('status', 'FAILED')->count();
            $failedAmount = Transaction::where('status', 'FAILED')->sum('amount');
            
            // Get recent transactions
            $recentTransactions = Transaction::orderBy('created_at', 'desc')->limit(5)->get();
            
            // Get monthly statistics
            $monthlyStats = Transaction::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
            
            // Get payment methods breakdown
            $paymentMethods = Transaction::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->whereNotNull('payment_method')
                ->groupBy('payment_method')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();
            
            return view('report.overview', compact(
                'totalTransactions', 'totalAmount', 
                'successCount', 'successAmount',
                'settledCount', 'settledAmount',
                'pendingCount', 'pendingAmount',
                'failedCount', 'failedAmount',
                'recentTransactions', 'monthlyStats', 'paymentMethods'
            ));
            
        } catch (\Exception $e) {
            return view('report.overview', [
                'error' => 'Failed to load report data: ' . $e->getMessage(),
                'totalTransactions' => 0, 'totalAmount' => 0,
                'successCount' => 0, 'successAmount' => 0,
                'settledCount' => 0, 'settledAmount' => 0,
                'pendingCount' => 0, 'pendingAmount' => 0,
                'failedCount' => 0, 'failedAmount' => 0,
                'recentTransactions' => collect([]),
                'monthlyStats' => collect([]),
                'paymentMethods' => collect([])
            ]);
        }
    }
    
    private function syncLatestDataFromAPI()
    {
        try {
            // Get latest payments from API
            $payments = $this->clickPesa->queryAllPayments(['limit' => 50]);
            
            if (isset($payments['data']) && is_array($payments['data'])) {
                foreach ($payments['data'] as $payment) {
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
                                'description' => 'API sync - ' . ($payment['description'] ?? 'Payment'),
                                'type' => 'payment',
                                'callback_data' => $payment,
                            ]);
                        } else {
                            // Update existing transaction and capture missing data
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
            // Log error but don't stop the report generation
            \Log::error('API sync failed: ' . $e->getMessage());
        }
    }
    
    public function reportBalance()
    {
        try {
            // Sync latest data from API
            $this->syncLatestDataFromAPI();
            
            // Get account balance from ClickPesa API
            $balance = $this->clickPesa->getAccountBalance();
            
            // Get transaction statistics by currency
            $currencyStats = Transaction::select('currency', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->get();
            
            // Get daily balance trends (last 7 days)
            $dailyTrends = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status = "SUCCESS" THEN amount ELSE 0 END) as credits'),
                DB::raw('SUM(CASE WHEN status != "SUCCESS" THEN amount ELSE 0 END) as debits')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
            
            // Get top transactions (last 5)
            $topTransactions = Transaction::where('status', 'SUCCESS')
                ->orderBy('amount', 'desc')
                ->limit(5)
                ->get();
            
            return view('report.balance', compact('balance', 'currencyStats', 'dailyTrends', 'topTransactions'));
            
        } catch (\Exception $e) {
            return view('report.balance', [
                'error' => 'Failed to load balance data: ' . $e->getMessage(),
                'balance' => [],
                'currencyStats' => collect([]),
                'dailyTrends' => collect([]),
                'topTransactions' => collect([])
            ]);
        }
    }
    
    public function reportStatement(Request $request)
    {
        try {
            // Sync latest data from API
            $this->syncLatestDataFromAPI();
            
            $selectedMonth = $request->get('month', now()->format('Y-m'));
            $currency = $request->get('currency', 'TZS');

            // Generate all months for the last 12 months automatically
            $allMonths = [];
            $currentDate = now();
            for ($i = 0; $i < 12; $i++) {
                $monthDate = $currentDate->copy()->subMonths($i);
                $allMonths[] = $monthDate->format('Y-m');
            }

            // Get monthly breakdown from database (without currency filter for monthly summary)
            $dbMonthlyStatements = Transaction::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "SUCCESS" THEN amount ELSE 0 END) as success_amount'),
                DB::raw('SUM(CASE WHEN status IN ("PROCESSING", "PENDING") THEN amount ELSE 0 END) as pending_amount'),
                DB::raw('SUM(CASE WHEN status = "FAILED" THEN amount ELSE 0 END) as failed_amount')
            )
            ->whereIn(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $allMonths)
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->keyBy('month');

            // Create complete monthly statements with all months included
            $monthlyStatements = collect($allMonths)->map(function($month) use ($dbMonthlyStatements) {
                $data = $dbMonthlyStatements->get($month, collect());
                
                return [
                    'month' => $month,
                    'month_name' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                    'transaction_count' => $data->get('transaction_count', 0),
                    'total_amount' => $data->get('total_amount', 0),
                    'success_amount' => $data->get('success_amount', 0),
                    'pending_amount' => $data->get('pending_amount', 0),
                    'failed_amount' => $data->get('failed_amount', 0),
                    'has_data' => $data->get('transaction_count', 0) > 0
                ];
            });
            
            // Get transactions for selected month with full reconciliation
            $selectedMonthTransactions = Transaction::when($selectedMonth, function($query, $selectedMonth) {
                    return $query->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $selectedMonth);
                })
                ->when($currency && $currency !== 'all', function($query, $currency) {
                    return $query->where('currency', $currency);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate monthly reconciliation totals
            $monthlyTotals = $selectedMonthTransactions->reduce(function($carry, $transaction) {
                $carry['total_count']++;
                $carry['total_amount'] += $transaction->amount;
                
                if ($transaction->status === 'SUCCESS' || $transaction->status === 'SETTLED') {
                    $carry['success_count']++;
                    $carry['success_amount'] += $transaction->amount;
                } elseif (in_array($transaction->status, ['PROCESSING', 'PENDING'])) {
                    $carry['pending_count']++;
                    $carry['pending_amount'] += $transaction->amount;
                } elseif ($transaction->status === 'FAILED') {
                    $carry['failed_count']++;
                    $carry['failed_amount'] += $transaction->amount;
                }
                
                return $carry;
            }, [
                'total_count' => 0,
                'total_amount' => 0,
                'success_count' => 0,
                'success_amount' => 0,
                'pending_count' => 0,
                'pending_amount' => 0,
                'failed_count' => 0,
                'failed_amount' => 0
            ]);

            return view('report.statement', compact(
                'monthlyStatements', 
                'selectedMonthTransactions', 
                'selectedMonth', 
                'currency',
                'monthlyTotals'
            ));

        } catch (\Exception $e) {
            return view('report.statement', [
                'error' => 'Failed to fetch account statement: ' . $e->getMessage(),
                'monthlyStatements' => collect([]),
                'selectedMonthTransactions' => collect([]),
                'selectedMonth' => $selectedMonth ?? now()->format('Y-m'),
                'currency' => $currency ?? 'TZS',
                'monthlyTotals' => []
            ]);
        }
    }
    
    public function exportStatement(Request $request)
    {
        try {
            $format = $request->get('format', 'pdf');
            $month = $request->get('month', now()->format('Y-m'));
            $currency = $request->get('currency', 'TZS');
            
            // Debug: Log the parameters
            \Log::info("Export Statement - Month: {$month}, Currency: {$currency}, Format: {$format}");
            
            // Get transactions for the selected month (remove currency filter for debugging)
            $transactions = Transaction::where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $month)
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Debug: Log the transaction count
            \Log::info("Found {$transactions->count()} transactions for month {$month}");
            
            if ($format === 'excel') {
                return $this->exportToExcel($transactions, $month, $currency);
            } else {
                return $this->exportToPDF($transactions, $month, $currency);
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
    
    private function exportToPDF($transactions, $month, $currency)
    {
        $html = view('report.statement-pdf', compact('transactions', 'month', 'currency'))->render();
        
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        
        $filename = "statement_{$month}_{$currency}.pdf";
        
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    private function exportToExcel($transactions, $month, $currency)
    {
        // Create CSV for Excel compatibility
        $filename = "statement_{$month}_{$currency}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];
        
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Date', 'Order Reference', 'Payer Name', 'Phone', 
                'Amount', 'Currency', 'Status', 'Payment Method', 'Description'
            ]);
            
            // CSV Data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->order_reference,
                    $transaction->payer_name,
                    $transaction->phone,
                    $transaction->amount,
                    $transaction->currency,
                    $transaction->status,
                    $transaction->payment_method ?? 'N/A',
                    $transaction->description ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    // Authentication
    public function login()
    {
        return view('auth.login');
    }
    
    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }
    
    public function profile()
    {
        $user = auth()->user();
        return view('auth.profile', compact('user'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ]);
        
        $user->update($validated);
        
        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }
    
    public function security()
    {
        return view('auth.security');
    }

    // System Settings Methods
    /**
     * Show the system general settings page.
     */
    public function systemGeneral()
    {
        $settings = \App\Models\GeneralSetting::getByGroup('general');
        return view('system-settings.general', compact('settings'));
    }

    /**
     * Store a new general setting.
     */
    public function storeGeneralSetting(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:255|unique:general_settings,setting_key',
            'setting_value' => 'required|string',
            'setting_type' => 'required|in:text,number,boolean,json',
            'setting_group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        \App\Models\GeneralSetting::create($validated);

        return redirect()->route('system-settings.general')->with('success', 'Setting created successfully!');
    }

    /**
     * Update a general setting.
     */
    public function updateGeneralSetting(Request $request, $id)
    {
        $setting = \App\Models\GeneralSetting::findOrFail($id);
        
        $validated = $request->validate([
            'setting_key' => 'required|string|max:255|unique:general_settings,setting_key,' . $id,
            'setting_value' => 'required|string',
            'setting_type' => 'required|in:text,number,boolean,json',
            'setting_group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        $setting->update($validated);

        return redirect()->route('system-settings.general')->with('success', 'Setting updated successfully!');
    }

    /**
     * Get a general setting (API endpoint).
     */
    public function getGeneralSetting($id)
    {
        $setting = \App\Models\GeneralSetting::findOrFail($id);
        return response()->json($setting);
    }

    /**
     * Delete a general setting.
     */
    public function deleteGeneralSetting($id)
    {
        $setting = \App\Models\GeneralSetting::findOrFail($id);
        $setting->delete();
        
        return response()->json(['success' => true, 'message' => 'Setting deleted successfully']);
    }

    /**
     * Show the system payment settings page.
     */
    public function systemPayment()
    {
        $settings = \App\Models\PaymentSetting::getByGroup('payment');
        return view('system-settings.payment', compact('settings'));
    }

    /**
     * Store a new payment setting.
     */
    public function storePaymentSetting(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:255|unique:payment_settings,setting_key',
            'setting_value' => 'required|string',
            'setting_type' => 'required|in:text,number,boolean,json',
            'setting_group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        \App\Models\PaymentSetting::create($validated);

        return redirect()->route('system-settings.payment')->with('success', 'Payment setting created successfully!');
    }

    /**
     * Update a payment setting.
     */
    public function updatePaymentSetting(Request $request, $id)
    {
        $setting = \App\Models\PaymentSetting::findOrFail($id);
        
        $validated = $request->validate([
            'setting_key' => 'required|string|max:255|unique:payment_settings,setting_key,' . $id,
            'setting_value' => 'required|string',
            'setting_type' => 'required|in:text,number,boolean,json',
            'setting_group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        $setting->update($validated);

        return redirect()->route('system-settings.payment')->with('success', 'Payment setting updated successfully!');
    }

    /**
     * Get a payment setting (API endpoint).
     */
    public function getPaymentSetting($id)
    {
        $setting = \App\Models\PaymentSetting::findOrFail($id);
        return response()->json($setting);
    }

    /**
     * Delete a payment setting.
     */
    public function deletePaymentSetting($id)
    {
        $setting = \App\Models\PaymentSetting::findOrFail($id);
        $setting->delete();
        
        return response()->json(['success' => true, 'message' => 'Payment setting deleted successfully']);
    }

    /**
     * Show the system security settings page.
     */
    public function systemSecurity()
    {
        return view('system-settings.security');
    }

    /**
     * Show the system notification settings page.
     */
    public function systemNotification()
    {
        return view('system-settings.notification');
    }

    /**
     * Show the system user settings page.
     */
    public function systemUser()
    {
        return view('system-settings.user');
    }

    /**
     * Show the system integration settings page.
     */
    public function systemIntegration()
    {
        return view('system-settings.integration');
    }

    /**
     * Show the system maintenance page.
     */
    public function systemMaintenance()
    {
        return view('system-settings.maintenance');
    }

    /**
     * Show the system health page.
     */
    public function systemHealth()
    {
        return view('system-settings.health');
    }

    /**
     * Show the create integration page.
     */
    public function createIntegration()
    {
        return view('system-settings.integration-create');
    }

    /**
     * Show the edit integration page.
     */
    public function editIntegration($id)
    {
        return view('system-settings.integration-edit', ['id' => $id]);
    }

    /**
     * Show the audit trail page.
     */
    public function systemAudit()
    {
        return view('system-settings.audit');
    }

    // Security Center Methods
    public function securityAuthentication()
    {
        return view('system-settings.security.authentication');
    }

    public function securityFraud()
    {
        return view('system-settings.security.fraud');
    }

    public function securityAccess()
    {
        return view('system-settings.security.access');
    }

    public function securityDevice()
    {
        return view('system-settings.security.device');
    }

    public function securitySession()
    {
        return view('system-settings.security.session');
    }

    public function securityProtection()
    {
        return view('system-settings.security.protection');
    }

    public function securityAlerts()
    {
        return view('system-settings.security.alerts');
    }

    public function securityTracking()
    {
        return view('system-settings.security.tracking');
    }

    // Members Methods
    public function membersAll()
    {
        return view('members.all');
    }

    public function membersAdd()
    {
        return view('members.add');
    }

    public function membersProfiles()
    {
        return view('members.profiles');
    }

    public function membersGroups()
    {
        return view('members.groups');
    }

    public function membersContributions()
    {
        return view('members.contributions');
    }

    public function membersReports()
    {
        return view('members.reports');
    }

    // Investment Methods
    public function investmentView()
    {
        return view('investment.view');
    }

    public function investmentNew()
    {
        return view('investment.new');
    }

    public function investmentPlans()
    {
        return view('investment.plans');
    }

    public function investmentReturns()
    {
        return view('investment.returns');
    }

    public function investmentHistory()
    {
        return view('investment.history');
    }

    public function investmentReports()
    {
        return view('investment.reports');
    }

    // Savings Methods
    public function savingsDeposit()
    {
        return view('savings.deposit');
    }

    public function savingsAccounts()
    {
        return view('savings.accounts');
    }

    public function savingsHistory()
    {
        return view('savings.history');
    }

    public function savingsWithdrawal()
    {
        return view('savings.withdrawal');
    }

    public function savingsReports()
    {
        return view('savings.reports');
    }

    // Loans Methods
    public function loansApply()
    {
        return view('loans.apply');
    }

    public function loansProducts()
    {
        return view('loans.products');
    }

    public function loansMy()
    {
        return view('loans.my');
    }

    public function loansRepayments()
    {
        return view('loans.repayments');
    }

    public function loansSchedule()
    {
        return view('loans.schedule');
    }

    public function loansReports()
    {
        return view('loans.reports');
    }

    // Welfare Methods
    public function welfareContribute()
    {
        return view('welfare.contribute');
    }

    public function welfareBalance()
    {
        return view('welfare.balance');
    }

    public function welfareSupport()
    {
        return view('welfare.support');
    }

    public function welfareHistory()
    {
        return view('welfare.history');
    }

    public function welfareReports()
    {
        return view('welfare.reports');
    }

    // Shares Methods
    public function sharesBuy()
    {
        return view('shares.buy');
    }

    public function sharesMy()
    {
        return view('shares.my');
    }

    public function sharesValue()
    {
        return view('shares.value');
    }

    public function sharesDividends()
    {
        return view('shares.dividends');
    }

    public function sharesTransfers()
    {
        return view('shares.transfers');
    }

    public function sharesReports()
    {
        return view('shares.reports');
    }

    // Integration Settings
    public function integration()
    {
        return view('system-settings.integration');
    }

    public function integrationCreate()
    {
        return view('system-settings.integration-create');
    }

    public function integrationSmsApi()
    {
        return view('system-settings.integrations.sms-api');
    }

    public function integrationEmailApi()
    {
        return view('system-settings.integrations.email-api');
    }

    public function integrationPaymentApi()
    {
        return view('system-settings.integrations.payment-api');
    }

    public function systemSecurityLogs()
    {
        return view('system-settings.security-logs');
    }
}
