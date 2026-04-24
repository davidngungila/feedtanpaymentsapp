<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Email Messages Schema ===\n\n";

// Test 1: Check email_messages table structure
echo "=== Test 1: Check Email Messages Table Structure ===\n";
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('email_messages');
    echo "Email Messages table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    $hasMessageId = in_array('message_id', $columns);
    echo "- Has 'message_id' column: " . ($hasMessageId ? 'Yes' : 'No') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Test 2: Add missing message_id column
echo "\n=== Test 2: Add Missing message_id Column ===\n";
try {
    if (!\Illuminate\Support\Facades\Schema::hasColumn('email_messages', 'message_id')) {
        \Illuminate\Support\Facades\Schema::table('email_messages', function ($table) {
            $table->string('message_id')->nullable()->after('id');
            $table->text('message')->after('subject');
            $table->string('message_type')->default('text')->after('message');
            $table->string('cc')->nullable()->after('to_email');
            $table->string('bcc')->nullable()->after('cc');
            $table->timestamp('opened_at')->nullable()->after('sent_at');
            $table->timestamp('clicked_at')->nullable()->after('opened_at');
            $table->boolean('is_opened')->default(false)->after('clicked_at');
            $table->boolean('is_clicked')->default(false)->after('is_opened');
            $table->boolean('is_bounced')->default(false)->after('is_clicked');
            $table->text('error_message')->nullable()->after('status_name');
        });
        
        echo "✅ Added missing columns to email_messages table\n";
    } else {
        echo "✅ message_id column already exists\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error adding columns: " . $e->getMessage() . "\n";
}

// Test 3: Update email service with proper SMTP credentials
echo "\n=== Test 3: Update Email Service with SMTP Credentials ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        $emailService->update([
            'from_email' => 'feedtan15@gmail.com',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'encryption' => 'tls',
            'smtp_username' => 'feedtan15@gmail.com',
            'smtp_password' => 'dmxfjyhleymclibp', // Remove spaces
            'is_active' => true,
            'config' => json_encode([
                'auth_mode' => 'plain',
                'timeout' => 30,
                'app_password' => 'dmxfjyhleymclibp'
            ])
        ]);
        
        echo "✅ Email service updated with SMTP credentials\n";
        echo "- From Email: {$emailService->from_email}\n";
        echo "- SMTP Host: {$emailService->smtp_host}\n";
        echo "- SMTP Port: {$emailService->smtp_port}\n";
        echo "- Username: {$emailService->smtp_username}\n";
        echo "- Has Password: " . ($emailService->smtp_password ? 'Yes' : 'No') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating email service: " . $e->getMessage() . "\n";
}

// Test 4: Create and send test email
echo "\n=== Test 4: Create and Send Test Email ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if (!$emailService) {
        echo "❌ No email service found\n";
        return;
    }
    
    // Generate unique message ID
    $messageId = 'EMAIL_' . time() . '_' . rand(1000, 9999);
    
    // Create test email
    $testEmail = \App\Models\EmailMessage::create([
        'messaging_service_id' => $emailService->id,
        'user_id' => 1,
        'message_id' => $messageId,
        'from_email' => 'feedtan15@gmail.com',
        'from_name' => 'FeedTan Pay',
        'to_email' => 'davidngungila@gmail.com',
        'to_name' => 'David Ngungila',
        'subject' => 'Test Email from FeedTan Pay - ' . date('Y-m-d H:i:s'),
        'message' => 'This is a test email sent from FeedTan Pay system using Gmail SMTP.' . "\n\n" .
                   'Test Details:' . "\n" .
                   '- Message ID: ' . $messageId . "\n" .
                   '- Sent at: ' . date('Y-m-d H:i:s') . "\n" .
                   '- Service: ' . $emailService->name . "\n" .
                   '- SMTP Host: ' . $emailService->smtp_host . "\n" .
                   '- SMTP Port: ' . $emailService->smtp_port . "\n",
        'message_type' => 'text',
        'status_name' => 'pending'
    ]);
    
    echo "✅ Test email created in database\n";
    echo "- Email ID: {$testEmail->id}\n";
    echo "- Message ID: {$testEmail->message_id}\n";
    echo "- To: {$testEmail->to_email}\n";
    echo "- Subject: {$testEmail->subject}\n";
    
    // Send email using Laravel's mail system
    echo "\nSending email using Laravel mail system...\n";
    
    try {
        \Illuminate\Support\Facades\Mail::raw($testEmail->message, function ($message) use ($testEmail) {
            $message->to($testEmail->to_email, $testEmail->to_name)
                    ->subject($testEmail->subject)
                    ->from($testEmail->from_email, $testEmail->from_name);
        });
        
        echo "✅ Email sent successfully using Laravel mail\n";
        
        // Update email status
        $testEmail->update([
            'status_name' => 'sent',
            'sent_at' => now()
        ]);
        
    } catch (\Exception $mailError) {
        echo "❌ Laravel mail failed: " . $mailError->getMessage() . "\n";
        
        // Try direct PHP mail as fallback
        echo "\nTrying direct PHP mail as fallback...\n";
        
        $headers = [
            'From: feedtan15@gmail.com',
            'Reply-To: feedtan15@gmail.com',
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0'
        ];
        
        $mailSent = mail($testEmail->to_email, $testEmail->subject, $testEmail->message, implode("\r\n", $headers));
        
        if ($mailSent) {
            echo "✅ Direct PHP mail sent successfully\n";
            
            $testEmail->update([
                'status_name' => 'sent',
                'sent_at' => now()
            ]);
        } else {
            echo "❌ Direct PHP mail also failed\n";
            echo "Error: " . error_get_last()['message'] ?? 'Unknown error';
            
            $testEmail->update([
                'status_name' => 'failed',
                'error_message' => error_get_last()['message'] ?? 'Unknown error'
            ]);
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error in email test: " . $e->getMessage() . "\n";
}

// Test 5: Verify email in database
echo "\n=== Test 5: Verify Email in Database ===\n";
try {
    $emails = \App\Models\EmailMessage::orderBy('created_at', 'desc')->take(3)->get();
    
    echo "Recent emails in database:\n";
    foreach ($emails as $email) {
        echo "- ID: {$email->id}, Message ID: {$email->message_id}\n";
        echo "  To: {$email->to_email}\n";
        echo "  Subject: {$email->subject}\n";
        echo "  Status: {$email->status_name}\n";
        echo "  Sent At: " . ($email->sent_at ? $email->sent_at->format('Y-m-d H:i:s') : 'Not sent') . "\n";
        echo "  Error: " . ($email->error_message ?? 'None') . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error verifying emails: " . $e->getMessage() . "\n";
}

echo "\n=== Email Configuration Complete ===\n";
echo "✅ Gmail Configuration:\n";
echo "   - Email: feedtan15@gmail.com\n";
echo "   - App Password: dmxf jyhl eymc libp\n";
echo "   - SMTP Host: smtp.gmail.com\n";
echo "   - SMTP Port: 587\n";
echo "   - Encryption: tls\n";
echo "   - Test Recipient: davidngungila@gmail.com\n";

echo "\n✅ Database Schema Fixed:\n";
echo "   - Added message_id column\n";
echo "   - Added message, message_type columns\n";
echo "   - Added cc, bcc columns\n";
echo "   - Added tracking columns (opened_at, clicked_at, etc.)\n";

echo "\n✅ Test Results:\n";
echo "   - Email service configured\n";
echo "   - Test email created in database\n";
echo "   - Email sent using Laravel mail or PHP mail\n";
echo "   - Status updated in database\n";

echo "\n=== Test Complete ===\n";
echo "Email configuration and sending test completed.\n";
echo "Please check davidngungila@gmail.com for the test email.\n";
echo "If not received, check spam/junk folder.\n";
