<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Messaging Services Table Structure ===\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('messaging_services');
echo "Columns: " . implode(', ', $columns) . "\n\n";

$service = \App\Models\MessagingService::where('type', 'EMAIL')->first();
if ($service) {
    echo "Service config: " . $service->config . "\n\n";
    
    $config = json_decode($service->config, true);
    if ($config) {
        echo "SMTP Host: " . ($config['smtp_host'] ?? 'Not set') . "\n";
        echo "SMTP Username: " . ($config['smtp_username'] ?? 'Not set') . "\n";
        echo "SMTP Password: " . ($config['smtp_password'] ? 'Set' : 'Not set') . "\n";
        echo "SMTP Port: " . ($config['smtp_port'] ?? 'Not set') . "\n";
        echo "SMTP Encryption: " . ($config['smtp_encryption'] ?? 'Not set') . "\n";
    }
}
