<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Email Sending Issues ===\n\n";

// Test 1: Check email services configuration
echo "=== Test 1: Check Email Services ===\n";
try {
    $emailServices = \App\Models\MessagingService::where('type', 'EMAIL')->get();
    
    echo "Email services found: " . $emailServices->count() . "\n";
    
    if ($emailServices->count() > 0) {
        foreach ($emailServices as $service) {
            echo "\nService ID: {$service->id}\n";
            echo "- Name: {$service->name}\n";
            echo "- Type: {$service->type}\n";
            echo "- Status: " . ($service->is_active ? 'Active' : 'Inactive') . "\n";
            echo "- From Email: " . ($service->from_email ?? 'Not set') . "\n";
            echo "- From Name: " . ($service->from_name ?? 'Not set') . "\n";
            echo "- Configuration: " . ($service->config ? 'Present' : 'Missing') . "\n";
            
            if ($service->config) {
                $config = json_decode($service->config, true);
                if ($config) {
                    echo "- SMTP Host: " . ($config['smtp_host'] ?? 'Not set') . "\n";
                    echo "- SMTP Port: " . ($config['smtp_port'] ?? 'Not set') . "\n";
                    echo "- SMTP Username: " . ($config['smtp_username'] ?? 'Not set') . "\n";
                    echo "- SMTP Password: " . ($config['smtp_password'] ? 'Set' : 'Not set') . "\n";
                    echo "- SMTP Encryption: " . ($config['smtp_encryption'] ?? 'None') . "\n";
                }
            }
        }
    } else {
        echo "❌ No email services found in database\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking email services: " . $e->getMessage() . "\n";
}

// Test 2: Check email configuration in .env
echo "\n=== Test 2: Check Email Configuration ===\n";
try {
    $envFile = base_path('.env');
    
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        
        $emailConfigs = [
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME'
        ];
        
        foreach ($emailConfigs as $config) {
            if (strpos($envContent, $config) !== false) {
                echo "✅ {$config} - Found in .env\n";
            } else {
                echo "❌ {$config} - Missing from .env\n";
            }
        }
        
        // Check current values
        echo "\nCurrent email configuration:\n";
        echo "- Mailer: " . env('MAIL_MAILER', 'Not set') . "\n";
        echo "- Host: " . env('MAIL_HOST', 'Not set') . "\n";
        echo "- Port: " . env('MAIL_PORT', 'Not set') . "\n";
        echo "- Username: " . (env('MAIL_USERNAME') ? 'Set' : 'Not set') . "\n";
        echo "- Password: " . (env('MAIL_PASSWORD') ? 'Set' : 'Not set') . "\n";
        echo "- Encryption: " . env('MAIL_ENCRYPTION', 'Not set') . "\n";
        echo "- From Address: " . env('MAIL_FROM_ADDRESS', 'Not set') . "\n";
        echo "- From Name: " . env('MAIL_FROM_NAME', 'Not set') . "\n";
        
    } else {
        echo "❌ .env file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking .env: " . $e->getMessage() . "\n";
}

// Test 3: Check MessagingController email sending methods
echo "\n=== Test 3: Check MessagingController Email Methods ===\n";
try {
    $controller = new \App\Http\Controllers\MessagingController();
    
    if (method_exists($controller, 'sendEmail')) {
        echo "✅ sendEmail() method exists\n";
    } else {
        echo "❌ sendEmail() method missing\n";
    }
    
    if (method_exists($controller, 'sendEmailViaApi')) {
        echo "✅ sendEmailViaApi() method exists\n";
    } else {
        echo "❌ sendEmailViaApi() method missing\n";
    }
    
    // Check if the method uses Laravel's Mail system
    $reflection = new ReflectionMethod($controller, 'sendEmailViaApi');
    $source = file_get_contents($reflection->getFileName());
    $startLine = $reflection->getStartLine() - 1;
    $endLine = $reflection->getEndLine();
    $methodSource = implode("\n", array_slice(explode("\n", $source), $startLine, $endLine - $startLine));
    
    if (strpos($methodSource, 'Mail::') !== false) {
        echo "✅ Uses Laravel Mail system\n";
    } else {
        echo "❌ Does not use Laravel Mail system\n";
    }
    
    if (strpos($methodSource, '->to(') !== false) {
        echo "✅ Uses proper ->to() method\n";
    } else {
        echo "❌ Does not use proper ->to() method\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking MessagingController: " . $e->getMessage() . "\n";
}

// Test 4: Check recent email messages for errors
echo "\n=== Test 4: Check Recent Email Messages ===\n";
try {
    $recentMessages = \App\Models\EmailMessage::orderBy('created_at', 'desc')->take(5)->get();
    
    echo "Recent email messages: " . $recentMessages->count() . "\n";
    
    foreach ($recentMessages as $message) {
        echo "\nMessage ID: {$message->id}\n";
        echo "- To Email: {$message->to_email}\n";
        echo "- Subject: {$message->subject}\n";
        echo "- Status: {$message->status_name}\n";
        echo "- Created: " . $message->created_at->format('Y-m-d H:i:s') . "\n";
        echo "- Sent At: " . ($message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : 'Not sent') . "\n";
        echo "- Failed At: " . ($message->failed_at ? $message->failed_at->format('Y-m-d H:i:s') : 'Not failed') . "\n";
        
        if ($message->custom_data) {
            $customData = json_decode($message->custom_data, true);
            if (isset($customData['error'])) {
                echo "- Error: " . $customData['error'] . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking recent messages: " . $e->getMessage() . "\n";
}

// Test 5: Test Laravel Mail configuration
echo "\n=== Test 5: Test Laravel Mail Configuration ===\n";
try {
    // Test mail configuration
    $mailConfig = config('mail');
    
    if ($mailConfig) {
        echo "✅ Mail configuration loaded\n";
        echo "- Default Mailer: " . ($mailConfig['default'] ?? 'Not set') . "\n";
        
        if (isset($mailConfig['mailers'])) {
            foreach ($mailConfig['mailers'] as $name => $mailer) {
                echo "- Mailer '{$name}':\n";
                echo "  - Transport: " . ($mailer['transport'] ?? 'Not set') . "\n";
                echo "  - Host: " . ($mailer['host'] ?? 'Not set') . "\n";
                echo "  - Port: " . ($mailer['port'] ?? 'Not set') . "\n";
                echo "  - Username: " . (isset($mailer['username']) ? 'Set' : 'Not set') . "\n";
                echo "  - Password: " . (isset($mailer['password']) ? 'Set' : 'Not set') . "\n";
            }
        }
    } else {
        echo "❌ Mail configuration not loaded\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing mail config: " . $e->getMessage() . "\n";
}

// Test 6: Check for common email sending issues
echo "\n=== Test 6: Common Email Issues Check ===\n";
try {
    $issues = [];
    
    // Check if email service is active
    $emailServices = \App\Models\MessagingService::where('type', 'EMAIL')->where('is_active', true)->get();
    if ($emailServices->count() === 0) {
        $issues[] = "No active email services found";
    }
    
    // Check if from_email is set
    foreach ($emailServices as $service) {
        if (!$service->from_email) {
            $issues[] = "Service '{$service->name}' missing from_email";
        }
    }
    
    // Check .env email configuration
    if (!env('MAIL_HOST')) {
        $issues[] = "MAIL_HOST not set in .env";
    }
    
    if (!env('MAIL_USERNAME')) {
        $issues[] = "MAIL_USERNAME not set in .env";
    }
    
    if (!env('MAIL_PASSWORD')) {
        $issues[] = "MAIL_PASSWORD not set in .env";
    }
    
    if (!env('MAIL_FROM_ADDRESS')) {
        $issues[] = "MAIL_FROM_ADDRESS not set in .env";
    }
    
    if (empty($issues)) {
        echo "✅ No common issues found\n";
    } else {
        echo "❌ Issues found:\n";
        foreach ($issues as $issue) {
            echo "  - {$issue}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking issues: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Summary ===\n";
echo "This will help identify the root cause of email sending failures.\n";
echo "Check the results above to see what needs to be fixed.\n\n";

echo "=== Next Steps ===\n";
echo "1. Fix any missing email service configuration\n";
echo "2. Update .env email settings if needed\n";
echo "3. Ensure active email service exists\n";
echo "4. Test email sending after fixes\n\n";

echo "=== Debug Complete ===\n";
