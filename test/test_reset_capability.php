<?php
require_once __DIR__ . '/../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Testing multi-statement execution...\n";
    // Using a safe operation (setting a variable) that won't modify data
    $db->exec("SET @test_var = 1; SET @test_var = 2;");
    echo "✅ Multi-statement execution SUCCESSFUL.\n";
} catch (PDOException $e) {
    echo "❌ Multi-statement execution FAILED (PDOException): " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Multi-statement execution FAILED: " . $e->getMessage() . "\n";
}
