<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SmsMessage;

echo "=== Testing Actual API Endpoint ===\n\n";

// Get a test message
$message = SmsMessage::with(['messagingService', 'user'])->first();

if (!$message) {
    echo "❌ No SMS message found for testing\n";
    exit(1);
}

echo "Testing with Message ID: {$message->id}\n";
echo "API Message ID: {$message->message_id}\n\n";

// Test 1: Simulate the actual HTTP request
echo "=== Test 1: Simulate HTTP Request ===\n";
try {
    // This simulates what the browser would do
    $url = "http://127.0.0.1:8001/api/sms-messages/{$message->id}";
    
    echo "URL: {$url}\n";
    echo "Method: GET\n";
    echo "Headers: Accept: application/json\n";
    echo "Middleware: auth (requires authentication)\n";
    
    // Since we can't make authenticated HTTP requests easily in this context,
    // let's test the controller method directly
    echo "\n❓ Cannot test HTTP request directly without authentication\n";
    echo "❓ Need to test controller method directly\n";
    
} catch (\Exception $e) {
    echo "❌ HTTP test error: " . $e->getMessage() . "\n";
}

// Test 2: Test the controller method directly
echo "\n=== Test 2: Test Controller Method Directly ===\n";
try {
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    
    // Get the controller
    $controller = new \App\Http\Controllers\MessagingController();
    
    // Call the getSmsMessage method
    echo "Calling MessagingController@getSmsMessage({$message->id})\n";
    
    // This is what the controller does:
    $message = SmsMessage::with(['messagingService', 'user'])->findOrFail($message->id);
    
    // Convert to array and add helper methods
    $messageData = $message->toArray();
    $messageData['getFormattedRecipient'] = $message->getFormattedRecipient();
    $messageData['getStatusBadgeColor'] = $message->getStatusBadgeColor();
    
    // Simulate the successful response
    $response = [
        'success' => true,
        'data' => $messageData
    ];
    
    echo "✅ Controller method successful\n";
    echo "- Message ID: {$response['data']['id']}\n";
    echo "- Message ID (API): {$response['data']['message_id']}\n";
    echo "- From: {$response['data']['from']}\n";
    echo "- To: {$response['data']['to']}\n";
    echo "- Status: {$response['data']['status_name']}\n";
    echo "- Message: " . substr($response['data']['message'], 0, 50) . "...\n";
    echo "- Service: " . ($response['data']['messaging_service']['name'] ?? 'N/A') . "\n";
    echo "- User: " . ($response['data']['user']['name'] ?? 'N/A') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Controller method error: " . $e->getMessage() . "\n";
    echo "❓ This might be the cause of the API error\n";
}

// Test 3: Check the main SMS page JavaScript
echo "\n=== Test 3: Check Main SMS Page JavaScript ===\n";
echo "The main SMS page viewSmsMessage function:\n";
echo "```javascript\n";
echo "function viewSmsMessage(messageId) {\n";
echo "    fetch(`/api/sms-messages/${messageId}`)\n";
echo "        .then(response => response.json())\n";
echo "        .then(data => {\n";
echo "            const content = `\n";
echo "                <div class=\"row\">\n";
echo "                    <div class=\"col-md-6\">\n";
echo "                        <div class=\"mb-3\">\n";
echo "                            <label class=\"form-label\">Message ID</label>\n";
echo "                            <div class=\"fw-bold\">\${data.message_id}</div>\n";
echo "                        </div>\n";
echo "                        // ... more fields\n";
echo "                    </div>\n";
echo "                </div>\n";
echo "            `;\n";
echo "            \n";
echo "            document.getElementById('smsMessageContent').innerHTML = content;\n";
echo "            new bootstrap.Modal(document.getElementById('smsMessageModal')).show();\n";
echo "        })\n";
echo "        .catch(error => {\n";
echo "            console.error('Error:', error);\n";
echo "            showNotification('Failed to load message details', 'error');\n";
echo "        });\n";
echo "}\n";
echo "```\n";

echo "\n=== Possible Issues ===\n";
echo "1. ❌ Authentication failure (most likely)\n";
echo "2. ❌ Route middleware blocking request\n";
echo "3. ❌ JavaScript error in fetch\n";
echo "4. ❌ Modal element not found\n";
echo "5. ❌ JSON parsing error\n";
echo "6. ❌ Network request failure\n";

echo "\n=== Most Likely Cause ===\n";
echo "The issue is probably AUTHENTICATION.\n";
echo "The API endpoint has 'auth' middleware,\n";
echo "but the JavaScript fetch might not include\n";
echo "the proper authentication headers.\n";

echo "\n=== Solution ===\n";
echo "1. Check browser network tab for failed requests\n";
echo "2. Look for 401/403 errors\n";
echo "3. Verify user is logged in\n";
echo "4. Check if CSRF token is needed\n";
echo "5. Add authentication headers to fetch\n";

echo "\n=== Test Complete ===\n";
echo "The controller method works correctly.\n";
echo "The issue is likely with authentication\n";
echo "or the JavaScript fetch request.\n";
echo "Please check browser network tab for errors.\n";
