<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Add Missing Profile Columns ===\n\n";

// Add phone column
echo "Adding phone column...\n";
try {
    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->string('phone')->nullable()->after('email');
        });
        echo "✅ Phone column added successfully\n";
    } else {
        echo "✅ Phone column already exists\n";
    }
} catch (\Exception $e) {
    echo "❌ Error adding phone column: " . $e->getMessage() . "\n";
}

// Add bio column
echo "\nAdding bio column...\n";
try {
    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'bio')) {
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->text('bio')->nullable()->after('phone');
        });
        echo "✅ Bio column added successfully\n";
    } else {
        echo "✅ Bio column already exists\n";
    }
} catch (\Exception $e) {
    echo "❌ Error adding bio column: " . $e->getMessage() . "\n";
}

// Verify columns were added
echo "\n=== Verification ===\n";
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    echo "Current users table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    $requiredColumns = ['phone', 'bio'];
    echo "\nChecking required columns:\n";
    foreach ($requiredColumns as $column) {
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', $column)) {
            echo "✅ {$column} column exists\n";
        } else {
            echo "❌ {$column} column missing\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error verifying columns: " . $e->getMessage() . "\n";
}

echo "\n=== Complete ===\n";
echo "Profile columns have been added to the database.\n";
echo "Edit Profile functionality should now work properly!\n";
