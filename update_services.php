<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;

echo "=== Updating Messaging Services ===\n\n";

// Clean up and create only two services
MessagingService::truncate();

// Create SMS Service
$smsService = MessagingService::create([
    'name' => 'Primary SMS Service',
    'type' => 'SMS',
    'provider' => 'messaging-service.co.tz',
    'base_url' => 'https://messaging-service.co.tz',
    'api_version' => 'v2',
    'api_key' => 'f9a89f439206e27169ead766463ca92c',
    'bearer_token' => 'f9a89f439206e27169ead766463ca92c',
    'sender_id' => 'FEEDTAN',
    'rate_limit_per_hour' => 1000,
    'cost_per_message' => 0.0160,
    'currency' => 'TZS',
    'is_active' => true,
    'test_mode' => false,
    'webhook_url' => 'https://feedtanpay.co.tz/webhook/sms',
    'notes' => 'Primary SMS service with API Token: f9a89f439206e27169ead766463ca92c',
]);

echo "✅ SMS Service Created: {$smsService->name} (ID: {$smsService->id})\n";

// Create Email Service
$emailService = MessagingService::create([
    'name' => 'Primary Email Service - Gmail',
    'type' => 'EMAIL',
    'provider' => 'gmail',
    'base_url' => 'https://gmail.googleapis.com',
    'api_version' => 'v1',
    'username' => 'feedtan15@gmail.com',
    'password' => 'dmxf jyhl eymc libp',
    'sender_id' => 'FeedTan Pay',
    'config' => [
        'from_email' => 'feedtan15@gmail.com',
        'from_name' => 'FeedTan Pay',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'encryption' => 'tls'
    ],
    'rate_limit_per_hour' => 2000,
    'cost_per_message' => 0.0000,
    'currency' => 'TZS',
    'is_active' => true,
    'test_mode' => false,
    'webhook_url' => 'https://feedtanpay.co.tz/webhook/email',
    'notes' => 'Primary Gmail service with app password',
]);

echo "✅ Email Service Created: {$emailService->name} (ID: {$emailService->id})\n";

echo "\n=== Services Summary ===\n";
echo "Total Services: " . MessagingService::count() . "\n";
echo "SMS Services: " . MessagingService::where('type', 'SMS')->count() . "\n";
echo "Email Services: " . MessagingService::where('type', 'EMAIL')->count() . "\n";

echo "\n=== Testing Services ===\n";
$smsServices = MessagingService::where('type', 'SMS')->get();
$emailServices = MessagingService::where('type', 'EMAIL')->get();

foreach ($smsServices as $service) {
    echo "SMS: {$service->name} - Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
}

foreach ($emailServices as $service) {
    echo "Email: {$service->name} - Ready: " . ($service->isReady() ? 'YES' : 'NO') . "\n";
    echo "  From Email: " . ($service->config['from_email'] ?? 'N/A') . "\n";
}

echo "\n=== Update Complete ===\n";
