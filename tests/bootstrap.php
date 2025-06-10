<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create the database directory if it doesn't exist
if (!is_dir(__DIR__ . '/../database')) {
    mkdir(__DIR__ . '/../database');
}

// Create the testing.sqlite file if it doesn't exist
if (!file_exists(__DIR__ . '/../database/testing.sqlite')) {
    touch(__DIR__ . '/../database/testing.sqlite');
    // Ensure the file has the correct permissions
    chmod(__DIR__ . '/../database/testing.sqlite', 0666);
}

// Create the database schema
$db = new PDO('sqlite:' . __DIR__ . '/../database/testing.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db = null;

// Ensure the Laravel application is properly configured for testing
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Run migrations on the testing database
$command = 'php ' . __DIR__ . '/../artisan migrate:fresh --env=testing --database=sqlite --force';
$output = [];
$return_var = 0;
exec($command, $output, $return_var);

if ($return_var !== 0) {
    echo "Error running migrations. Command output:\n";
    echo implode("\n", $output);
    exit(1);
}

echo "Testing database setup complete.\n";
