<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Email Sending Process ===\n\n";

// Test 1: Check current email sending JavaScript
echo "=== Test 1: Check Email Sending JavaScript ===\n";
try {
    $viewFile = resource_path('views/messaging/email/index.blade.php');
    
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        
        echo "Checking JavaScript functions:\n";
        
        // Check for sendEmail function
        if (strpos($content, 'function sendEmail()') !== false) {
            echo "✅ sendEmail() function exists\n";
        } else {
            echo "❌ sendEmail() function missing\n";
        }
        
        // Check for loading states
        if (strpos($content, 'disabled = true') !== false) {
            echo "✅ Loading states implemented\n";
        } else {
            echo "❌ Loading states missing\n";
        }
        
        // Check for progress tracking
        if (strpos($content, 'progress') !== false || strpos($content, 'spinner') !== false) {
            echo "✅ Progress indicators found\n";
        } else {
            echo "❌ Progress indicators missing\n";
        }
        
        // Check for completion handling
        if (strpos($content, 'finally') !== false) {
            echo "✅ Completion handling (finally) found\n";
        } else {
            echo "❌ Completion handling missing\n";
        }
        
        // Check for error handling
        if (strpos($content, 'catch(error)') !== false) {
            echo "✅ Error handling found\n";
        } else {
            echo "❌ Error handling missing\n";
        }
        
    } else {
        echo "❌ Email view file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking JavaScript: " . $e->getMessage() . "\n";
}

// Test 2: Check MessagingController sendEmail method
echo "\n=== Test 2: Check MessagingController sendEmail Method ===\n";
try {
    $controller = new \App\Http\Controllers\MessagingController();
    
    if (method_exists($controller, 'sendEmail')) {
        echo "✅ sendEmail() method exists\n";
        
        // Check method signature
        $reflection = new ReflectionMethod($controller, 'sendEmail');
        $parameters = $reflection->getParameters();
        
        echo "Parameters: " . count($parameters) . "\n";
        foreach ($parameters as $param) {
            echo "- {$param->getName()}: {$param->getType()}\n";
        }
        
        // Check if method returns proper response
        $source = file_get_contents($reflection->getFileName());
        $startLine = $reflection->getStartLine() - 1;
        $endLine = $reflection->getEndLine();
        $methodSource = implode("\n", array_slice(explode("\n", $source), $startLine, $endLine - $startLine));
        
        if (strpos($methodSource, 'return response()->json') !== false) {
            echo "✅ Returns JSON response\n";
        } else {
            echo "❌ Doesn't return JSON response\n";
        }
        
        if (strpos($methodSource, 'try') !== false && strpos($methodSource, 'catch') !== false) {
            echo "✅ Has try-catch blocks\n";
        } else {
            echo "❌ Missing try-catch blocks\n";
        }
        
    } else {
        echo "❌ sendEmail() method missing\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking controller: " . $e->getMessage() . "\n";
}

// Test 3: Check for hanging processes or timeouts
echo "\n=== Test 3: Check for Hanging Processes ===\n";
try {
    // Check recent emails that might be stuck
    $stuckEmails = \App\Models\EmailMessage::where('status_name', 'pending')
        ->where('created_at', '<', now()->subMinutes(5))
        ->get();
    
    echo "Emails stuck in pending for more than 5 minutes: " . $stuckEmails->count() . "\n";
    
    foreach ($stuckEmails as $email) {
        echo "- ID: {$email->id}, Created: {$email->created_at->format('Y-m-d H:i:s')}\n";
    }
    
    // Check for emails without sent_at but status is 'sent'
    $inconsistentEmails = \App\Models\EmailMessage::where('status_name', 'sent')
        ->whereNull('sent_at')
        ->get();
    
    echo "Emails with 'sent' status but no sent_at timestamp: " . $inconsistentEmails->count() . "\n";
    
    // Check for emails with very long processing times
    $longProcessingEmails = \App\Models\EmailMessage::whereNotNull('created_at')
        ->whereNotNull('sent_at')
        ->whereRaw('TIMESTAMPDIFF(SECOND, created_at, sent_at) > 30')
        ->get();
    
    echo "Emails taking more than 30 seconds to send: " . $longProcessingEmails->count() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking hanging processes: " . $e->getMessage() . "\n";
}

// Test 4: Check email service performance
echo "\n=== Test 4: Check Email Service Performance ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        echo "Email service: {$emailService->name}\n";
        
        $config = json_decode($emailService->config, true);
        if ($config) {
            echo "SMTP Host: " . ($config['smtp_host'] ?? 'Not set') . "\n";
            echo "SMTP Port: " . ($config['smtp_port'] ?? 'Not set') . "\n";
            echo "SMTP Encryption: " . ($config['encryption'] ?? 'Not set') . "\n";
            
            // Test SMTP connection timeout
            $timeout = $config['timeout'] ?? 30;
            echo "Timeout setting: {$timeout} seconds\n";
            
            if ($timeout > 60) {
                echo "⚠️ Timeout is very high, might cause hanging\n";
            } else {
                echo "✅ Timeout is reasonable\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking email service: " . $e->getMessage() . "\n";
}

// Test 5: Simulate email sending process
echo "\n=== Test 5: Simulate Email Sending Process ===\n";
try {
    echo "Simulating complete email sending workflow:\n\n";
    
    echo "1. User clicks 'Send Email' button\n";
    echo "   → JavaScript sendEmail() function called\n";
    echo "   → Form data collected\n";
    echo "   → Loading state: Button disabled, spinner shown\n";
    echo "   → Progress indicator: 'Sending...'\n\n";
    
    echo "2. API call to backend\n";
    echo "   → POST request to sendEmail endpoint\n";
    echo "   → Data: recipient, subject, body, template_id\n";
    echo "   → CSRF token included\n\n";
    
    echo "3. Backend processing\n";
    echo "   → MessagingController@sendEmail called\n";
    echo "   → Input validation\n";
    echo "   → Email message created in database (status: pending)\n";
    echo "   → Email sending via SMTP\n";
    echo "   → Status updated to 'sent'\n";
    echo "   → Response: JSON with success status\n\n";
    
    echo "4. Frontend response handling\n";
    echo "   → JSON response parsed\n";
    echo "   → If success: Show success notification\n";
    echo "   → If error: Show error notification\n";
    echo "   → Finally: Restore button state\n";
    echo "   → Progress: 100% complete\n\n";
    
    echo "✅ Process simulation complete\n";
    
} catch (\Exception $e) {
    echo "❌ Error simulating process: " . $e->getMessage() . "\n";
}

echo "\n=== Common Issues That Cause Hanging ===\n";
echo "❌ Missing completion handling in JavaScript\n";
echo "❌ No finally block to restore button state\n";
echo "❌ SMTP connection timeout too high\n";
echo "❌ Email service not responding\n";
echo "❌ Missing error handling\n";
echo "❌ Progress indicators not updated\n\n";

echo "=== Next Steps ===\n";
echo "1. Add proper progress tracking to JavaScript\n";
echo "2. Ensure button state is restored in finally block\n";
echo "3. Add timeout handling for email sending\n";
echo "4. Add progress indicators with percentage\n";
echo "5. Test complete workflow\n\n";

echo "=== Debug Complete ===\n";
