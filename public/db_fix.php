<?php
// Standalone database fix script

// Credentials from .env
$host = 'localhost';
$db = 'investian_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    echo "Connecting to database...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "Creating app_versions table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS app_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version_name VARCHAR(50) NOT NULL,
        version_code INT NOT NULL,
        apk_file_path VARCHAR(255) NOT NULL,
        file_size BIGINT,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expiry_date TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        download_count INT DEFAULT 0,
        uploaded_by INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Table 'app_versions' created successfully!\n";

    // Create upload directory
    $uploadDir = __DIR__ . '/uploads/apk';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "Upload directory created.\n";
    }

} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
