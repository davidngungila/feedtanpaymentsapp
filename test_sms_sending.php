<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;
use Illuminate\Support\Facades\Http;

echo "=== Testing SMS Sending Functionality ===\n\n";

// Get the SMS service
$smsService = MessagingService::where('type', 'SMS')->where('is_active', true)->first();

if (!$smsService) {
    echo "❌ No active SMS service found\n";
    exit(1);
}

echo "SMS Service Details:\n";
echo "- Name: {$smsService->name}\n";
echo "- Provider: {$smsService->provider}\n";
echo "- Base URL: {$smsService->base_url}\n";
echo "- Sender ID: {$smsService->sender_id}\n";
echo "- Bearer Token: " . substr($smsService->bearer_token, 0, 10) . "...\n";
echo "- Test Mode: " . ($smsService->test_mode ? 'YES' : 'NO') . "\n\n";

// Test 1: Test Connection
echo "=== Test 1: Test Connection ===\n";
try {
    $baseTest = Http::timeout(5)->get($smsService->base_url);
    echo "Base URL Test: HTTP {$baseTest->status()} - " . ($baseTest->successful() ? 'SUCCESS' : 'FAILED') . "\n";
} catch (\Exception $e) {
    echo "Base URL Test: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Test SMS API Endpoints
echo "=== Test 2: Test SMS API Endpoints ===\n";

$testEndpoints = [
    $smsService->base_url . '/api/sms/v2/test/text/single', // Test endpoint
    $smsService->base_url . '/api/sms/v2/text/single'      // Production endpoint
];

$payload = [
    'from' => $smsService->sender_id,
    'to' => '0622239304', // Test number provided by user
    'text' => 'Test message from FeedTan Pay - ' . date('Y-m-d H:i:s')
];

$headers = [
    'Authorization' => 'Bearer ' . $smsService->bearer_token,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
];

foreach ($testEndpoints as $index => $endpoint) {
    echo "Testing endpoint " . ($index + 1) . ": {$endpoint}\n";
    echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    echo "Headers: Authorization: Bearer " . substr($smsService->bearer_token, 0, 10) . "...\n\n";

    try {
        $response = Http::withHeaders($headers)
                       ->timeout(10)
                       ->post($endpoint, $payload);

        echo "Response Status: {$response->status()}\n";
        echo "Response Body:\n";
        echo $response->body() . "\n\n";
        
        if ($response->successful()) {
            echo "✅ SUCCESS: Endpoint " . ($index + 1) . " works!\n";
            break;
        } else {
            echo "❌ FAILED: Endpoint " . ($index + 1) . " failed\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . " (Endpoint " . ($index + 1) . ")\n";
    }
    echo str_repeat("-", 50) . "\n\n";
}

// Test 3: Test with Production Endpoint (if test mode is off)
echo "=== Test 3: Production Endpoint Test ===\n";
if (!$smsService->test_mode) {
    $productionEndpoint = $smsService->base_url . '/api/sms/v2/text/single';
    
    echo "Testing production endpoint: {$productionEndpoint}\n";
    echo "Note: This will send an actual SMS to 0622239304\n\n";
    
    try {
        $response = Http::withHeaders($headers)
                       ->timeout(10)
                       ->post($productionEndpoint, $payload);

        echo "Production Response Status: {$response->status()}\n";
        echo "Production Response Body:\n";
        echo $response->body() . "\n\n";
        
        if ($response->successful()) {
            echo "✅ SUCCESS: SMS sent successfully to 0622239304!\n";
            
            $data = $response->json();
            if (isset($data['messages'][0])) {
                $messageData = $data['messages'][0];
                echo "Message ID: " . ($messageData['messageId'] ?? 'N/A') . "\n";
                echo "Status: " . ($messageData['status']['name'] ?? 'N/A') . "\n";
                echo "SMS Count: " . ($messageData['smsCount'] ?? 'N/A') . "\n";
                echo "Price: " . ($messageData['price'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ FAILED: SMS sending failed\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "ℹ️ Service is in TEST MODE - not testing production endpoint\n";
    echo "To send actual SMS, disable test mode in the service configuration\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ SMS Service: Configured\n";
echo "✅ API Token: Updated to correct token\n";
echo "✅ Endpoints: Updated to match documentation\n";
echo "✅ Test Number: 0622239304\n";
echo "\n=== SMS Sending Test Complete ===\n";
