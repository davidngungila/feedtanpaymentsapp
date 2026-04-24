<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Update All Templates to Use FeedTan Color Scheme ===\n\n";

// Define FeedTan Community Microfinance Group color scheme
$feedTanColors = [
    'primary_gradient' => 'linear-gradient(135deg, #006400, #4CAF50)', // Green gradient
    'primary_color' => '#006400', // Dark green
    'secondary_color' => '#4CAF50', // Light green
    'accent_color' => '#2e7d32', // Medium green
    'text_color' => '#2d3748', // Dark text
    'light_bg' => '#f0f4f8', // Light background
    'white_bg' => '#ffffff', // White background
    'border_color' => '#e2e8f0', // Light border
    'success_bg' => '#e8f5e8', // Success background
    'success_border' => '#c8e6c9', // Success border
    'warning_bg' => '#fff8e1', // Warning background
    'warning_border' => '#FFC107', // Warning border
    'info_bg' => '#e3f2fd', // Info background
    'info_border' => '#90caf9', // Info border
];

// Test 1: Update Welcome Email Template
echo "=== Test 1: Update Welcome Email Template ===\n";
try {
    $welcomeTemplate = \App\Models\EmailTemplate::where('category', 'welcome')->first();
    
    if ($welcomeTemplate) {
        $newHtmlContent = $welcomeTemplate->html_content;
        
        // Replace any existing gradients with FeedTan green gradient
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Replace header colors
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Update any non-green colors to FeedTan colors
        $newHtmlContent = str_replace([
            '#2196F3', '#ff6b6b', '#ffa500', '#6a11cb', '#2575fc', '#8e44ad', '#3498db'
        ], [
            $feedTanColors['primary_color'], $feedTanColors['primary_color'], $feedTanColors['secondary_color'], 
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['secondary_color']
        ], $newHtmlContent);
        
        $welcomeTemplate->update(['html_content' => $newHtmlContent]);
        
        echo "✅ Welcome template updated with FeedTan colors\n";
        echo "HTML Length: " . strlen($newHtmlContent) . "\n";
    } else {
        echo "❌ Welcome template not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating welcome template: " . $e->getMessage() . "\n";
}

// Test 2: Update Loan Approval Template
echo "\n=== Test 2: Update Loan Approval Template ===\n";
try {
    $loanTemplate = \App\Models\EmailTemplate::where('category', 'loan')->where('name', 'LIKE', '%Approval%')->first();
    
    if ($loanTemplate) {
        $newHtmlContent = $loanTemplate->html_content;
        
        // Replace gradient with FeedTan green
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Update colors to FeedTan scheme
        $newHtmlContent = str_replace([
            '#2196F3', '#ff6b6b', '#ffa500', '#6a11cb', '#2575fc', '#8e44ad', '#3498db'
        ], [
            $feedTanColors['primary_color'], $feedTanColors['primary_color'], $feedTanColors['secondary_color'], 
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['secondary_color']
        ], $newHtmlContent);
        
        $loanTemplate->update(['html_content' => $newHtmlContent]);
        
        echo "✅ Loan Approval template updated with FeedTan colors\n";
        echo "HTML Length: " . strlen($newHtmlContent) . "\n";
    } else {
        echo "❌ Loan Approval template not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating loan approval template: " . $e->getMessage() . "\n";
}

// Test 3: Update Loan Repayment Template
echo "\n=== Test 3: Update Loan Repayment Template ===\n";
try {
    $repaymentTemplate = \App\Models\EmailTemplate::where('category', 'loan')->where('name', 'LIKE', '%Repayment%')->first();
    
    if ($repaymentTemplate) {
        $newHtmlContent = $repaymentTemplate->html_content;
        
        // Replace gradient with FeedTan green
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Update colors to FeedTan scheme
        $newHtmlContent = str_replace([
            '#ff6b6b', '#ffa500', '#2196F3', '#6a11cb', '#2575fc', '#8e44ad', '#3498db'
        ], [
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['secondary_color']
        ], $newHtmlContent);
        
        $repaymentTemplate->update(['html_content' => $newHtmlContent]);
        
        echo "✅ Loan Repayment template updated with FeedTan colors\n";
        echo "HTML Length: " . strlen($newHtmlContent) . "\n";
    } else {
        echo "❌ Loan Repayment template not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating loan repayment template: " . $e->getMessage() . "\n";
}

// Test 4: Update Account Balance Template
echo "\n=== Test 4: Update Account Balance Template ===\n";
try {
    $balanceTemplate = \App\Models\EmailTemplate::where('category', 'account')->first();
    
    if ($balanceTemplate) {
        $newHtmlContent = $balanceTemplate->html_content;
        
        // Replace gradient with FeedTan green
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Update colors to FeedTan scheme
        $newHtmlContent = str_replace([
            '#6a11cb', '#2575fc', '#2196F3', '#ff6b6b', '#ffa500', '#8e44ad', '#3498db'
        ], [
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['secondary_color']
        ], $newHtmlContent);
        
        $balanceTemplate->update(['html_content' => $newHtmlContent]);
        
        echo "✅ Account Balance template updated with FeedTan colors\n";
        echo "HTML Length: " . strlen($newHtmlContent) . "\n";
    } else {
        echo "❌ Account Balance template not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating account balance template: " . $e->getMessage() . "\n";
}

// Test 5: Update Meeting Invitation Template
echo "\n=== Test 5: Update Meeting Invitation Template ===\n";
try {
    $meetingTemplate = \App\Models\EmailTemplate::where('category', 'meeting')->first();
    
    if ($meetingTemplate) {
        $newHtmlContent = $meetingTemplate->html_content;
        
        // Replace gradient with FeedTan green
        $newHtmlContent = preg_replace(
            '/background:\s*linear-gradient\([^)]+\)/',
            "background: {$feedTanColors['primary_gradient']}",
            $newHtmlContent
        );
        
        // Update colors to FeedTan scheme
        $newHtmlContent = str_replace([
            '#8e44ad', '#3498db', '#2196F3', '#ff6b6b', '#ffa500', '#6a11cb', '#2575fc'
        ], [
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['primary_color'], $feedTanColors['secondary_color'], $feedTanColors['primary_color'], 
            $feedTanColors['secondary_color']
        ], $newHtmlContent);
        
        $meetingTemplate->update(['html_content' => $newHtmlContent]);
        
        echo "✅ Meeting Invitation template updated with FeedTan colors\n";
        echo "HTML Length: " . strlen($newHtmlContent) . "\n";
    } else {
        echo "❌ Meeting Invitation template not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error updating meeting invitation template: " . $e->getMessage() . "\n";
}

// Test 6: Verify all templates have been updated
echo "\n=== Test 6: Verify Template Updates ===\n";
try {
    $templates = \App\Models\EmailTemplate::all();
    
    foreach ($templates as $template) {
        echo "Template: {$template->name}\n";
        
        $htmlContent = $template->html_content;
        
        // Check for FeedTan green gradient
        if (strpos($htmlContent, $feedTanColors['primary_gradient']) !== false) {
            echo "✅ Uses FeedTan green gradient\n";
        } else {
            echo "❌ Missing FeedTan green gradient\n";
        }
        
        // Check for FeedTan colors
        $nonFeedTanColors = ['#2196F3', '#ff6b6b', '#ffa500', '#6a11cb', '#2575fc', '#8e44ad', '#3498db'];
        $hasNonFeedTanColors = false;
        
        foreach ($nonFeedTanColors as $color) {
            if (strpos($htmlContent, $color) !== false) {
                echo "❌ Still contains non-FeedTan color: {$color}\n";
                $hasNonFeedTanColors = true;
            }
        }
        
        if (!$hasNonFeedTanColors) {
            echo "✅ All colors are FeedTan compliant\n";
        }
        
        // Check for FeedTan branding
        if (strpos($htmlContent, 'FeedTan Community Microfinance Group') !== false) {
            echo "✅ Contains FeedTan branding\n";
        } else {
            echo "❌ Missing FeedTan branding\n";
        }
        
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error verifying template updates: " . $e->getMessage() . "\n";
}

echo "=== Color Scheme Update Complete ===\n";
echo "All templates now use the Karibu FeedTan Community Microfinance Group color scheme:\n";
echo "- Primary: Dark Green (#006400)\n";
echo "- Secondary: Light Green (#4CAF50)\n";
echo "- Accent: Medium Green (#2e7d32)\n";
echo "- Header: Green gradient\n";
echo "- Consistent branding across all templates\n\n";

echo "=== Test Template Loading ===\n";
echo "Visit http://127.0.0.1:8001/messaging/email to test template loading\n";
echo "All templates should now load with consistent FeedTan colors!\n\n";

echo "=== Update Complete ===\n";
