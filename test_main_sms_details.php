<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SmsMessage;

echo "=== Testing Main SMS Page Message Details ===\n\n";

// Test 1: Check if there are SMS messages in the database
echo "=== Test 1: Check Local SMS Messages ===\n";
try {
    $messages = SmsMessage::with(['messagingService', 'user'])->get();
    echo "Found " . $messages->count() . " local SMS messages\n";
    
    if ($messages->count() > 0) {
        foreach ($messages as $message) {
            echo "\nMessage ID: {$message->id}\n";
            echo "- Message ID (API): {$message->message_id}\n";
            echo "- From: {$message->from}\n";
            echo "- To: {$message->to}\n";
            echo "- Status: {$message->status_name}\n";
            echo "- Message: " . substr($message->message, 0, 30) . "...\n";
        }
    } else {
        echo "❌ No local SMS messages found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Test the getSmsMessage API endpoint directly
echo "\n=== Test 2: Test getSmsMessage API Endpoint ===\n";
try {
    $messages = SmsMessage::with(['messagingService', 'user'])->get();
    
    if ($messages->count() > 0) {
        $testMessage = $messages->first();
        $messageId = $testMessage->id;
        
        echo "Testing API endpoint: /api/sms-messages/{$messageId}\n";
        
        // Simulate the API call
        $message = SmsMessage::with(['messagingService', 'user'])->findOrFail($messageId);
        
        // Convert to array and add helper methods
        $messageData = $message->toArray();
        $messageData['getFormattedRecipient'] = $message->getFormattedRecipient();
        $messageData['getStatusBadgeColor'] = $message->getStatusBadgeColor();
        
        echo "✅ API endpoint simulation successful\n";
        echo "- Message ID: {$messageData['id']}\n";
        echo "- Message ID (API): {$messageData['message_id']}\n";
        echo "- From: {$messageData['from']}\n";
        echo "- To: {$messageData['to']}\n";
        echo "- Status: {$messageData['status_name']}\n";
        echo "- Message: " . substr($messageData['message'], 0, 50) . "...\n";
        echo "- Formatted Recipient: {$messageData['getFormattedRecipient']}\n";
        echo "- Status Badge Color: {$messageData['getStatusBadgeColor']}\n";
        
        // Test the JSON response structure
        $apiResponse = [
            'success' => true,
            'data' => $messageData
        ];
        
        echo "\n✅ API Response Structure:\n";
        echo "- success: " . ($apiResponse['success'] ? 'true' : 'false') . "\n";
        echo "- data.message_id: " . $apiResponse['data']['message_id'] . "\n";
        echo "- data.from: " . $apiResponse['data']['from'] . "\n";
        echo "- data.to: " . $apiResponse['data']['to'] . "\n";
        echo "- data.message: " . substr($apiResponse['data']['message'], 0, 30) . "...\n";
        echo "- data.status_name: " . $apiResponse['data']['status_name'] . "\n";
        echo "- data.getFormattedRecipient: " . $apiResponse['data']['getFormattedRecipient'] . "\n";
        echo "- data.getStatusBadgeColor: " . $apiResponse['data']['getStatusBadgeColor'] . "\n";
        
    } else {
        echo "❌ No messages to test API endpoint\n";
    }
    
} catch (\Exception $e) {
    echo "❌ API endpoint test error: " . $e->getMessage() . "\n";
}

// Test 3: Check the main SMS page JavaScript
echo "\n=== Test 3: Check Main SMS Page JavaScript ===\n";
echo "The main SMS page uses viewSmsMessage(messageId) function:\n";
echo "- Fetches: /api/sms-messages/{messageId}\n";
echo "- Expects JSON response with success and data fields\n";
echo "- Displays modal with message details\n";

echo "\nExpected JavaScript flow:\n";
echo "1. User clicks 'View Details' button\n";
echo "2. viewSmsMessage(messageId) is called\n";
echo "3. fetch('/api/sms-messages/' + messageId) is executed\n";
echo "4. Controller returns JSON with message data\n";
echo "5. Modal displays the message information\n";

// Test 4: Check route registration
echo "\n=== Test 4: Check Route Registration ===\n";
echo "Route should be: GET /api/sms-messages/{messageId}\n";
echo "Controller method: getSmsMessage\n";
echo "Middleware: auth\n";

echo "\n=== Possible Issues ===\n";
echo "1. ❌ Route not properly registered\n";
echo "2. ❌ Controller method has errors\n";
echo "3. ❌ Database relationships not loading\n";
echo "4. ❌ Helper methods not working\n";
echo "5. ❌ Authentication middleware blocking\n";
echo "6. ❌ JSON response format incorrect\n";

echo "\n=== Next Steps ===\n";
echo "1. Check if route is registered correctly\n";
echo "2. Test the API endpoint directly\n";
echo "3. Verify controller method works\n";
echo "4. Check JavaScript error handling\n";
echo "5. Fix any identified issues\n";

echo "\n=== Test Complete ===\n";
echo "Please check the browser console for specific error messages\n";
echo "when clicking 'View Details' on the main SMS page.\n";
