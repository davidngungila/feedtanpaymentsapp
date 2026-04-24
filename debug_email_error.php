<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Email Sending Error ===\n\n";

// Test 1: Check email service configuration
echo "=== Test 1: Check Email Service Configuration ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        echo "✅ Email service found:\n";
        echo "- ID: {$emailService->id}\n";
        echo "- Name: {$emailService->name}\n";
        echo "- Base URL: " . ($emailService->base_url ?? 'Not set') . "\n";
        echo "- Test Mode: " . ($emailService->test_mode ? 'Yes' : 'No') . "\n";
        echo "- API Key: " . ($emailService->api_key ? 'Set' : 'Not set') . "\n";
        echo "- Config: " . ($emailService->config ? 'Set' : 'Not set') . "\n";
        
        // Check config details
        if ($emailService->config) {
            $config = json_decode($emailService->config, true);
            echo "- From Email: " . ($config['from_email'] ?? 'Not set') . "\n";
            echo "- SMTP Host: " . ($config['smtp_host'] ?? 'Not set') . "\n";
            echo "- SMTP Port: " . ($config['smtp_port'] ?? 'Not set') . "\n";
        }
        
        // Check API endpoints
        echo "\nAPI Endpoints:\n";
        $sendEndpoint = $emailService->test_mode 
            ? $emailService->getApiEndpoint('email/test/send') 
            : $emailService->getApiEndpoint('email/send');
        echo "- Send Endpoint: " . ($sendEndpoint ?? 'Not available') . "\n";
        
    } else {
        echo "❌ No email service found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking email service: " . $e->getMessage() . "\n";
}

// Test 2: Test API endpoint directly
echo "\n=== Test 2: Test API Endpoint ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        $endpoint = $emailService->test_mode 
            ? $emailService->getApiEndpoint('email/test/send') 
            : $emailService->getApiEndpoint('email/send');
            
        if ($endpoint) {
            echo "Testing endpoint: {$endpoint}\n";
            
            $payload = [
                'from' => 'feedtan15@gmail.com',
                'to' => 'davidngungila@gmail.com',
                'subject' => 'Test Email - Debug',
                'html' => '<p>This is a test email for debugging.</p>'
            ];
            
            echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
            
            $headers = $emailService->getApiHeaders();
            echo "Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
            
            // Make the API call
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                           ->timeout(30)
                           ->post($endpoint, $payload);
            
            echo "\nAPI Response:\n";
            echo "- Status Code: " . $response->status() . "\n";
            echo "- Content Type: " . $response->header('Content-Type') . "\n";
            echo "- Response Body: " . substr($response->body(), 0, 500) . "...\n";
            
            // Try to parse JSON
            try {
                $json = $response->json();
                echo "- JSON Parse: ✅ Success\n";
                echo "- JSON Data: " . json_encode($json, JSON_PRETTY_PRINT) . "\n";
            } catch (\Exception $jsonError) {
                echo "- JSON Parse: ❌ Failed - " . $jsonError->getMessage() . "\n";
                echo "- Raw Response: " . $response->body() . "\n";
            }
            
        } else {
            echo "❌ No endpoint available\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing API: " . $e->getMessage() . "\n";
}

// Test 3: Check if email service is properly configured for SMTP
echo "\n=== Test 3: Check SMTP Configuration ===\n";
try {
    $emailService = \App\Models\MessagingService::where('type', 'EMAIL')->first();
    
    if ($emailService) {
        $isReady = $emailService->isReady();
        echo "- Service Ready: " . ($isReady ? 'Yes' : 'No') . "\n";
        
        // Check what's missing
        $config = json_decode($emailService->config, true);
        $missing = [];
        
        if (!$config['from_email']) $missing[] = 'from_email';
        if (!$config['smtp_host']) $missing[] = 'smtp_host';
        if (!$config['smtp_port']) $missing[] = 'smtp_port';
        if (!$config['smtp_username']) $missing[] = 'smtp_username';
        if (!$config['smtp_password']) $missing[] = 'smtp_password';
        
        if (!empty($missing)) {
            echo "- Missing config: " . implode(', ', $missing) . "\n";
        } else {
            echo "- All required config present\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking SMTP config: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Summary ===\n";
echo "The 'Unexpected token <' error typically occurs when:\n";
echo "1. API endpoint returns HTML instead of JSON\n";
echo "2. API endpoint is not configured correctly\n";
echo "3. Service is trying to use external API instead of SMTP\n";
echo "4. Missing or incorrect API configuration\n";

echo "\n=== Recommendations ===\n";
echo "1. Check if the email service should use SMTP instead of API\n";
echo "2. Verify the API endpoint URL is correct\n";
echo "3. Ensure API credentials are properly set\n";
echo "4. Consider using Laravel's built-in mail system instead\n";

echo "\n=== Test Complete ===\n";
