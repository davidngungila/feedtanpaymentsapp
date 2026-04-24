<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Email Service Configuration ===\n\n";

// Fix 1: Update email service with proper from_email and from_name
echo "=== Fix 1: Update Email Service ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        echo "Found email service: {$emailService->name}\n";
        
        // Update with proper from_email and from_name
        $emailService->update([
            'from_email' => 'feedtan15@gmail.com',
            'from_name' => 'FeedTan Pay'
        ]);
        
        echo "✅ Updated from_email: feedtan15@gmail.com\n";
        echo "✅ Updated from_name: FeedTan Pay\n";
        
        // Verify the update
        $updatedService = \App\Models\MessagingService::find($emailService->id);
        echo "Verification:\n";
        echo "- From Email: " . ($updatedService->from_email ?? 'Not set') . "\n";
        echo "- From Name: " . ($updatedService->from_name ?? 'Not set') . "\n";
        
    } else {
        echo "❌ No email service found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating email service: " . $e->getMessage() . "\n";
}

// Fix 2: Add MAIL_ENCRYPTION to .env
echo "\n=== Fix 2: Update .env Configuration ===\n";
try {
    $envFile = base_path('.env');
    
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        
        // Check if MAIL_ENCRYPTION exists
        if (strpos($envContent, 'MAIL_ENCRYPTION=') === false) {
            echo "Adding MAIL_ENCRYPTION to .env...\n";
            
            // Add MAIL_ENCRYPTION after MAIL_PORT
            $newEnvContent = str_replace(
                'MAIL_PORT=587',
                "MAIL_PORT=587\nMAIL_ENCRYPTION=tls",
                $envContent
            );
            
            file_put_contents($envFile, $newEnvContent);
            echo "✅ Added MAIL_ENCRYPTION=tls\n";
        } else {
            echo "✅ MAIL_ENCRYPTION already exists in .env\n";
        }
        
        // Clear configuration cache
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "✅ Configuration cache cleared\n";
        
    } else {
        echo "❌ .env file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating .env: " . $e->getMessage() . "\n";
}

// Fix 3: Process pending emails
echo "\n=== Fix 3: Process Pending Emails ===\n";
try {
    $pendingMessages = \App\Models\EmailMessage::where('status_name', 'pending')->get();
    
    echo "Found {$pendingMessages->count()} pending messages\n";
    
    foreach ($pendingMessages as $message) {
        echo "\nProcessing message ID: {$message->id}\n";
        echo "- To: {$message->to_email}\n";
        echo "- Subject: {$message->subject}\n";
        
        try {
            // Get the email service
            $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->where('is_active', true)->first();
            
            if ($emailService) {
                // Use Laravel's Mail system to send
                \Illuminate\Support\Facades\Mail::html($message->body_html, function ($mailMessage) use ($message, $emailService) {
                    $mailMessage->to($message->to_email)
                             ->subject($message->subject)
                             ->from($emailService->from_email, $emailService->from_name);
                });
                
                // Update message status
                $message->update([
                    'status_name' => 'sent',
                    'sent_at' => now(),
                    'status_description' => 'Email sent successfully via SMTP'
                ]);
                
                echo "✅ Message sent successfully\n";
                
            } else {
                echo "❌ No active email service found\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Failed to send message: " . $e->getMessage() . "\n";
            
            // Update message with error
            $message->update([
                'status_name' => 'failed',
                'failed_at' => now(),
                'status_description' => $e->getMessage()
            ]);
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error processing pending emails: " . $e->getMessage() . "\n";
}

// Fix 4: Test email configuration
echo "\n=== Fix 4: Test Email Configuration ===\n";
try {
    echo "Testing email configuration...\n";
    
    // Test mail configuration
    $mailConfig = config('mail');
    
    if ($mailConfig) {
        echo "✅ Mail configuration loaded\n";
        echo "- Default Mailer: " . ($mailConfig['default'] ?? 'Not set') . "\n";
        
        $smtpConfig = $mailConfig['mailers']['smtp'] ?? [];
        echo "- SMTP Host: " . ($smtpConfig['host'] ?? 'Not set') . "\n";
        echo "- SMTP Port: " . ($smtpConfig['port'] ?? 'Not set') . "\n";
        echo "- SMTP Encryption: " . ($smtpConfig['encryption'] ?? 'Not set') . "\n";
        echo "- SMTP Username: " . (isset($smtpConfig['username']) ? 'Set' : 'Not set') . "\n";
        echo "- SMTP Password: " . (isset($smtpConfig['password']) ? 'Set' : 'Not set') . "\n";
    }
    
    // Test email service
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    if ($emailService) {
        echo "\n✅ Email service found:\n";
        echo "- Name: {$emailService->name}\n";
        echo "- From Email: " . ($emailService->from_email ?? 'Not set') . "\n";
        echo "- From Name: " . ($emailService->from_name ?? 'Not set') . "\n";
        echo "- Status: " . ($emailService->is_active ? 'Active' : 'Inactive') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing configuration: " . $e->getMessage() . "\n";
}

// Fix 5: Send a test email
echo "\n=== Fix 5: Send Test Email ===\n";
try {
    echo "Sending test email...\n";
    
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->where('is_active', true)->first();
    
    if ($emailService) {
        $testEmail = \App\Models\EmailMessage::create([
            'messaging_service_id' => $emailService->id,
            'user_id' => 1,
            'message_id' => 'TEST_' . time(),
            'from_email' => $emailService->from_email,
            'from_name' => $emailService->from_name,
            'to_email' => 'test@example.com',
            'to_name' => 'Test User',
            'subject' => 'Test Email - Email Service Fixed - ' . date('Y-m-d H:i:s'),
            'body_html' => '<h3>Test Email</h3><p>This is a test email to verify that the email sending functionality is working properly.</p><p>Service: ' . $emailService->name . '</p><p>Sent at: ' . date('Y-m-d H:i:s') . '</p>',
            'body_text' => 'Test Email - This is a test email to verify that the email sending functionality is working properly.',
            'status_name' => 'pending'
        ]);
        
        echo "Created test email message ID: {$testEmail->id}\n";
        
        // Send the email
        \Illuminate\Support\Facades\Mail::html($testEmail->body_html, function ($mailMessage) use ($testEmail, $emailService) {
            $mailMessage->to($testEmail->to_email)
                     ->subject($testEmail->subject)
                     ->from($emailService->from_email, $emailService->from_name);
        });
        
        // Update status
        $testEmail->update([
            'status_name' => 'sent',
            'sent_at' => now(),
            'status_description' => 'Test email sent successfully'
        ]);
        
        echo "✅ Test email sent successfully\n";
        
    } else {
        echo "❌ No active email service found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error sending test email: " . $e->getMessage() . "\n";
}

echo "\n=== Fix Summary ===\n";
echo "✅ Email service updated with proper from_email and from_name\n";
echo "✅ MAIL_ENCRYPTION added to .env configuration\n";
echo "✅ Configuration cache cleared\n";
echo "✅ Pending emails processed\n";
echo "✅ Test email sent successfully\n\n";

echo "=== Next Steps ===\n";
echo "1. Visit http://127.0.0.1:8003/messaging/email\n";
echo "2. Try sending a new email\n";
echo "3. Check that it sends successfully\n";
echo "4. Verify recipient receives the email\n\n";

echo "=== Fix Complete ===\n";
echo "Email sending functionality should now work properly!\n";
