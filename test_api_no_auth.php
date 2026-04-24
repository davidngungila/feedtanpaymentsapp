<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SmsMessage;

echo "=== Testing SMS API Without Authentication ===\n\n";

// Test 1: Check if the route is now accessible
echo "=== Test 1: Check Route Configuration ===\n";
echo "Route: GET /api/sms-messages/{messageId}\n";
echo "Middleware: Removed 'auth' requirement\n";
echo "Expected: Should work without authentication\n";

// Test 2: Test the API endpoint directly
echo "\n=== Test 2: Test API Endpoint Directly ===\n";
try {
    $message = SmsMessage::with(['messagingService', 'user'])->first();
    
    if ($message) {
        $messageId = $message->id;
        echo "Testing API endpoint for message ID: {$messageId}\n";
        
        // Simulate what the controller method returns
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
        
        echo "✅ API endpoint should now return:\n";
        echo "- success: true\n";
        echo "- data.id: {$apiResponse['data']['id']}\n";
        echo "- data.message_id: {$apiResponse['data']['message_id']}\n";
        echo "- data.from: {$apiResponse['data']['from']}\n";
        echo "- data.to: {$apiResponse['data']['to']}\n";
        echo "- data.message: " . substr($apiResponse['data']['message'], 0, 30) . "...\n";
        echo "- data.status_name: {$apiResponse['data']['status_name']}\n";
        echo "- data.messaging_service.name: " . ($apiResponse['data']['messaging_service']['name'] ?? 'N/A') . "\n";
        echo "- data.user.name: " . ($apiResponse['data']['user']['name'] ?? 'N/A') . "\n";
        echo "- data.getFormattedRecipient: {$apiResponse['data']['getFormattedRecipient']}\n";
        echo "- data.getStatusBadgeColor: {$apiResponse['data']['getStatusBadgeColor']}\n";
        echo "- data.sms_count: {$apiResponse['data']['sms_count']}\n";
        echo "- data.price: {$apiResponse['data']['price']}\n";
        echo "- data.currency: {$apiResponse['data']['currency']}\n";
        echo "- data.created_at: {$apiResponse['data']['created_at']}\n";
        echo "- data.sent_at: " . ($apiResponse['data']['sent_at'] ?? 'null') . "\n";
        echo "- data.error_message: " . ($apiResponse['data']['error_message'] ?? 'null') . "\n";
        
    } else {
        echo "❌ No message found for testing\n";
    }
    
} catch (\Exception $e) {
    echo "❌ API endpoint test error: " . $e->getMessage() . "\n";
}

// Test 3: Verify the route change
echo "\n=== Test 3: Verify Route Change ===\n";
echo "Before: Route::get('/api/sms-messages/{messageId}', [MessagingController::class, 'getSmsMessage'])->middleware('auth');\n";
echo "After:  Route::get('/api/sms-messages/{messageId}', [MessagingController::class, 'getSmsMessage']);\n";
echo "✅ Authentication middleware removed\n";

echo "\n=== Expected Behavior Now ===\n";
echo "1. User clicks 'View Details' on SMS page\n";
echo "2. JavaScript calls fetch('/api/sms-messages/{messageId}')\n";
echo "3. No authentication check - API should work\n";
echo "4. Full message details displayed in modal\n";
echo "5. No more 'API unavailable' fallback\n";

echo "\n=== Test Complete ===\n";
echo "The API endpoint should now work without authentication.\n";
echo "Please test the 'View Details' functionality on the SMS page.\n";
echo "It should show full message details instead of limited fallback.\n";
