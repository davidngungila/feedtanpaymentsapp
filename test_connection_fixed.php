<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessagingService;

echo "=== Testing Service Connection ===\n\n";

// Get the first SMS service
$smsService = MessagingService::where('type', 'SMS')->where('is_active', true)->first();
if ($smsService) {
    echo "Testing SMS Service: {$smsService->name}\n";
    echo "Provider: {$smsService->provider}\n";
    echo "Base URL: {$smsService->base_url}\n";
    echo "Bearer Token: " . substr($smsService->bearer_token, 0, 10) . "...\n";
    echo "Sender ID: {$smsService->sender_id}\n\n";
    
    // Test different endpoint formats
    $endpoints = [
        $smsService->base_url . '/api/v2/sms/test/text/single',
        $smsService->base_url . '/api/sms/test/text/single',
        $smsService->base_url . '/sms/test/text/single',
        $smsService->base_url . '/api/sms/text/single/test',
        $smsService->base_url . '/sms/text/single'
    ];

    $payload = [
        'from' => $smsService->sender_id,
        'to' => '255700000000',
        'text' => 'Test message from FeedTan Pay'
    ];

    foreach ($endpoints as $index => $endpoint) {
        echo "Testing endpoint " . ($index + 1) . ": {$endpoint}\n";
        echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders($smsService->getApiHeaders())
                               ->timeout(10)
                               ->post($endpoint, $payload);

            echo "Response Status: {$response->status()}\n";
            echo "Response Body:\n";
            echo $response->body() . "\n\n";
            
            if ($response->successful()) {
                echo "✅ SMS Service Connection: SUCCESS (Endpoint " . ($index + 1) . ")\n";
                break;
            } else {
                echo "❌ SMS Service Connection: FAILED (Endpoint " . ($index + 1) . ")\n";
            }
        } catch (\Exception $e) {
            echo "❌ SMS Service Connection: ERROR - " . $e->getMessage() . " (Endpoint " . ($index + 1) . ")\n";
        }
        echo str_repeat("-", 50) . "\n\n";
    }
} else {
    echo "❌ No active SMS service found\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Get the Gmail email service
$emailService = MessagingService::where('type', 'EMAIL')->where('provider', 'gmail')->where('is_active', true)->first();
if ($emailService) {
    echo "Testing Gmail Email Service: {$emailService->name}\n";
    echo "Provider: {$emailService->provider}\n";
    echo "Username: {$emailService->username}\n";
    echo "SMTP Host: " . ($emailService->config['smtp_host'] ?? 'smtp.gmail.com') . "\n";
    echo "SMTP Port: " . ($emailService->config['smtp_port'] ?? 587) . "\n\n";
    
    // Test the connection
    try {
        $config = $emailService->config;
        $timeout = 10;
        $host = $config['smtp_host'] ?? 'smtp.gmail.com';
        $port = $config['smtp_port'] ?? 587;
        
        echo "Testing socket connection to {$host}:{$port}...\n";
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if ($socket) {
            fclose($socket);
            echo "✅ Gmail SMTP Connection: SUCCESS\n";
            
            if ($emailService->username && $emailService->password) {
                echo "✅ Credentials: Present\n";
            } else {
                echo "❌ Credentials: Missing\n";
            }
        } else {
            echo "❌ Gmail SMTP Connection: FAILED - {$errstr} (Error {$errno})\n";
        }
    } catch (\Exception $e) {
        echo "❌ Gmail SMTP Connection: ERROR - " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No active Gmail email service found\n";
}

echo "\n=== Test Complete ===\n";
