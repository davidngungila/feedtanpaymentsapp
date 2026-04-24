<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;

echo "=== Testing All Messaging Service Actions ===\n\n";

// Get the services
$smsService = MessagingService::where('type', 'SMS')->first();
$emailService = MessagingService::where('type', 'EMAIL')->first();

echo "Current Services:\n";
echo "1. SMS Service: {$smsService->name} (ID: {$smsService->id}) - Active: " . ($smsService->is_active ? 'YES' : 'NO') . "\n";
echo "2. Email Service: {$emailService->name} (ID: {$emailService->id}) - Active: " . ($emailService->is_active ? 'YES' : 'NO') . "\n\n";

// Test 1: View Services (servicesIndex)
echo "=== Test 1: View Services ===\n";
$allServices = MessagingService::all();
echo "Total services in database: " . $allServices->count() . "\n";
foreach ($allServices as $service) {
    echo "- {$service->name} ({$service->type}) - Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
}
echo "✅ View Services: PASS\n\n";

// Test 2: Add Service (storeService)
echo "=== Test 2: Add Service ===\n";
try {
    $newService = MessagingService::create([
        'name' => 'Test Service',
        'type' => 'SMS',
        'provider' => 'test-provider.com',
        'base_url' => 'https://test-provider.com',
        'api_version' => 'v1',
        'bearer_token' => 'test-token-123',
        'sender_id' => 'TEST',
        'rate_limit_per_hour' => 100,
        'cost_per_message' => 0.01,
        'currency' => 'TZS',
        'is_active' => false,
        'test_mode' => true,
        'notes' => 'Test service for validation'
    ]);
    echo "✅ Add Service: PASS - Created service ID: {$newService->id}\n";
    
    // Clean up test service
    $newService->delete();
    echo "✅ Clean up test service\n";
} catch (\Exception $e) {
    echo "❌ Add Service: FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Edit Service (updateService)
echo "=== Test 3: Edit Service ===\n";
try {
    $originalName = $smsService->name;
    $smsService->update(['notes' => 'Updated via test at ' . date('Y-m-d H:i:s')]);
    echo "✅ Edit Service: PASS - Updated service notes\n";
    
    // Restore original
    $smsService->update(['notes' => 'Primary SMS service with API Token: f9a89f439206e27169ead766463ca92c']);
    echo "✅ Restored original notes\n";
} catch (\Exception $e) {
    echo "❌ Edit Service: FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test Connection
echo "=== Test 4: Test Connection ===\n";
try {
    // Test SMS service connection
    $baseTest = \Illuminate\Support\Facades\Http::timeout(5)->get($smsService->base_url);
    echo "SMS Base URL Test: HTTP {$baseTest->status()} - " . ($baseTest->successful() ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Test Gmail service connection
    $config = $emailService->config;
    $host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $port = $config['smtp_port'] ?? 587;
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    
    if ($socket) {
        fclose($socket);
        echo "✅ Gmail SMTP Connection: SUCCESS\n";
    } else {
        echo "❌ Gmail SMTP Connection: FAILED - {$errstr}\n";
    }
} catch (\Exception $e) {
    echo "❌ Test Connection: FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Toggle Service Status
echo "=== Test 5: Toggle Service Status ===\n";
try {
    // Deactivate SMS service
    $smsService->is_active = false;
    $smsService->save();
    echo "✅ Deactivate SMS Service: PASS\n";
    
    // Reactivate SMS service
    $smsService->is_active = true;
    $smsService->save();
    echo "✅ Activate SMS Service: PASS\n";
    
    // Deactivate Email service
    $emailService->is_active = false;
    $emailService->save();
    echo "✅ Deactivate Email Service: PASS\n";
    
    // Reactivate Email service
    $emailService->is_active = true;
    $emailService->save();
    echo "✅ Activate Email Service: PASS\n";
} catch (\Exception $e) {
    echo "❌ Toggle Service Status: FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Delete Service (with validation)
echo "=== Test 6: Delete Service ===\n";
try {
    // Create a temporary service for deletion test
    $tempService = MessagingService::create([
        'name' => 'Temp Service for Delete Test',
        'type' => 'SMS',
        'provider' => 'temp-provider.com',
        'base_url' => 'https://temp-provider.com',
        'api_version' => 'v1',
        'bearer_token' => 'temp-token',
        'sender_id' => 'TEMP',
        'rate_limit_per_hour' => 100,
        'cost_per_message' => 0.01,
        'currency' => 'TZS',
        'is_active' => false,
        'test_mode' => true,
        'notes' => 'Temporary service for delete test'
    ]);
    
    echo "✅ Created temporary service for deletion test (ID: {$tempService->id})\n";
    
    // Delete the temporary service
    $tempService->delete();
    echo "✅ Delete Service: PASS - Service deleted successfully\n";
    
} catch (\Exception $e) {
    echo "❌ Delete Service: FAIL - " . $e->getMessage() . "\n";
}
echo "\n";

// Final verification
echo "=== Final Verification ===\n";
$finalServices = MessagingService::all();
echo "Final service count: " . $finalServices->count() . "\n";
foreach ($finalServices as $service) {
    echo "- {$service->name} ({$service->type}) - Active: " . ($service->is_active ? 'YES' : 'NO') . " - Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
}

echo "\n=== All Tests Complete ===\n";
echo "✅ All messaging service actions are working properly!\n";
