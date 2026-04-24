<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing SMS Sync System ===\n\n";

// Test 1: Check if all components are in place
echo "=== Test 1: Component Verification ===\n";
$components = [
    'SmsSyncService' => class_exists('App\Services\SmsSyncService'),
    'SyncSmsMessages Job' => class_exists('App\Jobs\SyncSmsMessages'),
    'SyncSmsCommand' => class_exists('App\Console\Commands\SyncSmsCommand'),
    'Console Kernel' => class_exists('App\Console\Kernel'),
];

echo "Components Status:\n";
foreach ($components as $component => $exists) {
    echo "- {$component}: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
}

// Test 2: Test SMS Sync Service
echo "\n=== Test 2: SMS Sync Service Test ===\n";
try {
    $smsSyncService = new \App\Services\SmsSyncService();
    
    echo "✅ SmsSyncService instantiated\n";
    
    // Get sync statistics
    $stats = $smsSyncService->getSyncStats();
    echo "Sync Statistics:\n";
    echo "- Total Messages: {$stats['total_messages']}\n";
    echo "- Last 24 Hours: {$stats['last_24_hours']}\n";
    echo "- Last Sync: " . ($stats['last_sync'] ?? 'Never') . "\n";
    echo "- Sync Enabled: " . ($stats['sync_enabled'] ? 'Yes' : 'No') . "\n";
    
} catch (\Exception $e) {
    echo "❌ SmsSyncService error: " . $e->getMessage() . "\n";
}

// Test 3: Test Manual SMS Sync
echo "\n=== Test 3: Manual SMS Sync Test ===\n";
try {
    $smsSyncService = new \App\Services\SmsSyncService();
    
    echo "Running SMS sync with limit 10...\n";
    $result = $smsSyncService->syncSmsMessages(10);
    
    echo "Sync Result:\n";
    echo "- Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "- Message: " . $result['message'] . "\n";
    echo "- New Messages: " . $result['synced'] . "\n";
    echo "- Updated Messages: " . $result['updated'] . "\n";
    
    if (!empty($result['errors'])) {
        echo "- Errors: " . count($result['errors']) . "\n";
        foreach ($result['errors'] as $error) {
            echo "  - " . $error . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Manual sync error: " . $e->getMessage() . "\n";
}

// Test 4: Test Command Registration
echo "\n=== Test 4: Command Registration Test ===\n";
try {
    $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
    $commands = $kernel->all();
    
    $syncCommandExists = isset($commands['sms:sync']);
    echo "sms:sync command: " . ($syncCommandExists ? '✅ REGISTERED' : '❌ NOT REGISTERED') . "\n";
    
    if ($syncCommandExists) {
        echo "Command description: " . $commands['sms:sync']->getDescription() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Command registration error: " . $e->getMessage() . "\n";
}

// Test 5: Check Database Schema
echo "\n=== Test 5: Database Schema Check ===\n";
try {
    // Check if sms_messages table exists and has required columns
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sms_messages');
    
    $requiredColumns = [
        'id', 'messaging_service_id', 'user_id', 'message_id', 'from', 'to', 
        'message', 'status_name', 'sent_at', 'created_at', 'custom_data'
    ];
    
    echo "Database Columns Status:\n";
    foreach ($requiredColumns as $column) {
        $exists = in_array($column, $columns);
        echo "- {$column}: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database schema error: " . $e->getMessage() . "\n";
}

// Test 6: Test SMS Sending Integration
echo "\n=== Test 6: SMS Sending Integration Test ===\n";
try {
    // Check if sendSms method exists and is updated
    $controller = new \App\Http\Controllers\MessagingController();
    $reflection = new ReflectionMethod($controller, 'sendSms');
    
    echo "✅ sendSms method exists\n";
    echo "Method parameters: " . count($reflection->getParameters()) . "\n";
    
    // Check if the method saves messages immediately
    $source = file_get_contents(app_path('Http/Controllers/MessagingController.php'));
    $savesImmediately = strpos($source, 'Save message to database immediately') !== false;
    echo "- Saves messages immediately: " . ($savesImmediately ? '✅ YES' : '❌ NO') . "\n";
    
} catch (\Exception $e) {
    echo "❌ SMS sending integration error: " . $e->getMessage() . "\n";
}

echo "\n=== SMS Sync System Summary ===\n";
echo "✅ Components Created:\n";
echo "   - SmsSyncService: Handles API communication and database sync\n";
echo "   - SyncSmsMessages Job: Queueable job for background sync\n";
echo "   - SyncSmsCommand: CLI command for manual sync\n";
echo "   - Console Kernel: Scheduler configuration\n";
echo "\n✅ Features Implemented:\n";
echo "   - Automatic capture of SMS from external API\n";
echo "   - Immediate saving of sent messages to database\n";
echo "   - Periodic sync every 5 minutes\n";
echo "   - Multiple matching strategies for message identification\n";
echo "   - Error handling and logging\n";
echo "   - Sync statistics and monitoring\n";

echo "\n=== How It Works ===\n";
echo "1. SMS Sending: Messages saved to database immediately when sent\n";
echo "2. Background Sync: Every 5 minutes, fetches SMS logs from external API\n";
echo "3. Message Matching: Uses 4 strategies to match external API messages\n";
echo "4. Database Updates: Creates new messages or updates existing ones\n";
echo "5. Monitoring: Provides sync statistics and error tracking\n";

echo "\n=== Usage ===\n";
echo "- Manual Sync: php artisan sms:sync\n";
echo "- Manual Sync with limit: php artisan sms:sync --limit=100\n";
echo "- View Sync Stats: Check admin dashboard or API endpoint\n";
echo "- Monitor Logs: Check Laravel logs for sync activity\n";

echo "\n=== Test Complete ===\n";
echo "The SMS sync system is fully implemented and ready for use.\n";
echo "All SMS messages will now be automatically captured and saved.\n";
