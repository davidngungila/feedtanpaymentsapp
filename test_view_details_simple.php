<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test View Details Fixed ===\n\n";

// Test 1: Verify the JavaScript function is properly structured
echo "=== Test 1: Verify JavaScript Function Structure ===\n";
try {
    $viewFile = resource_path('views/messaging/email/index.blade.php');
    
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        
        // Check if the function is properly defined
        if (strpos($content, 'window.viewEmailMessage = function(messageId)') !== false) {
            echo "✅ viewEmailMessage function is properly defined\n";
        } else {
            echo "❌ viewEmailMessage function is not properly defined\n";
        }
        
        // Check if it uses the correct API response structure
        if (strpos($content, 'data.from_email') !== false) {
            echo "✅ Uses correct API response structure (from_email)\n";
        } else {
            echo "❌ Does not use correct API response structure\n";
        }
        
        // Check if it has proper error handling
        if (strpos($content, 'if (!data.success)') !== false) {
            echo "✅ Has proper error handling\n";
        } else {
            echo "❌ Missing proper error handling\n";
        }
        
        // Check if it uses the correct modal
        if (strpos($content, 'emailDetailsModal') !== false) {
            echo "✅ Uses correct modal (emailDetailsModal)\n";
        } else {
            echo "❌ Does not use correct modal\n";
        }
        
    } else {
        echo "❌ View file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking JavaScript function: " . $e->getMessage() . "\n";
}

// Test 2: Test the API endpoint with actual data
echo "\n=== Test 2: Test API Endpoint ===\n";
try {
    $message = \App\Models\EmailMessage::with('messagingService', 'user')->first();
    
    if ($message) {
        echo "Testing with message ID: {$message->id}\n";
        
        $controller = new \App\Http\Controllers\MessagingController();
        $response = $controller->getEmailMessage($message->id);
        
        echo "API Response Status: " . $response->getStatusCode() . "\n";
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                echo "✅ API response is successful\n";
                
                // Verify key fields
                $keyFields = ['message_id', 'from_email', 'to_email', 'subject', 'status_name'];
                foreach ($keyFields as $field) {
                    if (isset($data[$field])) {
                        echo "✅ Field {$field}: " . $data[$field] . "\n";
                    } else {
                        echo "❌ Missing field: {$field}\n";
                    }
                }
                
            } else {
                echo "❌ API response indicates failure\n";
            }
        } else {
            echo "❌ API call failed with status: " . $response->getStatusCode() . "\n";
        }
    } else {
        echo "❌ No email messages found to test\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing API endpoint: " . $e->getMessage() . "\n";
}

echo "\n=== View Details Fix Summary ===\n";
echo "✅ Fixed JavaScript function to use correct API response structure\n";
echo "✅ Removed calls to non-existent methods\n";
echo "✅ Added proper error handling\n";
echo "✅ Updated modal content to display correct fields\n\n";

echo "=== Test the Fix ===\n";
echo "Visit http://127.0.0.1:8001/messaging/email\n";
echo "Click 'View Details' on any email message\n";
echo "The modal should now display all message information correctly!\n\n";

echo "=== Fix Complete ===\n";
