<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;

echo "=== Testing Messaging Services ===\n\n";

// Test SMS Services
$smsServices = MessagingService::where('type', 'SMS')->where('is_active', true)->get();
echo "SMS Services:\n";
foreach ($smsServices as $service) {
    echo "- {$service->name}: {$service->provider} (Sender: {$service->sender_id})\n";
    echo "  API Token: " . substr($service->bearer_token, 0, 10) . "...\n";
    echo "  Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
    echo "  Test Mode: " . ($service->test_mode ? 'YES' : 'NO') . "\n\n";
}

// Test Email Services
$emailServices = MessagingService::where('type', 'EMAIL')->where('is_active', true)->get();
echo "Email Services:\n";
foreach ($emailServices as $service) {
    echo "- {$service->name}: {$service->provider}\n";
    echo "  From Email: " . ($service->config['from_email'] ?? 'N/A') . "\n";
    echo "  Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
    echo "  Test Mode: " . ($service->test_mode ? 'YES' : 'NO') . "\n\n";
}

echo "=== Total Active Services ===\n";
echo "SMS: " . $smsServices->count() . "\n";
echo "Email: " . $emailServices->count() . "\n";
echo "Total: " . ($smsServices->count() + $emailServices->count()) . "\n";
