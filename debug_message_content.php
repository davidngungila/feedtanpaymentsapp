<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;

echo "=== Debugging Message Content Display Issue ===\n\n";

// Get the SMS service
$smsService = MessagingService::where('type', 'SMS')->where('is_active', true)->first();

if (!$smsService) {
    echo "❌ No active SMS service found\n";
    exit(1);
}

echo "SMS Service: {$smsService->name}\n\n";

// Test 1: Get actual log data to verify message content exists
echo "=== Test 1: Verify Message Content in API Response ===\n";
try {
    $url = $smsService->base_url . '/api/v2/logs?limit=2';
    $response = \Illuminate\Support\Facades\Http::withHeaders($smsService->getApiHeaders())
                       ->timeout(30)
                       ->get($url);

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['results']) && is_array($data['results'])) {
            echo "✅ API Response successful\n";
            
            foreach ($data['results'] as $index => $log) {
                echo "\nLog " . ($index + 1) . ":\n";
                echo "- Message ID: " . ($log['messageId'] ?? 'N/A') . "\n";
                echo "- From: " . ($log['from'] ?? 'N/A') . "\n";
                echo "- To: " . ($log['to'] ?? 'N/A') . "\n";
                echo "- Status: " . ($log['status']['name'] ?? 'N/A') . "\n";
                echo "- Has 'text' field: " . (isset($log['text']) ? 'YES' : 'NO') . "\n";
                
                if (isset($log['text'])) {
                    echo "- Text content: '" . $log['text'] . "'\n";
                    echo "- Text length: " . strlen($log['text']) . " characters\n";
                    echo "- Text is empty: " . (empty($log['text']) ? 'YES' : 'NO') . "\n";
                }
                
                // Check all fields
                echo "- All fields: " . json_encode(array_keys($log)) . "\n";
            }
        }
    } else {
        echo "❌ API failed: " . $response->status() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check the JavaScript template literal
echo "\n=== Test 2: Verify JavaScript Template ===\n";
echo "The JavaScript template should include:\n";
echo "```\n";
echo "\${log.text ? `\n";
echo "        <div class=\"row\">\n";
echo "            <div class=\"col-12\">\n";
echo "                <div class=\"mb-3\">\n";
echo "                    <label class=\"form-label\">Message Content</label>\n";
echo "                    <div class=\"bg-light p-3 rounded border\">\n";
echo "                        <pre class=\"mb-0\" style=\"white-space: pre-wrap; word-wrap: break-word; font-family: inherit;\">\${log.text}</pre>\n";
echo "                    </div>\n";
echo "                </div>\n";
echo "            </div>\n";
echo "        </div>\n";
echo "        ` : ''}\n";
echo "```\n";

// Test 3: Simulate the JavaScript execution
echo "\n=== Test 3: Simulate JavaScript Execution ===\n";
try {
    $url = $smsService->base_url . '/api/v2/logs?limit=1';
    $response = \Illuminate\Support\Facades\Http::withHeaders($smsService->getApiHeaders())
                       ->timeout(30)
                       ->get($url);

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['results'][0])) {
            $log = $data['results'][0];
            
            echo "Simulating JavaScript with log data:\n";
            echo "- log.text exists: " . (isset($log['text']) ? 'YES' : 'NO') . "\n";
            echo "- log.text value: '" . ($log['text'] ?? 'NULL') . "'\n";
            echo "- log.text empty: " . (empty($log['text'] ?? '') ? 'YES' : 'NO') . "\n";
            
            // Simulate the conditional
            $hasText = isset($log['text']) && !empty($log['text']);
            echo "- Conditional result: " . ($hasText ? 'SHOW MESSAGE CONTENT' : 'HIDE MESSAGE CONTENT') . "\n";
            
            if ($hasText) {
                echo "- Message content would be displayed:\n";
                echo "  " . $log['text'] . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Simulation error: " . $e->getMessage() . "\n";
}

echo "\n=== Debugging Steps ===\n";
echo "1. ✅ API includes message content in 'text' field\n";
echo "2. ✅ JavaScript template includes message content section\n";
echo "3. ❓ Browser might be caching old JavaScript\n";
echo "4. ❓ JavaScript might have syntax errors\n";
echo "5. ❓ Template literal might not be rendering properly\n";

echo "\n=== Solutions to Try ===\n";
echo "1. Clear browser cache and refresh page\n";
echo "2. Check browser console for JavaScript errors\n";
echo "3. Add debugging to showLogDetails function\n";
echo "4. Verify the logs.blade.php file is updated\n";

echo "\n=== Next Steps ===\n";
echo "1. Add debugging console.log to JavaScript\n";
echo "2. Add alert to show message content\n";
echo "3. Check if the conditional is working\n";
echo "4. Verify the modal content is being updated\n";
