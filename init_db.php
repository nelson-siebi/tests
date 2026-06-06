<?php
/**
 * Investian Database Initialization Script
 * Use this script to set up your database tables and default data.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Try to load environment if possible
if (file_exists(__DIR__ . '/.env')) {
    // Basic .env loader if Env class isn't fully ready in this context
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

use App\Core\Model;

try {
    echo "Initializing database...\n";
    $db = Model::connect();

    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Error: schema.sql not found at $schemaFile\n");
    }

    $sql = file_get_contents($schemaFile);

    // Split SQL by semicolons, but be careful with functions/triggers if any
    // For this schema, simple split is fine.
    $queries = explode(';', $sql);

    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query))
            continue;

        try {
            $db->exec($query);
            $count++;
        } catch (PDOException $e) {
            // Ignore "Table already exists" or similar if you want to be able to run it multiple times
            // But usually for init, we want to know errors.
            echo "Warning in query: " . substr($query, 0, 50) . "... -> " . $e->getMessage() . "\n";
        }
    }

    echo "Successfully executed $count queries.\n";
    echo "Database is ready!\n";
    echo "Default Admin: admin@investian.com / password123\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
