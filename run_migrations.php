<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers.php';

use App\Core\Model;

// Load environment manually if needed
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
        $_ENV[trim($name)] = trim($value);
    }
}

try {
    $db = Model::connect();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting schema updates...\n";

    // 1. Modify users table to add status column if it doesn't exist
    echo "Checking 'users' table status column...\n";
    $stmt = $db->query("DESCRIBE users");
    $userCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('status', $userCols)) {
        echo "Adding 'status' to 'users' table...\n";
        $db->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'suspended') DEFAULT 'active' AFTER referred_by");
        echo "✅ status column added to users.\n";
    } else {
        echo "users.status already exists.\n";
    }

    // 2. Modify investment_plans status enum
    echo "Checking 'investment_plans' table status enum...\n";
    $db->exec("ALTER TABLE investment_plans MODIFY COLUMN status ENUM('active', 'inactive', 'deleted') DEFAULT 'active'");
    echo "✅ investment_plans status column updated to support 'deleted'.\n";

    // 3. Modify ads table to add view_count and created_by if they don't exist
    echo "Checking 'ads' table columns...\n";
    $stmt = $db->query("DESCRIBE ads");
    $adCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('view_count', $adCols)) {
        echo "Adding 'view_count' to 'ads' table...\n";
        $db->exec("ALTER TABLE ads ADD COLUMN view_count INT DEFAULT 0 AFTER status");
        echo "✅ view_count column added to ads.\n";
    }
    if (!in_array('created_by', $adCols)) {
        echo "Adding 'created_by' to 'ads' table...\n";
        $db->exec("ALTER TABLE ads ADD COLUMN created_by INT AFTER view_count");
        try {
            $db->exec("ALTER TABLE ads ADD CONSTRAINT fk_ads_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
            echo "✅ created_by column and foreign key added to ads.\n";
        } catch (Exception $ex) {
            echo "Warning adding foreign key: " . $ex->getMessage() . "\n";
        }
    }

    // 4. Modify investments table columns (started_at -> start_date, ends_at -> end_date, daily_profit, next_payout, ads_per_day)
    echo "Checking 'investments' table columns...\n";
    $stmt = $db->query("DESCRIBE investments");
    $investCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('started_at', $investCols) && !in_array('start_date', $investCols)) {
        echo "Renaming started_at to start_date...\n";
        $db->exec("ALTER TABLE investments CHANGE COLUMN started_at start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✅ start_date renamed.\n";
    }
    if (in_array('ends_at', $investCols) && !in_array('end_date', $investCols)) {
        echo "Renaming ends_at to end_date...\n";
        $db->exec("ALTER TABLE investments CHANGE COLUMN ends_at end_date TIMESTAMP NULL");
        echo "✅ end_date renamed.\n";
    }
    
    $stmt = $db->query("DESCRIBE investments");
    $investCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('daily_profit', $investCols)) {
        echo "Adding 'daily_profit' to 'investments' table...\n";
        $db->exec("ALTER TABLE investments ADD COLUMN daily_profit DECIMAL(15, 2) DEFAULT 0.00 AFTER amount");
        echo "✅ daily_profit added.\n";
    }
    if (!in_array('next_payout', $investCols)) {
        echo "Adding 'next_payout' to 'investments' table...\n";
        $db->exec("ALTER TABLE investments ADD COLUMN next_payout TIMESTAMP NULL AFTER last_payout_at");
        echo "✅ next_payout added.\n";
    }
    if (!in_array('ads_per_day', $investCols)) {
        echo "Adding 'ads_per_day' to 'investments' table...\n";
        $db->exec("ALTER TABLE investments ADD COLUMN ads_per_day INT DEFAULT 5 AFTER end_date");
        echo "✅ ads_per_day added.\n";
    }

    echo "Populating existing investments data...\n";
    $db->exec("
        UPDATE investments i 
        JOIN investment_plans p ON i.plan_id = p.id 
        SET i.daily_profit = COALESCE(NULLIF(p.daily_profit_amount, 0), (i.amount * p.daily_profit_percent) / 100), 
            i.ads_per_day = COALESCE(p.ads_per_day, 5)
    ");
    echo "✅ Existing investments updated.\n";

    // 5. Modify transactions table type enum
    echo "Checking 'transactions' table type column...\n";
    $db->exec("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'investment', 'payout', 'referral', 'commission') NOT NULL");
    echo "✅ transactions.type enum updated.\n";

    // 6. Check and create other tables if not exists
    echo "Checking other tables...\n";
    $stmt = $db->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('community_messages', $allTables)) {
        echo "Creating table 'community_messages'...\n";
        $db->exec("
            CREATE TABLE community_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                message TEXT NOT NULL,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        echo "✅ community_messages created.\n";
    }

    if (!in_array('guides', $allTables)) {
        echo "Creating table 'guides'...\n";
        $db->exec("
            CREATE TABLE guides (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                content TEXT NOT NULL,
                image_url VARCHAR(255),
                order_index INT DEFAULT 0,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "✅ guides created.\n";
    }

    if (!in_array('guide_steps', $allTables)) {
        echo "Creating table 'guide_steps'...\n";
        $db->exec("
            CREATE TABLE guide_steps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guide_id INT NOT NULL,
                title VARCHAR(255),
                content TEXT NOT NULL,
                media_url VARCHAR(255),
                media_type ENUM('image', 'video', 'none') DEFAULT 'none',
                order_index INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (guide_id) REFERENCES guides(id) ON DELETE CASCADE
            )
        ");
        echo "✅ guide_steps created.\n";
    }

    if (!in_array('app_versions', $allTables)) {
        echo "Creating table 'app_versions'...\n";
        $db->exec("
            CREATE TABLE app_versions (
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
            )
        ");
        echo "✅ app_versions created.\n";
    }

    echo "All migrations completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR OCCURRED: " . $e->getMessage() . "\n";
}
