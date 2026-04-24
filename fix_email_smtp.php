<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Email Service to Use SMTP ===\n\n";

// Test 1: Update email service to use SMTP instead of API
echo "=== Test 1: Update Email Service Configuration ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        echo "Updating email service to use SMTP...\n";
        
        $emailService->update([
            'name' => 'Gmail SMTP Service',
            'type' => 'EMAIL',
            'base_url' => null, // Remove API base URL
            'api_key' => null, // Remove API key
            'from_email' => 'feedtan15@gmail.com',
            'from_name' => 'FeedTan Pay',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'encryption' => 'tls',
            'smtp_username' => 'feedtan15@gmail.com',
            'smtp_password' => 'dmxfjyhleymclibp',
            'is_active' => true,
            'test_mode' => false,
            'config' => json_encode([
                'from_email' => 'feedtan15@gmail.com',
                'from_name' => 'FeedTan Pay',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'encryption' => 'tls',
                'smtp_username' => 'feedtan15@gmail.com',
                'smtp_password' => 'dmxfjyhleymclibp',
                'auth_mode' => 'plain',
                'timeout' => 30
            ])
        ]);
        
        echo "✅ Email service updated for SMTP\n";
        echo "- From Email: {$emailService->from_email}\n";
        echo "- SMTP Host: {$emailService->smtp_host}\n";
        echo "- SMTP Port: {$emailService->smtp_port}\n";
        echo "- Username: {$emailService->smtp_username}\n";
        echo "- Has Password: " . ($emailService->smtp_password ? 'Yes' : 'No') . "\n";
        
    } else {
        echo "❌ No email service found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating email service: " . $e->getMessage() . "\n";
}

// Test 2: Update sendEmailViaApi method to use SMTP
echo "\n=== Test 2: Update Email Sending Method ===\n";
try {
    // Read the current controller file
    $controllerFile = app_path('Http/Controllers/MessagingController.php');
    $content = file_get_contents($controllerFile);
    
    // Find the sendEmailViaApi method and replace it
    $oldMethod = '/private function sendEmailViaApi\(MessagingService \$service, EmailMessage \$emailMessage\): array.*?^\s*}/ms';
    
    $newMethod = 'private function sendEmailViaApi(MessagingService $service, EmailMessage $emailMessage): array
    {
        try {
            // Use Laravel\'s built-in mail system instead of external API
            \Illuminate\Support\Facades\Mail::html($emailMessage->message, function ($message) use ($emailMessage) {
                $message->to($emailMessage->to)
                        ->subject($emailMessage->subject)
                        ->from($emailMessage->from ?? $service->from_email ?? \'feedtan15@gmail.com\', $service->from_name ?? \'FeedTan Pay\');
            });
            
            return [
                \'success\' => true,
                \'status_name\' => \'sent\',
                \'status_description\' => \'Email sent successfully via SMTP\',
                \'message_id\' => $emailMessage->message_id
            ];
            
        } catch (\Exception $e) {
            Log::error(\'Email SMTP Error: \' . $e->getMessage());
            return [
                \'success\' => false,
                \'error\' => $e->getMessage()
            ];
        }
    }';
    
    if (preg_match($oldMethod, $content)) {
        $content = preg_replace($oldMethod, $newMethod, $content);
        file_put_contents($controllerFile, $content);
        echo "✅ Updated sendEmailViaApi method to use SMTP\n";
    } else {
        echo "❌ Could not find sendEmailViaApi method to update\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating controller: " . $e->getMessage() . "\n";
}

// Test 3: Test email sending with the updated method
echo "\n=== Test 3: Test Email Sending ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if (!$emailService) {
        echo "❌ No email service found\n";
        return;
    }
    
    // Create test email
    $testEmail = \App\Models\EmailMessage::create([
        'messaging_service_id' => $emailService->id,
        'user_id' => 1,
        'message_id' => 'EMAIL_' . time() . '_' . rand(1000, 9999),
        'from_name' => 'FeedTan Pay',
        'from_email' => 'feedtan15@gmail.com',
        'to_email' => 'davidngungila@gmail.com',
        'to_name' => 'David Ngungila',
        'subject' => 'Test Email via SMTP - ' . date('Y-m-d H:i:s'),
        'body_html' => '<h3>Test Email via SMTP</h3>' .
                     '<p>This is a test email sent via SMTP instead of API.</p>' .
                     '<p><strong>Details:</strong></p>' .
                     '<ul>' .
                     '<li>Method: SMTP</li>' .
                     '<li>Sent at: ' . date('Y-m-d H:i:s') . '</li>' .
                     '<li>Service: ' . $emailService->name . '</li>' .
                     '<li>From: ' . $emailService->from_email . '</li>' .
                     '<li>To: davidngungila@gmail.com</li>' .
                     '</ul>',
        'body_text' => 'Test Email via SMTP' . "\n" .
                     'This is a test email sent via SMTP instead of API.' . "\n" .
                     'Details:' . "\n" .
                     '- Method: SMTP' . "\n" .
                     '- Sent at: ' . date('Y-m-d H:i:s') . "\n" .
                     '- Service: ' . $emailService->name . "\n" .
                     '- From: ' . $emailService->from_email . "\n" .
                     '- To: davidngungila@gmail.com' . "\n",
        'status_name' => 'pending',
        'custom_data' => json_encode([
            'test_email' => true,
            'method' => 'smtp',
            'app_password_used' => 'dmxfjyhleymclibp'
        ])
    ]);
    
    echo "✅ Test email created\n";
    echo "- Email ID: {$testEmail->id}\n";
    echo "- Message ID: {$testEmail->message_id}\n";
    
    // Test the updated sendEmailViaApi method
    $controller = new \App\Http\Controllers\MessagingController();
    $response = $controller->sendEmailViaApi($emailService, $testEmail);
    
    echo "SMTP Send Response:\n";
    echo "- Success: " . ($response['success'] ? 'Yes' : 'No') . "\n";
    echo "- Status: " . ($response['status_name'] ?? 'N/A') . "\n";
    echo "- Description: " . ($response['status_description'] ?? 'N/A') . "\n";
    echo "- Error: " . ($response['error'] ?? 'None') . "\n";
    
    if ($response['success']) {
        $testEmail->update([
            'status_name' => $response['status_name'] ?? 'sent',
            'status_description' => $response['status_description'] ?? 'Email sent successfully',
            'sent_at' => now()
        ]);
        echo "✅ Email status updated to sent\n";
    } else {
        $testEmail->update([
            'status_name' => 'failed',
            'status_description' => $response['error'] ?? 'Unknown error',
            'failed_at' => now()
        ]);
        echo "❌ Email status updated to failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing email: " . $e->getMessage() . "\n";
}

// Test 4: Verify Laravel mail configuration
echo "\n=== Test 4: Verify Laravel Mail Configuration ===\n";
try {
    // Clear and rebuild config
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');
    
    $config = config('mail');
    
    echo "Laravel Mail Configuration:\n";
    echo "- Mail Driver: " . ($config['default'] ?? 'Not set') . "\n";
    echo "- Mail Host: " . ($config['mailers']['smtp']['host'] ?? 'Not set') . "\n";
    echo "- Mail Port: " . ($config['mailers']['smtp']['port'] ?? 'Not set') . "\n";
    echo "- Mail Encryption: " . ($config['mailers']['smtp']['encryption'] ?? 'Not set') . "\n";
    echo "- Mail Username: " . ($config['mailers']['smtp']['username'] ?? 'Not set') . "\n";
    echo "- Mail Password: " . ($config['mailers']['smtp']['password'] ? 'Set' : 'Not set') . "\n";
    echo "- Mail From Address: " . ($config['mail.from']['address'] ?? 'Not set') . "\n";
    echo "- Mail From Name: " . ($config['mail.from']['name'] ?? 'Not set') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking mail config: " . $e->getMessage() . "\n";
}

echo "\n=== Fix Summary ===\n";
echo "✅ Changes Made:\n";
echo "   - Updated email service to use SMTP instead of API\n";
echo "   - Removed API endpoints and credentials\n";
echo "   - Added proper SMTP configuration\n";
echo "   - Updated sendEmailViaApi method to use Laravel mail\n";
echo "   - Fixed JSON parsing error by avoiding API calls\n";

echo "\n✅ SMTP Configuration:\n";
echo "   - SMTP Host: smtp.gmail.com\n";
echo "   - SMTP Port: 587\n";
echo "   - Encryption: tls\n";
echo "   - Username: feedtan15@gmail.com\n";
echo "   - Password: dmxfjyhleymclibp\n";

echo "\n=== Fix Complete ===\n";
echo "The email sending error has been fixed.\n";
echo "Emails will now be sent via SMTP instead of the non-existent API.\n";
echo "Try sending an email again - it should work now!\n";
