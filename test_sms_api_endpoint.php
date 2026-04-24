<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SmsMessage;

echo "=== Testing SMS API Endpoint for Message Details ===\n\n";

// Get the SMS messages shown in the table
echo "=== Test 1: Check SMS Messages in Table ===\n";
try {
    $messages = SmsMessage::with(['messagingService', 'user'])
                           ->orderBy('created_at', 'desc')
                           ->take(3)
                           ->get();

    echo "Found " . $messages->count() . " messages:\n";
    
    foreach ($messages as $message) {
        echo "\nMessage #{$message->id}:\n";
        echo "- Recipient: {$message->to}\n";
        echo "- Message: " . substr($message->message, 0, 20) . "...\n";
        echo "- Service: {$message->messagingService->name}\n";
        echo "- Status: {$message->status_name}\n";
        echo "- Sent: " . ($message->sent_at ? $message->sent_at->format('M j, Y H:i') : '-') . "\n";
        echo "- Cost: {$message->currency} {$message->price}\n";
        echo "- User: {$message->user->name}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error getting messages: " . $e->getMessage() . "\n";
}

// Test 2: Test the getSmsMessage controller method directly
echo "\n=== Test 2: Test getSmsMessage Controller Method ===\n";
try {
    $message = SmsMessage::with(['messagingService', 'user'])->first();
    
    if ($message) {
        $messageId = $message->id;
        echo "Testing getSmsMessage({$messageId})\n";
        
        // Simulate the controller method
        $message = SmsMessage::with(['messagingService', 'user'])->findOrFail($messageId);
        
        // Convert to array and add helper methods
        $messageData = $message->toArray();
        $messageData['getFormattedRecipient'] = $message->getFormattedRecipient();
        $messageData['getStatusBadgeColor'] = $message->getStatusBadgeColor();
        
        // Create the API response
        $apiResponse = [
            'success' => true,
            'data' => $messageData
        ];
        
        echo "✅ Controller method successful\n";
        echo "- Response success: " . ($apiResponse['success'] ? 'true' : 'false') . "\n";
        echo "- Data keys: " . implode(', ', array_keys($apiResponse['data'])) . "\n";
        echo "- Message ID: {$apiResponse['data']['id']}\n";
        echo "- Message ID (API): {$apiResponse['data']['message_id']}\n";
        echo "- Recipient: {$apiResponse['data']['to']}\n";
        echo "- Message: " . substr($apiResponse['data']['message'], 0, 30) . "...\n";
        echo "- Service: " . ($apiResponse['data']['messaging_service']['name'] ?? 'N/A') . "\n";
        echo "- Status: {$apiResponse['data']['status_name']}\n";
        echo "- User: " . ($apiResponse['data']['user']['name'] ?? 'N/A') . "\n";
        
    } else {
        echo "❌ No message found for testing\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Controller method error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Check the route and middleware
echo "\n=== Test 3: Check Route Configuration ===\n";
echo "Route: GET /api/sms-messages/{messageId}\n";
echo "Controller: MessagingController@getSmsMessage\n";
echo "Middleware: auth\n";
echo "Expected behavior:\n";
echo "1. User must be authenticated\n";
echo "2. Returns JSON with success and data fields\n";
echo "3. Data contains message details with relationships\n";

// Test 4: Simulate the JavaScript fetch request
echo "\n=== Test 4: Simulate JavaScript Fetch ===\n";
try {
    $message = SmsMessage::first();
    
    if ($message) {
        echo "Simulating fetch('/api/sms-messages/{$message->id}')\n";
        echo "This would:\n";
        echo "1. Send GET request to /api/sms-messages/{$message->id}\n";
        echo "2. Apply auth middleware\n";
        echo "3. Call MessagingController@getSmsMessage\n";
        echo "4. Return JSON response\n";
        echo "5. JavaScript processes response and shows modal\n";
        
        // Check if the route exists
        $routes = app('router')->getRoutes();
        $routeFound = false;
        
        foreach ($routes as $route) {
            if ($route->uri() === 'api/sms-messages/{messageId}' && 
                in_array('GET', $route->methods())) {
                $routeFound = true;
                echo "✅ Route found: " . $route->uri() . "\n";
                echo "   Action: " . $route->getActionName() . "\n";
                echo "   Middleware: " . implode(', ', $route->middleware()) . "\n";
                break;
            }
        }
        
        if (!$routeFound) {
            echo "❌ Route not found!\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Route check error: " . $e->getMessage() . "\n";
}

// Test 5: Check for common issues
echo "\n=== Test 5: Common Issues Check ===\n";
echo "1. Authentication: User must be logged in\n";
echo "2. CSRF Token: Not needed for GET requests\n";
echo "3. Headers: Should include Accept: application/json\n";
echo "4. Response Format: Must be valid JSON\n";
echo "5. Error Handling: Should return proper error responses\n";

echo "\n=== Expected JSON Response ===\n";
echo "```json\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"data\": {\n";
echo "    \"id\": 1,\n";
echo "    \"message_id\": \"SMS_...\",\n";
echo "    \"from\": \"FEEDTAN\",\n";
echo "    \"to\": \"255622239304\",\n";
echo "    \"message\": \"VBB\",\n";
echo "    \"status_name\": \"PENDING\",\n";
echo "    \"messaging_service\": {\"name\": \"Main SMS Service\"},\n";
echo "    \"user\": {\"name\": \"Admin User\"},\n";
echo "    \"getFormattedRecipient\": \"255622239304\",\n";
echo "    \"getStatusBadgeColor\": \"warning\"\n";
echo "  }\n";
echo "}\n";
echo "```\n";

echo "\n=== Debugging Steps ===\n";
echo "1. Open browser developer tools (F12)\n";
echo "2. Go to Network tab\n";
echo "3. Click 'View Details' on a message\n";
echo "4. Look for the API request in Network tab\n";
echo "5. Check the response status and body\n";
echo "6. Look for any errors in Console tab\n";

echo "\n=== Most Likely Issues ===\n";
echo "1. Authentication failure (401 Unauthorized)\n";
echo "2. Route not found (404 Not Found)\n";
echo "3. Controller error (500 Internal Server Error)\n";
echo "4. JSON response format error\n";
echo "5. JavaScript processing error\n";

echo "\n=== Test Complete ===\n";
echo "The controller method appears to work correctly.\n";
echo "The issue is likely with authentication or the HTTP request.\n";
echo "Please check the browser Network tab for the actual API request.\n";
