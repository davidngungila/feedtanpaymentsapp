<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Email Sending with Gmail App Password ===\n\n";

// Test 1: Check if email service is configured
echo "=== Test 1: Check Email Service Configuration ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->where('is_active', true)->first();
    
    if ($emailService) {
        echo "✅ Email service found:\n";
        echo "- Name: {$emailService->name}\n";
        echo "- From Email: {$emailService->from_email}\n";
        echo "- SMTP Host: {$emailService->smtp_host}\n";
        echo "- SMTP Port: {$emailService->smtp_port}\n";
        echo "- Encryption: {$emailService->encryption}\n";
        echo "- Username: {$emailService->smtp_username}\n";
        echo "- Has Password: " . ($emailService->smtp_password ? 'Yes' : 'No') . "\n";
        echo "- Is Active: " . ($emailService->is_active ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ No active email service found\n";
        echo "Need to configure email service first.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking email service: " . $e->getMessage() . "\n";
}

// Test 2: Configure Gmail service if not exists
echo "\n=== Test 2: Configure Gmail Service ===\n";
try {
    $gmailService = \App\Models\MessagingService::where('type', 'EMAIL')->where('name', 'Gmail Service')->first();
    
    if (!$gmailService) {
        echo "Creating Gmail service configuration...\n";
        
        $gmailService = \App\Models\MessagingService::create([
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
            'settings' => json_encode([
                'auth_mode' => 'plain',
                'timeout' => 30
            ])
        ]);
        
        echo "✅ Gmail service created successfully\n";
    } else {
        echo "✅ Gmail service already exists\n";
        
        // Update with new credentials
        $gmailService->update([
            'from_email' => 'feedtan15@gmail.com',
            'smtp_username' => 'feedtan15@gmail.com',
            'smtp_password' => 'dmxf jyhl eymc libp',
            'is_active' => true
        ]);
        
        echo "✅ Gmail service updated with new credentials\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error configuring Gmail service: " . $e->getMessage() . "\n";
}

// Test 3: Test email sending
echo "\n=== Test 3: Test Email Sending ===\n";
try {
    $testEmail = new \App\Models\EmailMessage();
    $testEmail->messaging_service_id = $gmailService->id;
    $testEmail->user_id = 1; // Admin user
    $testEmail->from_email = 'feedtan15@gmail.com';
    $testEmail->from_name = 'FeedTan Pay';
    $testEmail->to_email = 'davidngungila@gmail.com';
    $testEmail->to_name = 'David Ngungila';
    $testEmail->subject = 'Test Email from FeedTan Pay';
    $testEmail->message = 'This is a test email sent from FeedTan Pay system using Gmail SMTP.';
    $testEmail->message_type = 'text';
    $testEmail->status_name = 'pending';
    $testEmail->save();
    
    echo "✅ Test email created in database\n";
    echo "- Email ID: {$testEmail->id}\n";
    echo "- To: {$testEmail->to_email}\n";
    echo "- Subject: {$testEmail->subject}\n";
    
    // Try to send the email
    echo "\nAttempting to send email...\n";
    
    // Create a mock request for the sendEmail method
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'service_id' => $gmailService->id,
        'to' => 'davidngungila@gmail.com',
        'to_name' => 'David Ngungila',
        'subject' => 'Test Email from FeedTan Pay',
        'message' => 'This is a test email sent from FeedTan Pay system using Gmail SMTP.',
        'message_type' => 'text'
    ]);
    
    // Get the messaging controller
    $controller = new \App\Http\Controllers\MessagingController();
    
    // Call the sendEmail method
    $response = $controller->sendEmail($request);
    
    echo "Send Email Response:\n";
    echo "- Status Code: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        echo "- Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
        echo "- Message: " . ($data['message'] ?? 'No message') . "\n";
        
        if ($data['success']) {
            echo "✅ Email sent successfully!\n";
            echo "- Email ID: " . ($data['data']['email_id'] ?? 'N/A') . "\n";
            echo "- Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ Email sending failed\n";
        echo "- Response: " . $response->getContent() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error sending test email: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 4: Check email configuration
echo "\n=== Test 4: Email Configuration Test ===\n";
try {
    // Test Laravel mail configuration
    $config = config('mail');
    
    echo "Laravel Mail Configuration:\n";
    echo "- Mail Driver: " . ($config['default'] ?? 'Not set') . "\n";
    echo "- Mail Host: " . ($config['mailers']['smtp']['host'] ?? 'Not set') . "\n";
    echo "- Mail Port: " . ($config['mailers']['smtp']['port'] ?? 'Not set') . "\n";
    echo "- Mail Encryption: " . ($config['mailers']['smtp']['encryption'] ?? 'Not set') . "\n";
    echo "- Mail Username: " . ($config['mailers']['smtp']['username'] ?? 'Not set') . "\n";
    
    // Test if we can create a mail instance
    $mailer = app('mailer');
    echo "- Mailer Instance: " . ($mailer ? '✅ Available' : '❌ Not Available') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking mail configuration: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ Gmail Credentials:\n";
echo "   - Email: feedtan15@gmail.com\n";
echo "   - App Password: dmxf jyhl eymc libp\n";
echo "   - Test Recipient: davidngungila@gmail.com\n";

echo "\n✅ Next Steps:\n";
echo "1. Check if the email was sent successfully\n";
echo "2. Verify delivery to davidngungila@gmail.com\n";
echo "3. Check spam/junk folder if not received\n";
echo "4. Test with different recipients if needed\n";

echo "\n=== Test Complete ===\n";
echo "The email sending test has been completed.\n";
echo "Please check the recipient's inbox for the test email.\n";
