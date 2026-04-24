<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
        return view('report.overview');
    }
    
    public function reportBalance()
    {
        return view('report.balance');
    }
    
    public function reportStatement()
    {
        return view('report.statement');
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
