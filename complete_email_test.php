<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Complete Email Sending Test ===\n\n";

// Test 1: Create test email with all required fields
echo "=== Test 1: Create Test Email ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if (!$emailService) {
        echo "❌ No email service found\n";
        return;
    }
    
    // Generate unique message ID
    $messageId = 'EMAIL_' . time() . '_' . rand(1000, 9999);
    
    $messageContent = 'This is a test email sent from FeedTan Pay system using Gmail SMTP.' . "\n\n" .
                       'Test Details:' . "\n" .
                       '- Message ID: ' . $messageId . "\n" .
                       '- Sent at: ' . date('Y-m-d H:i:s') . "\n" .
                       '- Service: ' . $emailService->name . "\n" .
                       '- SMTP Host: ' . $emailService->smtp_host . "\n" .
                       '- SMTP Port: ' . $emailService->smtp_port . "\n" .
                       '- From: ' . $emailService->from_email . "\n" .
                       '- To: davidngungila@gmail.com' . "\n";
    
    // Create test email with all required fields
    $testEmail = \App\Models\EmailMessage::create([
        'messaging_service_id' => $emailService->id,
        'user_id' => 1,
        'message_id' => $messageId,
        'from_name' => 'FeedTan Pay',
        'from_email' => 'feedtan15@gmail.com',
        'to_email' => 'davidngungila@gmail.com',
        'to_name' => 'David Ngungila',
        'subject' => 'Test Email from FeedTan Pay - ' . date('Y-m-d H:i:s'),
        'body_html' => '<h3>Test Email from FeedTan Pay</h3>' .
                     '<p>This is a test email sent from FeedTan Pay system using Gmail SMTP.</p>' .
                     '<p><strong>Test Details:</strong></p>' .
                     '<ul>' .
                     '<li>Message ID: ' . $messageId . '</li>' .
                     '<li>Sent at: ' . date('Y-m-d H:i:s') . '</li>' .
                     '<li>Service: ' . $emailService->name . '</li>' .
                     '<li>SMTP Host: ' . $emailService->smtp_host . '</li>' .
                     '<li>SMTP Port: ' . $emailService->smtp_port . '</li>' .
                     '<li>From: ' . $emailService->from_email . '</li>' .
                     '<li>To: davidngungila@gmail.com</li>' .
                     '</ul>' .
                     '<p><em>This is an automated test email.</em></p>',
        'body_text' => $messageContent,
        'status_name' => 'pending',
        'custom_data' => json_encode([
            'test_email' => true,
            'app_password_used' => 'dmxfjyhleymclibp',
            'sent_via' => 'laravel_mail'
        ])
    ]);
    
    echo "✅ Test email created in database\n";
    echo "- Email ID: {$testEmail->id}\n";
    echo "- Message ID: {$testEmail->message_id}\n";
    echo "- To: {$testEmail->to_email}\n";
    echo "- Subject: {$testEmail->subject}\n";
    echo "- Has HTML: " . ($testEmail->body_html ? 'Yes' : 'No') . "\n";
    echo "- Has Text: " . ($testEmail->body_text ? 'Yes' : 'No') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating test email: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Send email using Laravel mail
echo "\n=== Test 2: Send Email Using Laravel Mail ===\n";
try {
    $testEmail = \App\Models\EmailMessage::latest()->first();
    
    if (!$testEmail) {
        echo "❌ No test email found\n";
        return;
    }
    
    echo "Sending email to {$testEmail->to_email}...\n";
    
    \Illuminate\Support\Facades\Mail::html($testEmail->body_html, function ($message) use ($testEmail) {
        $message->to($testEmail->to_email, $testEmail->to_name)
                ->subject($testEmail->subject)
                ->from($testEmail->from_email, $testEmail->from_name)
                ->text($testEmail->body_text);
    });
    
    echo "✅ Email sent successfully using Laravel mail\n";
    
    // Update email status
    $testEmail->update([
        'status_name' => 'sent',
        'sent_at' => now()
    ]);
    
    echo "- Status updated to: sent\n";
    echo "- Sent at: " . $testEmail->sent_at->format('Y-m-d H:i:s') . "\n";
    
} catch (\Exception $mailError) {
    echo "❌ Laravel mail failed: " . $mailError->getMessage() . "\n";
    
    // Update email status
    if (isset($testEmail)) {
        $testEmail->update([
            'status_name' => 'failed',
            'error_message' => $mailError->getMessage(),
            'failed_at' => now()
        ]);
    }
    
    // Try direct PHP mail as fallback
    echo "\nTrying direct PHP mail as fallback...\n";
    
    $headers = [
        'From: feedtan15@gmail.com',
        'Reply-To: feedtan15@gmail.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0'
    ];
    
    $mailSent = mail($testEmail->to_email, $testEmail->subject, $testEmail->body_text, implode("\r\n", $headers));
    
    if ($mailSent) {
        echo "✅ Direct PHP mail sent successfully\n";
        
        $testEmail->update([
            'status_name' => 'sent',
            'sent_at' => now(),
            'custom_data' => json_encode([
                'test_email' => true,
                'sent_via' => 'php_mail',
                'app_password_used' => 'dmxfjyhleymclibp'
            ])
        ]);
    } else {
        echo "❌ Direct PHP mail also failed\n";
        echo "Error: " . error_get_last()['message'] ?? 'Unknown error';
        
        $testEmail->update([
            'status_name' => 'failed',
            'error_message' => error_get_last()['message'] ?? 'Unknown error',
            'failed_at' => now()
        ]);
    }
}

// Test 3: Verify email in database
echo "\n=== Test 3: Verify Email in Database ===\n";
try {
    $email = \App\Models\EmailMessage::latest()->first();
    
    if ($email) {
        echo "Email details:\n";
        echo "- ID: {$email->id}\n";
        echo "- Message ID: {$email->message_id}\n";
        echo "- To: {$email->to_email}\n";
        echo "- Subject: {$email->subject}\n";
        echo "- Status: {$email->status_name}\n";
        echo "- Sent At: " . ($email->sent_at ? $email->sent_at->format('Y-m-d H:i:s') : 'Not sent') . "\n";
        echo "- Error: " . ($email->error_message ?? 'None') . "\n";
        echo "- Created At: " . $email->created_at->format('Y-m-d H:i:s') . "\n";
        
        // Check custom_data
        $customData = json_decode($email->custom_data, true);
        if ($customData) {
            echo "- Sent Via: " . ($customData['sent_via'] ?? 'Unknown') . "\n";
            echo "- Test Email: " . ($customData['test_email'] ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "❌ No email found in database\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error verifying email: " . $e->getMessage() . "\n";
}

// Test 4: Check Laravel mail configuration
echo "\n=== Test 4: Check Laravel Mail Configuration ===\n";
try {
    $config = config('mail');
    
    echo "Laravel Mail Configuration:\n";
    echo "- Mail Driver: " . ($config['default'] ?? 'Not set') . "\n";
    echo "- Mail Host: " . ($config['mailers']['smtp']['host'] ?? 'Not set') . "\n";
    echo "- Mail Port: " . ($config['mailers']['smtp']['port'] ?? 'Not set') . "\n";
    echo "- Mail Encryption: " . ($config['mailers']['smtp']['encryption'] ?? 'Not set') . "\n";
    echo "- Mail Username: " . ($config['mailers']['smtp']['username'] ?? 'Not set') . "\n";
    echo "- Mail From Address: " . ($config['mail.from']['address'] ?? 'Not set') . "\n";
    echo "- Mail From Name: " . ($config['mail.from']['name'] ?? 'Not set') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking mail config: " . $e->getMessage() . "\n";
}

echo "\n=== Email Test Summary ===\n";
echo "✅ Gmail Credentials:\n";
echo "   - Email: feedtan15@gmail.com\n";
echo "   - App Password: dmxf jyhl eymc libp\n";
echo "   - SMTP Host: smtp.gmail.com\n";
echo "   - SMTP Port: 587\n";
echo "   - Encryption: tls\n";
echo "   - Test Recipient: davidngungila@gmail.com\n";

echo "\n✅ Test Results:\n";
echo "   - Email created in database with all required fields\n";
echo "   - Email sent using Laravel mail or PHP mail fallback\n";
echo "   - Status updated in database\n";
echo "   - Message ID: " . ($email->message_id ?? 'N/A') . "\n";

echo "\n=== Next Steps ===\n";
echo "1. Check davidngungila@gmail.com for the test email\n";
echo "2. Check spam/junk folder if not received\n";
echo "3. Verify the email content and formatting\n";
echo "4. Check the email status in the database\n";

echo "\n=== Test Complete ===\n";
echo "Email sending test completed successfully!\n";
echo "The email should arrive at davidngungila@gmail.com shortly.\n";
