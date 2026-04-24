<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Template Loading Issues ===\n\n";

// Test 1: Check if EmailTemplate model exists
echo "=== Test 1: Check EmailTemplate Model ===\n";
try {
    if (class_exists('App\Models\EmailTemplate')) {
        echo "✅ EmailTemplate model exists\n";
        
        // Test model methods
        $template = new \App\Models\EmailTemplate();
        echo "✅ EmailTemplate model can be instantiated\n";
        
        // Check if table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('email_templates')) {
            echo "✅ email_templates table exists\n";
            
            // Check table structure
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('email_templates');
            echo "Table columns: " . implode(', ', $columns) . "\n";
            
            // Check if data exists
            $count = \App\Models\EmailTemplate::count();
            echo "Total templates in database: {$count}\n";
            
            if ($count > 0) {
                echo "✅ Templates exist in database\n";
                
                // Show first template details
                $firstTemplate = \App\Models\EmailTemplate::first();
                echo "First template:\n";
                echo "- ID: {$firstTemplate->id}\n";
                echo "- Name: {$firstTemplate->name}\n";
                echo "- Category: {$firstTemplate->category}\n";
                echo "- Subject: {$firstTemplate->subject}\n";
                echo "- HTML Length: " . strlen($firstTemplate->html_content) . "\n";
                echo "- Is Active: " . ($firstTemplate->is_active ? 'Yes' : 'No') . "\n";
                
            } else {
                echo "❌ No templates found in database\n";
            }
        } else {
            echo "❌ email_templates table does not exist\n";
        }
    } else {
        echo "❌ EmailTemplate model does not exist\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking EmailTemplate model: " . $e->getMessage() . "\n";
}

// Test 2: Test API endpoint directly
echo "\n=== Test 2: Test API Endpoint ===\n";
try {
    $templates = \App\Models\EmailTemplate::active()->get();
    
    if ($templates->count() > 0) {
        $template = $templates->first();
        
        echo "Testing template loading for ID: {$template->id}\n";
        
        // Test the controller method directly
        $controller = new \App\Http\Controllers\MessagingController();
        $response = $controller->getEmailTemplate($template->id);
        
        echo "API Response Status: " . $response->getStatusCode() . "\n";
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            echo "API Response Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
            
            if ($data['success']) {
                echo "Template Name: " . $data['data']['name'] . "\n";
                echo "Template Subject: " . $data['data']['subject'] . "\n";
                echo "HTML Content Length: " . strlen($data['data']['html_content']) . "\n";
                echo "Variables Count: " . count($data['data']['variables']) . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing API endpoint: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Check template content for color scheme
echo "\n=== Test 3: Check Template Color Scheme ===\n";
try {
    $templates = \App\Models\EmailTemplate::all();
    
    foreach ($templates as $template) {
        echo "Template: {$template->name}\n";
        
        // Check for current color scheme
        $htmlContent = $template->html_content;
        
        // Look for gradient colors
        if (preg_match('/background:\s*linear-gradient\([^)]+\)/', $htmlContent, $matches)) {
            echo "Current gradient: " . $matches[0] . "\n";
        }
        
        // Look for FeedTan branding
        if (strpos($htmlContent, 'FeedTan') !== false) {
            echo "✅ Contains FeedTan branding\n";
        } else {
            echo "❌ Missing FeedTan branding\n";
        }
        
        // Look for main colors
        $colors = [];
        if (preg_match_all('/#[0-9a-fA-F]{6}/', $htmlContent, $matches)) {
            $colors = array_unique($matches[0]);
            echo "Colors used: " . implode(', ', $colors) . "\n";
        }
        
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking template colors: " . $e->getMessage() . "\n";
}

// Test 4: Test template preview
echo "\n=== Test 4: Test Template Preview ===\n";
try {
    $template = \App\Models\EmailTemplate::first();
    
    if ($template) {
        echo "Testing preview for template: {$template->name}\n";
        
        $controller = new \App\Http\Controllers\MessagingController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'template_id' => $template->id,
            'variables' => [
                'memberName' => 'Test User',
                'memberNumber' => 'FT2026001',
                'joinDate' => '2026-04-23',
                'currentDate' => date('Y-m-d'),
                'companyName' => 'FeedTan Community Microfinance Group'
            ]
        ]);
        
        $response = $controller->previewEmailTemplate($request);
        
        echo "Preview Response Status: " . $response->getStatusCode() . "\n";
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            echo "Preview Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
            
            if ($data['success']) {
                echo "Processed Subject: " . $data['data']['subject'] . "\n";
                echo "Processed HTML Length: " . strlen($data['data']['html']) . "\n";
                
                // Check if variables were replaced
                if (strpos($data['data']['html'], 'Test User') !== false) {
                    echo "✅ Variables replaced correctly\n";
                } else {
                    echo "❌ Variables not replaced\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing template preview: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Summary ===\n";
echo "This will help identify the template loading issue and color scheme problems\n";
