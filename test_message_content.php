<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;
use App\Models\SmsMessage;

echo "=== Testing Message Content in SMS Logs ===\n\n";

// Get the SMS service
$smsService = MessagingService::where('type', 'SMS')->where('is_active', true)->first();

if (!$smsService) {
    echo "❌ No active SMS service found\n";
    exit(1);
}

echo "SMS Service: {$smsService->name}\n\n";

// Test 1: Check if logs API includes message content
echo "=== Test 1: Check Logs API for Message Content ===\n";
try {
    $url = $smsService->base_url . '/api/v2/logs?limit=3';
    $response = \Illuminate\Support\Facades\Http::withHeaders($smsService->getApiHeaders())
                       ->timeout(30)
                       ->get($url);

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['results']) && is_array($data['results'])) {
            echo "✅ Logs API successful\n";
            
            foreach ($data['results'] as $index => $log) {
                echo "\nLog " . ($index + 1) . ":\n";
                echo "- Message ID: " . ($log['messageId'] ?? 'N/A') . "\n";
                echo "- From: " . ($log['from'] ?? 'N/A') . "\n";
                echo "- To: " . ($log['to'] ?? 'N/A') . "\n";
                echo "- Status: " . ($log['status']['name'] ?? 'N/A') . "\n";
                echo "- Has 'text' field: " . (isset($log['text']) ? 'YES' : 'NO') . "\n";
                echo "- Has 'message' field: " . (isset($log['message']) ? 'YES' : 'NO') . "\n";
                
                if (isset($log['text'])) {
                    echo "- Text: " . substr($log['text'], 0, 50) . "...\n";
                }
                if (isset($log['message'])) {
                    echo "- Message: " . substr($log['message'], 0, 50) . "...\n";
                }
                
                // Show all available fields
                echo "- Available fields: " . implode(', ', array_keys($log)) . "\n";
            }
        } else {
            echo "❌ No results found in logs data\n";
        }
    } else {
        echo "❌ Failed to get logs: " . $response->status() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check local database for message content
echo "\n=== Test 2: Check Local Database for Message Content ===\n";
try {
    // Look for messages that might match the log message IDs
    $messages = SmsMessage::with(['messagingService', 'user'])
                          ->orderBy('created_at', 'desc')
                          ->take(5)
                          ->get();
    
    echo "Found " . $messages->count() . " local SMS messages\n";
    
    foreach ($messages as $message) {
        echo "\nLocal Message:\n";
        echo "- ID: {$message->id}\n";
        echo "- Message ID: {$message->message_id}\n";
        echo "- From: {$message->from}\n";
        echo "- To: {$message->to}\n";
        echo "- Message: " . substr($message->message, 0, 50) . "...\n";
        echo "- Status: {$message->status_name}\n";
        echo "- Created: {$message->created_at}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Check if we can match log message IDs with local messages
echo "\n=== Test 3: Message ID Matching ===\n";
try {
    // Get logs again
    $url = $smsService->base_url . '/api/v2/logs?limit=5';
    $response = \Illuminate\Support\Facades\Http::withHeaders($smsService->getApiHeaders())
                       ->timeout(30)
                       ->get($url);

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['results'])) {
            foreach ($data['results'] as $log) {
                $logMessageId = $log['messageId'] ?? null;
                if ($logMessageId) {
                    // Try to find matching local message
                    $localMessage = SmsMessage::where('message_id', $logMessageId)->first();
                    
                    echo "\nLog Message ID: {$logMessageId}\n";
                    echo "- Local match found: " . ($localMessage ? 'YES' : 'NO') . "\n";
                    
                    if ($localMessage) {
                        echo "- Local message: " . substr($localMessage->message, 0, 50) . "...\n";
                        echo "- Local status: {$localMessage->status_name}\n";
                    }
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Matching error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ Logs API: Checked for message content\n";
echo "✅ Local Database: Checked for message storage\n";
echo "✅ Message Matching: Tested ID correlation\n";
echo "\n=== Recommendation ===\n";
echo "If logs API doesn't include message content:\n";
echo "1. Use message_id to fetch from local database\n";
echo "2. Add message content to log details modal\n";
echo "3. Handle cases where message not found locally\n";
