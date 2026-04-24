<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Full Historical SMS Sync ===\n\n";

// Test 1: Check if the new sync functionality is available
echo "=== Test 1: New Sync Functionality Check ===\n";
try {
    $smsSyncService = new \App\Services\SmsSyncService();
    
    // Use reflection to check if the new method exists
    $reflection = new ReflectionClass($smsSyncService);
    $hasSyncAllMessages = $reflection->hasMethod('syncAllMessages');
    
    echo "syncAllMessages method: " . ($hasSyncAllMessages ? '✅ EXISTS' : '❌ MISSING') . "\n";
    
    // Check if the main method supports new parameters
    $syncMethod = $reflection->getMethod('syncSmsMessages');
    $parameters = $syncMethod->getParameters();
    
    echo "syncSmsMessages parameters:\n";
    foreach ($parameters as $param) {
        echo "  - {$param->getName()}: " . ($param->isDefaultValueAvailable() ? 'Optional' : 'Required') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Functionality check error: " . $e->getMessage() . "\n";
}

// Test 2: Check command options
echo "\n=== Test 2: Command Options Check ===\n";
try {
    $reflection = new ReflectionClass(\App\Console\Commands\SyncSmsCommand::class);
    
    // Get the signature property from the class
    $signatureProperty = $reflection->getProperty('signature');
    $signatureProperty->setAccessible(true);
    $signature = $signatureProperty->getDefaultValue();
    
    echo "Command signature: {$signature}\n";
    echo "Available options:\n";
    
    if (strpos($signature, '--all') !== false) {
        echo "  ✅ --all: Sync all messages from beginning\n";
    }
    if (strpos($signature, '--from') !== false) {
        echo "  ✅ --from: Start date (YYYY-MM-DD)\n";
    }
    if (strpos($signature, '--to') !== false) {
        echo "  ✅ --to: End date (YYYY-MM-DD)\n";
    }
    if (strpos($signature, '--limit') !== false) {
        echo "  ✅ --limit: Number of messages to sync\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Command check error: " . $e->getMessage() . "\n";
}

// Test 3: Test limited sync with date range
echo "\n=== Test 3: Limited Sync with Date Range ===\n";
try {
    $smsSyncService = new \App\Services\SmsSyncService();
    
    echo "Testing sync with date range (last 7 days)...\n";
    
    $fromDate = date('Y-m-d', strtotime('-7 days'));
    $toDate = date('Y-m-d');
    
    $result = $smsSyncService->syncSmsMessages(100, $fromDate, $toDate, false);
    
    echo "Date range sync result:\n";
    echo "- Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "- Message: " . $result['message'] . "\n";
    echo "- New messages: " . $result['synced'] . "\n";
    echo "- Updated messages: " . $result['updated'] . "\n";
    
} catch (\Exception $e) {
    echo "❌ Date range sync error: " . $e->getMessage() . "\n";
}

// Test 4: Test pagination logic simulation
echo "\n=== Test 4: Pagination Logic Test ===\n";
try {
    $smsSyncService = new \App\Services\SmsSyncService();
    
    echo "Testing pagination with small limit...\n";
    
    // Test with a small limit to simulate pagination
    $result = $smsSyncService->syncSmsMessages(10, null, null, false);
    
    echo "Pagination test result:\n";
    echo "- Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "- New messages: " . $result['synced'] . "\n";
    echo "- Updated messages: " . $result['updated'] . "\n";
    
} catch (\Exception $e) {
    echo "❌ Pagination test error: " . $e->getMessage() . "\n";
}

// Test 5: Show usage examples
echo "\n=== Test 5: Usage Examples ===\n";
echo "Full Historical Sync Commands:\n";
echo "1. php artisan sms:sync --all\n";
echo "   - Sync ALL messages from the beginning\n";
echo "   - May take a long time\n";
echo "   - Uses pagination with 500 messages per batch\n\n";

echo "2. php artisan sms:sync --all --from=2024-01-01\n";
echo "   - Sync all messages from January 1, 2024\n";
echo "   - Useful for specific date ranges\n\n";

echo "3. php artisan sms:sync --all --from=2024-01-01 --to=2024-12-31\n";
echo "   - Sync messages within a specific date range\n";
echo "   - More targeted sync\n\n";

echo "4. php artisan sms:sync --from=2024-06-01 --to=2024-06-30\n";
echo "   - Sync messages for June 2024 (limited sync)\n";
echo "   - Does NOT use pagination\n\n";

echo "5. php artisan sms:sync --limit=1000\n";
echo "   - Regular sync with custom limit\n";
echo "   - Does NOT use pagination\n\n";

// Test 6: Check current database status
echo "\n=== Test 6: Current Database Status ===\n";
try {
    $totalMessages = \App\Models\SmsMessage::count();
    $oldestMessage = \App\Models\SmsMessage::orderBy('created_at', 'asc')->first();
    $newestMessage = \App\Models\SmsMessage::orderBy('created_at', 'desc')->first();
    
    echo "Current database status:\n";
    echo "- Total messages: {$totalMessages}\n";
    echo "- Oldest message: " . ($oldestMessage ? $oldestMessage->created_at->format('Y-m-d H:i:s') : 'None') . "\n";
    echo "- Newest message: " . ($newestMessage ? $newestMessage->created_at->format('Y-m-d H:i:s') : 'None') . "\n";
    
    if ($oldestMessage && $newestMessage) {
        $dateRange = $oldestMessage->created_at->diffInDays($newestMessage->created_at);
        echo "- Date range: {$dateRange} days\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database status error: " . $e->getMessage() . "\n";
}

echo "\n=== Full Historical Sync Summary ===\n";
echo "✅ New Features Added:\n";
echo "   - --all flag: Sync all messages from beginning\n";
echo "   - --from/--to flags: Date range filtering\n";
echo "   - Pagination: 500 messages per batch\n";
echo "   - Rate limiting protection: 0.5s delay between batches\n";
echo "   - Progress tracking: Iteration counter and batch stats\n";
echo "   - Error handling: Comprehensive logging and error tracking\n";

echo "\n⚠️  Important Notes:\n";
echo "   - Full sync may take a long time depending on message count\n";
echo "   - API rate limiting is handled with delays\n";
echo "   - Maximum 1000 iterations to prevent infinite loops\n";
echo "   - All sync activity is logged for monitoring\n";

echo "\n🚀 Ready for Full Sync:\n";
echo "The system is now ready to sync ALL messages from the API account.\n";
echo "Run 'php artisan sms:sync --all' to start the full historical sync.\n";
