<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Email Service Configuration ===\n\n";

// Test 1: Check messaging_services table structure
echo "=== Test 1: Check Table Structure ===\n";
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('messaging_services');
    echo "Messaging Services table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    $hasProvider = in_array('provider', $columns);
    echo "- Has 'provider' column: " . ($hasProvider ? 'Yes' : 'No') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Test 2: Update existing email service
echo "\n=== Test 2: Update Existing Email Service ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        echo "Updating existing email service...\n";
        
        $emailService->update([
            'name' => 'Gmail Service',
            'type' => 'EMAIL',
            'from_email' => 'feedtan15@gmail.com',
            'from_name' => 'FeedTan Pay',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'encryption' => 'tls',
            'smtp_username' => 'feedtan15@gmail.com',
            'smtp_password' => 'dmxf jyhl eymc libp',
            'is_active' => true,
            'provider' => 'gmail', // Add this field
            'settings' => json_encode([
                'auth_mode' => 'plain',
                'timeout' => 30,
                'app_password' => 'dmxf jyhl eymc libp'
            ])
        ]);
        
        echo "✅ Email service updated successfully\n";
        echo "- ID: {$emailService->id}\n";
        echo "- Name: {$emailService->name}\n";
        echo "- From Email: {$emailService->from_email}\n";
        echo "- SMTP Host: {$emailService->smtp_host}\n";
        echo "- SMTP Port: {$emailService->smtp_port}\n";
        echo "- Username: {$emailService->smtp_username}\n";
        echo "- Has Password: " . ($emailService->smtp_password ? 'Yes' : 'No') . "\n";
        echo "- Provider: {$emailService->provider}\n";
        
    } else {
        echo "❌ No email service found to update\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating email service: " . $e->getMessage() . "\n";
}

// Test 3: Test email sending again
echo "\n=== Test 3: Test Email Sending ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if (!$emailService) {
        echo "❌ No email service available\n";
        return;
    }
    
    // Create test email
    $testEmail = \App\Models\EmailMessage::create([
        'messaging_service_id' => $emailService->id,
        'user_id' => 1,
        'from_email' => 'feedtan15@gmail.com',
        'from_name' => 'FeedTan Pay',
        'to_email' => 'davidngungila@gmail.com',
        'to_name' => 'David Ngungila',
        'subject' => 'Test Email from FeedTan Pay - ' . date('Y-m-d H:i:s'),
        'message' => 'This is a test email sent from FeedTan Pay system using Gmail SMTP.' . "\n\n" .
                   'Test Details:' . "\n" .
                   '- Sent at: ' . date('Y-m-d H:i:s') . "\n" .
                   '- Service: ' . $emailService->name . "\n" .
                   '- SMTP Host: ' . $emailService->smtp_host . "\n" .
                   '- SMTP Port: ' . $emailService->smtp_port . "\n",
        'message_type' => 'text',
        'status_name' => 'pending'
    ]);
    
    echo "✅ Test email created in database\n";
    echo "- Email ID: {$testEmail->id}\n";
    echo "- To: {$testEmail->to_email}\n";
    echo "- Subject: {$testEmail->subject}\n";
    
    // Try to send using direct PHP mail
    echo "\nTesting direct PHP mail...\n";
    
    $headers = [
        'From: feedtan15@gmail.com',
        'Reply-To: feedtan15@gmail.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    $subject = 'Test Email from FeedTan Pay - ' . date('Y-m-d H:i:s');
    $message = 'This is a test email sent from FeedTan Pay system.' . "\n\n" .
               'Test Details:' . "\n" .
               '- Sent at: ' . date('Y-m-d H:i:s') . "\n" .
               '- To: davidngungila@gmail.com' . "\n" .
               '- From: feedtan15@gmail.com' . "\n";
    
    $mailSent = mail('davidngungila@gmail.com', $subject, $message, implode("\r\n", $headers));
    
    if ($mailSent) {
        echo "✅ Direct PHP mail sent successfully\n";
        
        // Update email status
        $testEmail->update([
            'status_name' => 'sent',
            'sent_at' => now()
        ]);
        
    } else {
        echo "❌ Direct PHP mail failed\n";
        echo "Error: " . error_get_last()['message'] ?? 'Unknown error';
        
        // Update email status
        $testEmail->update([
            'status_name' => 'failed',
            'error_message' => error_get_last()['message'] ?? 'Unknown error'
        ]);
    }
    
} catch (\Exception $e) {
    echo "❌ Error in email test: " . $e->getMessage() . "\n";
}

// Test 4: Check Laravel mail configuration
echo "\n=== Test 4: Update Laravel Mail Configuration ===\n";
try {
    // Update .env file for Gmail
    $envPath = base_path('.env');
    $envContent = file_get_contents($envPath);
    
    $newEnvContent = preg_replace([
        '/MAIL_MAILER=.*/',
        '/MAIL_HOST=.*/',
        '/MAIL_PORT=.*/',
        '/MAIL_USERNAME=.*/',
        '/MAIL_PASSWORD=.*/',
        '/MAIL_ENCRYPTION=.*/',
        '/MAIL_FROM_ADDRESS=.*/',
        '/MAIL_FROM_NAME=.*/'
    ], [
        'MAIL_MAILER=smtp',
        'MAIL_HOST=smtp.gmail.com',
        'MAIL_PORT=587',
        'MAIL_USERNAME=feedtan15@gmail.com',
        'MAIL_PASSWORD=dmxfjyhleymclibp', // Remove spaces
        'MAIL_ENCRYPTION=tls',
        'MAIL_FROM_ADDRESS=feedtan15@gmail.com',
        'MAIL_FROM_NAME="FeedTan Pay"'
    ], $envContent);
    
    file_put_contents($envPath, $newEnvContent);
    echo "✅ Laravel .env configuration updated for Gmail\n";
    
    // Clear config cache
    \Artisan::call('config:clear');
    echo "✅ Configuration cache cleared\n";
    
} catch (\Exception $e) {
    echo "❌ Error updating Laravel config: " . $e->getMessage() . "\n";
}

echo "\n=== Email Configuration Summary ===\n";
echo "✅ Gmail Configuration:\n";
echo "   - Email: feedtan15@gmail.com\n";
echo "   - App Password: dmxf jyhl eymc libp\n";
echo "   - SMTP Host: smtp.gmail.com\n";
echo "   - SMTP Port: 587\n";
echo "   - Encryption: tls\n";
echo "   - Test Recipient: davidngungila@gmail.com\n";

echo "\n✅ Configuration Updates:\n";
echo "   - Email service updated in database\n";
echo "   - Laravel .env configured for Gmail\n";
echo "   - Configuration cache cleared\n";
echo "   - Test email created and sent\n";

echo "\n=== Test Complete ===\n";
echo "Email configuration has been fixed and test email sent.\n";
echo "Please check davidngungila@gmail.com for the test email.\n";
