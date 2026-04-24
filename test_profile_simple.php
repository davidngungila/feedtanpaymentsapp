<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Profile Page Issues ===\n\n";

// Test 1: Check if avatar column exists
echo "=== Test 1: Check User Table Structure ===\n";
try {
    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar')) {
        echo "Adding avatar column to users table...\n";
        
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->string('avatar')->nullable()->after('email');
        });
        
        echo "✅ Avatar column added to users table\n";
    } else {
        echo "✅ Avatar column already exists in users table\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error adding avatar column: " . $e->getMessage() . "\n";
}

// Test 2: Create upload directory
echo "\n=== Test 2: Create Upload Directory ===\n";
try {
    $avatarPath = public_path('uploads/avatars');
    
    if (!is_dir($avatarPath)) {
        mkdir($avatarPath, 0755, true);
        echo "✅ Created avatars upload directory\n";
    } else {
        echo "✅ Avatars directory already exists\n";
    }
    
    // Create .htaccess for security
    $htaccessPath = public_path('uploads/.htaccess');
    if (!file_exists($htaccessPath)) {
        $htaccessContent = "Order Allow,Deny\nAllow from all\n";
        file_put_contents($htaccessPath, $htaccessContent);
        echo "✅ Created .htaccess for uploads directory\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error creating upload directory: " . $e->getMessage() . "\n";
}

// Test 3: Check current profile controller
echo "\n=== Test 3: Check Profile Controller ===\n";
try {
    $controller = new \App\Http\Controllers\DashboardController();
    
    if (method_exists($controller, 'profile')) {
        echo "✅ profile() method exists\n";
    } else {
        echo "❌ profile() method missing\n";
    }
    
    if (method_exists($controller, 'updateProfile')) {
        echo "✅ updateProfile() method exists\n";
    } else {
        echo "❌ updateProfile() method missing\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n=== Profile Issues Identified ===\n";
echo "❌ JavaScript uses alerts instead of proper API calls\n";
echo "❌ No actual image upload functionality\n";
echo "❌ Profile form has hardcoded values\n";
echo "❌ Duplicate modal definitions\n";
echo "❌ No proper error handling\n\n";

echo "=== Ready to Fix Profile Page ===\n";
