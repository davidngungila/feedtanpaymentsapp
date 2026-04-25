<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillPayController;
use App\Http\Controllers\StatementController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Account Settings routes
Route::get('/account-settings', [DashboardController::class, 'accountSettings'])->name('account-settings');
Route::get('/account-settings/notifications', [DashboardController::class, 'notifications'])->name('account-settings.notifications');
Route::get('/account-settings/connections', [DashboardController::class, 'connections'])->name('account-settings.connections');

// Payment routes
Route::get('/payments/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
Route::post('/payments/initiate', [PaymentController::class, 'processInitiate'])->name('payments.process.initiate');
Route::get('/payments/status', [PaymentController::class, 'status'])->name('payments.status');
Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');
Route::post('/payments/sync-api', [PaymentController::class, 'syncFromAPI'])->name('payments.sync.api');
Route::get('/payments/export/pdf', [PaymentController::class, 'exportPdf'])->name('payments.export.pdf');

// API Capture routes
Route::get('/api-capture/auto', [ApiCaptureController::class, 'autoCapture'])->name('api.capture.auto');
Route::get('/api-capture/manual', [ApiCaptureController::class, 'manualCapture'])->name('api.capture.manual');
Route::get('/api-capture/status', [ApiCaptureController::class, 'captureStatus'])->name('api.capture.status');
Route::get('/admin/api-capture', [ApiCaptureController::class, 'dashboard'])->name('admin.api.capture');

// Payout routes
Route::get('/payouts/initiate', [DashboardController::class, 'initiatePayout'])->name('payouts.initiate');
Route::get('/payouts/history', [DashboardController::class, 'payoutHistory'])->name('payouts.history');

// BillPay routes
Route::get('/billpay/all', [BillPayController::class, 'index'])->name('billpay.all');
Route::get('/billpay/create', [BillPayController::class, 'create'])->name('billpay.create');
Route::post('/billpay/create', [BillPayController::class, 'store'])->name('billpay.store');
Route::get('/billpay/{billPayNumber}', [BillPayController::class, 'show'])->name('billpay.show');
Route::patch('/billpay/{billPayNumber}', [BillPayController::class, 'update'])->name('billpay.update');

// Statement routes - redirect to report statement
Route::get('/statement', [DashboardController::class, 'reportStatement'])->name('statement.index');
Route::post('/sync/payments', [StatementController::class, 'syncPayments'])->name('sync.payments');

// Report export routes
Route::get('/report/statement/export', [DashboardController::class, 'exportStatement'])->name('report.statement.export');

// Report API routes
Route::get('/report/statement/transactions', [DashboardController::class, 'getMonthTransactions'])->name('report.statement.transactions');

// Report routes
Route::get('/report/balance', [DashboardController::class, 'reportBalance'])->name('report.balance');
Route::get('/report/statement', [DashboardController::class, 'reportStatement'])->name('report.statement');

// Members routes
Route::prefix('members')->name('members.')->group(function () {
    Route::get('/all', [DashboardController::class, 'membersAll'])->name('all');
    Route::get('/add', [DashboardController::class, 'membersAdd'])->name('add');
    Route::get('/profiles', [DashboardController::class, 'membersProfiles'])->name('profiles');
    Route::get('/groups', [DashboardController::class, 'membersGroups'])->name('groups');
    Route::get('/contributions', [DashboardController::class, 'membersContributions'])->name('contributions');
    Route::get('/reports', [DashboardController::class, 'membersReports'])->name('reports');
});


// Investment routes
Route::prefix('investment')->name('investment.')->group(function () {
    Route::get('/view', [DashboardController::class, 'investmentView'])->name('view');
    Route::get('/new', [DashboardController::class, 'investmentNew'])->name('new');
    Route::get('/plans', [DashboardController::class, 'investmentPlans'])->name('plans');
    Route::get('/returns', [DashboardController::class, 'investmentReturns'])->name('returns');
    Route::get('/history', [DashboardController::class, 'investmentHistory'])->name('history');
    Route::get('/reports', [DashboardController::class, 'investmentReports'])->name('reports');
});

// Savings routes
Route::prefix('savings')->name('savings.')->group(function () {
    Route::get('/deposit', [DashboardController::class, 'savingsDeposit'])->name('deposit');
    Route::get('/accounts', [DashboardController::class, 'savingsAccounts'])->name('accounts');
    Route::get('/history', [DashboardController::class, 'savingsHistory'])->name('history');
    Route::get('/withdrawal', [DashboardController::class, 'savingsWithdrawal'])->name('withdrawal');
    Route::get('/reports', [DashboardController::class, 'savingsReports'])->name('reports');
});

// Loans routes
Route::prefix('loans')->name('loans.')->group(function () {
    Route::get('/apply', [DashboardController::class, 'loansApply'])->name('apply');
    Route::get('/products', [DashboardController::class, 'loansProducts'])->name('products');
    Route::get('/my', [DashboardController::class, 'loansMy'])->name('my');
    Route::get('/repayments', [DashboardController::class, 'loansRepayments'])->name('repayments');
    Route::get('/schedule', [DashboardController::class, 'loansSchedule'])->name('schedule');
    Route::get('/reports', [DashboardController::class, 'loansReports'])->name('reports');
});

// Welfare routes
Route::prefix('welfare')->name('welfare.')->group(function () {
    Route::get('/contribute', [DashboardController::class, 'welfareContribute'])->name('contribute');
    Route::get('/balance', [DashboardController::class, 'welfareBalance'])->name('balance');
    Route::get('/support', [DashboardController::class, 'welfareSupport'])->name('support');
    Route::get('/history', [DashboardController::class, 'welfareHistory'])->name('history');
    Route::get('/reports', [DashboardController::class, 'welfareReports'])->name('reports');
});

// Shares routes
Route::prefix('shares')->name('shares.')->group(function () {
    Route::get('/buy', [DashboardController::class, 'sharesBuy'])->name('buy');
    Route::get('/my', [DashboardController::class, 'sharesMy'])->name('my');
    Route::get('/value', [DashboardController::class, 'sharesValue'])->name('value');
    Route::get('/dividends', [DashboardController::class, 'sharesDividends'])->name('dividends');
    Route::get('/transfers', [DashboardController::class, 'sharesTransfers'])->name('transfers');
    Route::get('/reports', [DashboardController::class, 'sharesReports'])->name('reports');
});

// System Settings Routes (Admin only)
Route::prefix('system-settings')->name('system-settings.')->middleware(['admin'])->group(function () {
    Route::get('/general', [DashboardController::class, 'systemGeneral'])->name('general');
    Route::post('/general', [DashboardController::class, 'storeGeneralSetting'])->name('general.store');
    Route::put('/general/{id}', [DashboardController::class, 'updateGeneralSetting'])->name('general.update');
    Route::delete('/general/{id}', [DashboardController::class, 'deleteGeneralSetting'])->name('general.delete');
    
    Route::get('/payment', [DashboardController::class, 'systemPayment'])->name('payment');
    Route::post('/payment', [DashboardController::class, 'storePaymentSetting'])->name('payment.store');
    Route::put('/payment/{id}', [DashboardController::class, 'updatePaymentSetting'])->name('payment.update');
    Route::delete('/payment/{id}', [DashboardController::class, 'deletePaymentSetting'])->name('payment.delete');
    Route::get('/security', [DashboardController::class, 'systemSecurity'])->name('security');
    Route::get('/security-logs', [DashboardController::class, 'systemSecurityLogs'])->name('security-logs');
    Route::get('/notification', [DashboardController::class, 'systemNotification'])->name('notification');
    Route::get('/user', [DashboardController::class, 'systemUser'])->name('user');
    Route::get('/integration', [DashboardController::class, 'systemIntegration'])->name('integration');
    Route::get('/integration/create', [DashboardController::class, 'createIntegration'])->name('integration.create');
    Route::get('/integration/edit/{id}', [DashboardController::class, 'editIntegration'])->name('integration.edit');
    Route::get('/integration/sms-api', [DashboardController::class, 'integrationSmsApi'])->name('integration.sms-api');
    Route::get('/integration/email-api', [DashboardController::class, 'integrationEmailApi'])->name('integration.email-api');
    Route::get('/integration/payment-api', [DashboardController::class, 'integrationPaymentApi'])->name('integration.payment-api');
    Route::get('/maintenance', [DashboardController::class, 'systemMaintenance'])->name('maintenance');
    Route::get('/health', [DashboardController::class, 'systemHealth'])->name('health');
    Route::get('/audit', [DashboardController::class, 'systemAudit'])->name('audit');
    
    // Security Center Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/authentication', [DashboardController::class, 'securityAuthentication'])->name('authentication');
        Route::get('/fraud', [DashboardController::class, 'securityFraud'])->name('fraud');
        Route::get('/access', [DashboardController::class, 'securityAccess'])->name('access');
        Route::get('/device', [DashboardController::class, 'securityDevice'])->name('device');
        Route::get('/session', [DashboardController::class, 'securitySession'])->name('session');
        Route::get('/protection', [DashboardController::class, 'securityProtection'])->name('protection');
        Route::get('/alerts', [DashboardController::class, 'securityAlerts'])->name('alerts');
        Route::get('/tracking', [DashboardController::class, 'securityTracking'])->name('tracking');
    });
    
    // Admin user management
    Route::get('/create-admin', [AuthController::class, 'showCreateAdminForm'])->name('create-admin');
    Route::post('/create-admin', [AuthController::class, 'createAdmin']);
});

// API routes for settings
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/general-settings/{id}', [DashboardController::class, 'getGeneralSetting']);
    Route::delete('/general-settings/{id}', [DashboardController::class, 'deleteGeneralSetting']);
    Route::get('/payment-settings/{id}', [DashboardController::class, 'getPaymentSetting']);
    Route::delete('/payment-settings/{id}', [DashboardController::class, 'deletePaymentSetting']);
});

// Test route to verify Laravel routing works
Route::get('/test-messaging-simple', function() {
    return response()->json(['message' => 'Laravel routing works!', 'timestamp' => now()]);
});

// Messaging System Routes
Route::prefix('messaging')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [MessagingController::class, 'dashboard'])->name('messaging.dashboard');
    
    // SMS Routes
    Route::get('/sms', [MessagingController::class, 'smsIndex'])->name('messaging.sms');
    Route::get('/sms/logs', [MessagingController::class, 'smsLogsPage'])->name('messaging.sms.logs');
    Route::post('/sms/send', [MessagingController::class, 'sendSms'])->name('messaging.sms.send');
    
    // Email Routes
    Route::get('/email', [MessagingController::class, 'emailIndex'])->name('messaging.email');
    Route::post('/email/send', [MessagingController::class, 'sendEmail'])->name('messaging.email.send');
    
    // Services Management Routes
    Route::get('/services', [MessagingController::class, 'servicesIndex'])->name('messaging.services');
    Route::get('/services/{serviceId}', [MessagingController::class, 'getService'])->name('messaging.services.show');
    Route::post('/services', [MessagingController::class, 'storeService'])->name('messaging.services.store');
    Route::put('/services/{service}', [MessagingController::class, 'updateService'])->name('messaging.services.update');
    Route::delete('/services/{service}', [MessagingController::class, 'deleteService'])->name('messaging.services.delete');
    Route::post('/services/{serviceId}/test', [MessagingController::class, 'testService'])->name('messaging.services.test');
    Route::post('/services/{serviceId}/toggle/{activate}', [MessagingController::class, 'toggleServiceStatus'])->name('messaging.services.toggle');
});

// Other routes
Route::get('/forgot-password', [DashboardController::class, 'forgotPassword'])->name('forgot-password');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
Route::get('/security', [DashboardController::class, 'security'])->name('security');

// Profile API routes
Route::get('/api/profile', [ProfileController::class, 'getProfile'])->name('api.profile.get');
Route::post('/api/profile/update', [ProfileController::class, 'update'])->name('api.profile.update');
Route::post('/api/profile/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('api.profile.upload-avatar');
Route::delete('/api/profile/delete-avatar', [ProfileController::class, 'deleteAvatar'])->name('api.profile.delete-avatar');
Route::post('/api/change-password', [ProfileController::class, 'changePassword'])->name('api.change-password');
Route::get('/api/download-user-data', [ProfileController::class, 'downloadUserData'])->name('api.download-user-data');

// API route for getting user role
Route::get('/api/user-role', [AuthController::class, 'getUserRole'])->middleware('auth');

// API route for SMS message details
Route::get('/api/sms-messages/{messageId}', [MessagingController::class, 'getSmsMessage']);

// API route for SMS message export
Route::get('/api/sms-messages/{messageId}/export', [MessagingController::class, 'exportSmsMessage'])->middleware('auth');

// API routes for SMS logs and balance
Route::get('/api/sms-logs', [MessagingController::class, 'getSmsLogs'])->middleware('auth');
Route::get('/api/sms-logs/export', [MessagingController::class, 'exportSmsLogs'])->middleware('auth');
Route::get('/api/sms-balance', [MessagingController::class, 'getSmsBalance'])->middleware('auth');

// API routes for email templates
Route::post('/api/email-template/preview', [MessagingController::class, 'previewEmailTemplate'])->middleware('auth');
Route::get('/api/email-template/{id}', [MessagingController::class, 'getEmailTemplate'])->middleware('auth');

// API routes for email messages
Route::get('/api/email-messages/{messageId}', [MessagingController::class, 'getEmailMessage'])->middleware('auth');
Route::get('/api/email-messages/{messageId}/content', [MessagingController::class, 'getEmailMessageContent'])->middleware('auth');
Route::get('/api/email-messages/{messageId}/export', [MessagingController::class, 'exportEmailMessage'])->middleware('auth');
});
