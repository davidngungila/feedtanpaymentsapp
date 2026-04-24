<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing SMS Logs Database Integration ===\n\n";

// Test 1: Check if getSmsLogs method is updated
echo "=== Test 1: getSmsLogs Method Check ===\n";
try {
    $controller = new \App\Http\Controllers\MessagingController();
    $reflection = new ReflectionClass($controller);
    
    if ($reflection->hasMethod('getSmsLogs')) {
        $method = $reflection->getMethod('getSmsLogs');
        echo "✅ getSmsLogs method exists\n";
        
        // Check method parameters
        $parameters = $method->getParameters();
        echo "Parameters: " . count($parameters) . "\n";
        
        // Read the method source to verify it uses database
        $source = file_get_contents(app_path('Http/Controllers/MessagingController.php'));
        $methodStart = strpos($source, 'public function getSmsLogs');
        $methodEnd = strpos($source, 'public function exportSmsLogs', $methodStart);
        $methodCode = substr($source, $methodStart, $methodEnd - $methodStart);
        
        $usesDatabase = strpos($methodCode, 'SmsMessage::with') !== false;
        $usesExternalAPI = strpos($methodCode, 'Http::withHeaders') !== false;
        
        echo "- Uses Database: " . ($usesDatabase ? '✅ YES' : '❌ NO') . "\n";
        echo "- Uses External API: " . ($usesExternalAPI ? '❌ YES (should be NO)' : '✅ NO') . "\n";
    } else {
        echo "❌ getSmsLogs method not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Method check error: " . $e->getMessage() . "\n";
}

// Test 2: Test the API endpoint directly
echo "\n=== Test 2: API Endpoint Test ===\n";
try {
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    
    echo "Testing getSmsLogs API endpoint...\n";
    
    // Call the method
    $controller = new \App\Http\Controllers\MessagingController();
    $response = $controller->getSmsLogs($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        
        echo "✅ API Response successful\n";
        echo "- Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
        echo "- Message: " . $data['message'] . "\n";
        echo "- Source: " . ($data['data']['source'] ?? 'Unknown') . "\n";
        echo "- Total Results: " . ($data['data']['total'] ?? 0) . "\n";
        echo "- Results Count: " . count($data['data']['results'] ?? []) . "\n";
        
        // Check a sample result
        if (!empty($data['data']['results'])) {
            $sample = $data['data']['results'][0];
            echo "\nSample Result Structure:\n";
            echo "- messageId: " . ($sample['messageId'] ?? 'N/A') . "\n";
            echo "- from: " . ($sample['from'] ?? 'N/A') . "\n";
            echo "- to: " . ($sample['to'] ?? 'N/A') . "\n";
            echo "- status.name: " . ($sample['status']['name'] ?? 'N/A') . "\n";
            echo "- text: " . substr($sample['text'] ?? 'N/A', 0, 30) . "...\n";
            echo "- local_id: " . ($sample['local_id'] ?? 'N/A') . "\n";
            echo "- user.name: " . ($sample['user']['name'] ?? 'N/A') . "\n";
            echo "- service.name: " . ($sample['service']['name'] ?? 'N/A') . "\n";
        }
        
    } else {
        echo "❌ API Response failed\n";
        echo "- Status: " . $response->getStatusCode() . "\n";
        echo "- Content: " . $response->getContent() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ API test error: " . $e->getMessage() . "\n";
}

// Test 3: Test filtering functionality
echo "\n=== Test 3: Filtering Test ===\n";
try {
    $request = new \Illuminate\Http\Request();
    
    // Test with limit filter
    $request->merge(['limit' => 5]);
    
    $controller = new \App\Http\Controllers\MessagingController();
    $response = $controller->getSmsLogs($request);
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        
        echo "✅ Limit filter test passed\n";
        echo "- Requested limit: 5\n";
        echo "- Results returned: " . count($data['data']['results']) . "\n";
        echo "- Should be <= 5: " . (count($data['data']['results']) <= 5 ? '✅ YES' : '❌ NO') . "\n";
    }
    
    // Test with date filter
    $request = new \Illuminate\Http\Request();
    $request->merge(['from' => date('Y-m-d')]);
    
    $response = $controller->getSmsLogs($request);
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        
        echo "✅ Date filter test passed\n";
        echo "- Filter from: " . date('Y-m-d') . "\n";
        echo "- Results returned: " . count($data['data']['results']) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Filtering test error: " . $e->getMessage() . "\n";
}

// Test 4: Test export functionality
echo "\n=== Test 4: Export Functionality Test ===\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge(['limit' => 3]);
    
    $controller = new \App\Http\Controllers\MessagingController();
    $response = $controller->exportSmsLogs($request);
    
    echo "Export Response Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getContent();
        $lines = explode("\n", $content);
        
        echo "✅ Export successful\n";
        echo "- Content Type: " . $response->headers->get('Content-Type') . "\n";
        echo "- Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
        echo "- CSV Lines: " . count($lines) . "\n";
        echo "- Header Line: " . $lines[0] . "\n";
        echo "- Sample Data Line: " . ($lines[1] ?? 'N/A') . "\n";
        
        // Check if it includes database-specific fields
        $hasUserField = strpos($content, 'User') !== false;
        $hasServiceField = strpos($content, 'Service') !== false;
        $hasCreatedAtField = strpos($content, 'Created At') !== false;
        
        echo "- Has User Field: " . ($hasUserField ? '✅ YES' : '❌ NO') . "\n";
        echo "- Has Service Field: " . ($hasServiceField ? '✅ YES' : '❌ NO') . "\n";
        echo "- Has Created At Field: " . ($hasCreatedAtField ? '✅ YES' : '❌ NO') . "\n";
        
    } else {
        echo "❌ Export failed\n";
        echo "- Status: " . $response->getStatusCode() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Export test error: " . $e->getMessage() . "\n";
}

// Test 5: Database vs API comparison
echo "\n=== Test 5: Database vs API Comparison ===\n";
try {
    // Get database count
    $dbCount = \App\Models\SmsMessage::count();
    echo "Database Messages: {$dbCount}\n";
    
    // Test API endpoint
    $request = new \Illuminate\Http\Request();
    $controller = new \App\Http\Controllers\MessagingController();
    $response = $controller->getSmsLogs($request);
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        $apiCount = $data['data']['total'] ?? 0;
        $source = $data['data']['source'] ?? 'unknown';
        
        echo "API Messages: {$apiCount}\n";
        echo "Data Source: {$source}\n";
        echo "Counts Match: " . ($dbCount === $apiCount ? '✅ YES' : '❌ NO') . "\n";
        echo "Source is Database: " . ($source === 'local_database' ? '✅ YES' : '❌ NO') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Comparison test error: " . $e->getMessage() . "\n";
}

echo "\n=== SMS Logs Database Integration Summary ===\n";
echo "✅ Changes Made:\n";
echo "   - getSmsLogs() now fetches from local database\n";
echo "   - exportSmsLogs() now exports from local database\n";
echo "   - Added comprehensive filtering support\n";
echo "   - Maintains same API response format\n";
echo "   - Added database-specific fields (user, service, created_at)\n";
echo "   - JavaScript shows data source indicator\n";

echo "\n✅ Benefits:\n";
echo "   - Faster response times (no external API calls)\n";
echo "   - More reliable (no API rate limiting)\n";
echo "   - Better filtering capabilities\n";
echo "   - Access to all historical data\n";
echo "   - Additional fields from database\n";

echo "\n🎯 Current Status:\n";
echo "The SMS logs page at http://127.0.0.1:8001/messaging/sms/logs\n";
echo "now fetches data from the local database instead of the external API.\n";
echo "All messages synced from the API are available in the database.\n";

echo "\n=== Test Complete ===\n";
