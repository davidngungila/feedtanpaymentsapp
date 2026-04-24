<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug View Details Action ===\n\n";

// Test 1: Check if there are email messages to test with
echo "=== Test 1: Check Available Email Messages ===\n";
try {
    $messages = \App\Models\EmailMessage::with('messagingService', 'user')->orderBy('created_at', 'desc')->take(3)->get();
    
    echo "Total email messages: " . $messages->count() . "\n";
    
    if ($messages->count() > 0) {
        foreach ($messages as $message) {
            echo "Message ID: {$message->id}, Message ID: {$message->message_id}\n";
            echo "- To: {$message->to_email}\n";
            echo "- Subject: {$message->subject}\n";
            echo "- Status: {$message->status_name}\n";
            echo "- Created: " . $message->created_at->format('Y-m-d H:i:s') . "\n";
            echo "- Has Service: " . ($message->messagingService ? 'Yes' : 'No') . "\n";
            echo "- Has User: " . ($message->user ? 'Yes' : 'No') . "\n";
            echo "\n";
        }
    } else {
        echo "❌ No email messages found to test View Details\n";
        
        // Create a test message
        echo "Creating a test email message...\n";
        $service = \App\Models\MessagingService::where('type', 'EMAIL')->first();
        if ($service) {
            $testMessage = \App\Models\EmailMessage::create([
                'messaging_service_id' => $service->id,
                'user_id' => 1,
                'message_id' => 'EMAIL_TEST_' . time(),
                'from_name' => 'FeedTan Pay',
                'from_email' => 'feedtan15@gmail.com',
                'to_email' => 'test@example.com',
                'to_name' => 'Test User',
                'subject' => 'Test Email for View Details',
                'body_html' => '<h3>Test Email</h3><p>This is a test email for View Details functionality.</p>',
                'body_text' => 'Test Email - This is a test email for View Details functionality.',
                'status_name' => 'sent',
                'custom_data' => json_encode([
                    'template_id' => 1,
                    'sent_via' => 'test_system'
                ])
            ]);
            
            echo "✅ Test message created with ID: {$testMessage->id}\n";
            $messages = \App\Models\EmailMessage::with('messagingService', 'user')->orderBy('created_at', 'desc')->take(1)->get();
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking email messages: " . $e->getMessage() . "\n";
}

// Test 2: Test the getEmailMessage API endpoint directly
echo "\n=== Test 2: Test getEmailMessage API Endpoint ===\n";
try {
    if ($messages->count() > 0) {
        $message = $messages->first();
        
        echo "Testing getEmailMessage with message ID: {$message->id}\n";
        
        $controller = new \App\Http\Controllers\MessagingController();
        $response = $controller->getEmailMessage($message->id);
        
        echo "API Response Status: " . $response->getStatusCode() . "\n";
        echo "API Response Content: " . $response->getContent() . "\n";
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            echo "API Response Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
            
            if ($data['success']) {
                echo "Response Data Structure:\n";
                echo "- Has message_id: " . (isset($data['message_id']) ? 'Yes' : 'No') . "\n";
                echo "- Has from_email: " . (isset($data['from_email']) ? 'Yes' : 'No') . "\n";
                echo "- Has from_name: " . (isset($data['from_name']) ? 'Yes' : 'No') . "\n";
                echo "- Has to_email: " . (isset($data['to_email']) ? 'Yes' : 'No') . "\n";
                echo "- Has to_name: " . (isset($data['to_name']) ? 'Yes' : 'No') . "\n";
                echo "- Has subject: " . (isset($data['subject']) ? 'Yes' : 'No') . "\n";
                echo "- Has status_name: " . (isset($data['status_name']) ? 'Yes' : 'No') . "\n";
                echo "- Has sent_at: " . (isset($data['sent_at']) ? 'Yes' : 'No') . "\n";
                echo "- Has created_at: " . (isset($data['created_at']) ? 'Yes' : 'No') . "\n";
                echo "- Has service: " . (isset($data['service']) ? 'Yes' : 'No') . "\n";
                echo "- Has user: " . (isset($data['user']) ? 'Yes' : 'No') . "\n";
                
                if (isset($data['service'])) {
                    echo "- Service Name: " . ($data['service']['name'] ?? 'N/A') . "\n";
                    echo "- Service Type: " . ($data['service']['type'] ?? 'N/A') . "\n";
                }
                
                if (isset($data['user'])) {
                    echo "- User Name: " . ($data['user']['name'] ?? 'N/A') . "\n";
                    echo "- User Email: " . ($data['user']['email'] ?? 'N/A') . "\n";
                }
            }
        } else {
            echo "❌ API call failed\n";
            echo "Status Code: " . $response->getStatusCode() . "\n";
            echo "Content: " . $response->getContent() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing getEmailMessage API: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Test the API route directly
echo "\n=== Test 3: Test API Route ===\n";
try {
    if ($messages->count() > 0) {
        $message = $messages->first();
        
        echo "Testing API route: /api/email-messages/{$message->id}\n";
        
        // Simulate a web request to the API route
        $request = \Illuminate\Http\Request::create("/api/email-messages/{$message->id}", 'GET');
        
        // Get the route
        $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
        
        if ($route) {
            echo "✅ Route found: " . $route->getName() . "\n";
            echo "Controller: " . $route->getAction('uses') . "\n";
            echo "Middleware: " . implode(', ', $route->middleware()) . "\n";
        } else {
            echo "❌ Route not found\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing API route: " . $e->getMessage() . "\n";
}

// Test 4: Check the frontend JavaScript function
echo "\n=== Test 4: Check Frontend JavaScript Function ===\n";
try {
    echo "Checking viewEmailMessage function...\n";
    
    // Read the email index view file to check the JavaScript function
    $viewFile = resource_path('views/messaging/email/index.blade.php');
    
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        
        if (strpos($content, 'window.viewEmailMessage') !== false) {
            echo "✅ viewEmailMessage function is globally accessible\n";
        } else {
            echo "❌ viewEmailMessage function is not globally accessible\n";
        }
        
        if (strpos($content, 'function viewEmailMessage') !== false) {
            echo "✅ viewEmailMessage function is defined\n";
        } else {
            echo "❌ viewEmailMessage function is not defined\n";
        }
        
        // Check the API call in the function
        if (strpos($content, '/api/email-messages/') !== false) {
            echo "✅ API call path is correct\n";
        } else {
            echo "❌ API call path is incorrect\n";
        }
        
        // Check the modal handling
        if (strpos($content, 'emailDetailsModal') !== false) {
            echo "✅ Modal handling is present\n";
        } else {
            echo "❌ Modal handling is missing\n";
        }
    } else {
        echo "❌ Email index view file not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking JavaScript function: " . $e->getMessage() . "\n";
}

// Test 5: Check the modal structure
echo "\n=== Test 5: Check Modal Structure ===\n";
try {
    $viewFile = resource_path('views/messaging/email/index.blade.php');
    
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        
        if (strpos($content, 'emailDetailsModal') !== false) {
            echo "✅ emailDetailsModal exists\n";
            
            // Check modal structure
            if (strpos($content, 'emailDetailsContent') !== false) {
                echo "✅ emailDetailsContent div exists\n";
            } else {
                echo "❌ emailDetailsContent div missing\n";
            }
            
            if (strpos($content, 'modal-header') !== false) {
                echo "✅ Modal header exists\n";
            } else {
                echo "❌ Modal header missing\n";
            }
            
            if (strpos($content, 'modal-body') !== false) {
                echo "✅ Modal body exists\n";
            } else {
                echo "❌ Modal body missing\n";
            }
        } else {
            echo "❌ emailDetailsModal not found\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking modal structure: " . $e->getMessage() . "\n";
}

// Test 6: Test with a simulated frontend call
echo "\n=== Test 6: Simulate Frontend Call ===\n";
try {
    if ($messages->count() > 0) {
        $message = $messages->first();
        
        echo "Simulating frontend call to viewEmailMessage({$message->id})\n";
        
        // Simulate the JavaScript fetch call
        $apiUrl = "/api/email-messages/{$message->id}";
        
        echo "API URL: {$apiUrl}\n";
        
        // Test if the route exists and is accessible
        try {
            $controller = new \App\Http\Controllers\MessagingController();
            $response = $controller->getEmailMessage($message->id);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);
                
                if ($data['success']) {
                    echo "✅ Frontend would receive valid data\n";
                    
                    // Simulate the modal content generation
                    $modalContent = "
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>Message ID</label>
                                    <div class='fw-bold'>{$data['message_id']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>From Email</label>
                                    <div>{$data['from_email']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>From Name</label>
                                    <div>{$data['from_name']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>To Email</label>
                                    <div>{$data['to_email']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>To Name</label>
                                    <div>{$data['to_name']}</div>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='mb-3'>
                                    <label class='form-label'>Subject</label>
                                    <div class='fw-bold'>{$data['subject']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Status</label>
                                    <div class='badge bg-success'>{$data['status_name']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Service</label>
                                    <div>{$data['service']['name']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Sent At</label>
                                    <div>{$data['sent_at']}</div>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Created At</label>
                                    <div>{$data['created_at']}</div>
                                </div>
                            </div>
                        </div>
                    ";
                    
                    echo "✅ Modal content would be generated successfully\n";
                    echo "Content length: " . strlen($modalContent) . " characters\n";
                } else {
                    echo "❌ API response indicates failure\n";
                }
            } else {
                echo "❌ API call failed with status: " . $response->getStatusCode() . "\n";
            }
        } catch (\Exception $e) {
            echo "❌ Error in API call: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error simulating frontend call: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Summary ===\n";
echo "This will help identify the exact cause of the View Details failure\n";
