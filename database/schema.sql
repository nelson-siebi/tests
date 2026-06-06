-- Database Schema for Investian

-- CREATE DATABASE IF NOT EXISTS investian_db;
-- USE investian_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    balance DECIMAL(15, 2) DEFAULT 0.00,
    referral_code VARCHAR(50) UNIQUE,
    referred_by INT,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Investment Plans Table (Managed by Admin)
CREATE TABLE IF NOT EXISTS investment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1611974717424-3684a0006145?auto=format&fit=crop&q=80&w=400',
    price DECIMAL(15, 2) NOT NULL,
    daily_profit_amount DECIMAL(15, 2) NOT NULL,
    daily_profit_percent DECIMAL(10, 2) NOT NULL,
    duration_days INT NOT NULL DEFAULT 30,
    ads_per_day INT DEFAULT 5,
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ads Table
CREATE TABLE IF NOT EXISTS ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    link VARCHAR(255) NOT NULL,
    duration INT DEFAULT 30, -- seconds
    reward DECIMAL(15, 2) DEFAULT 50.00, -- Amount earned per view
    status ENUM('active', 'inactive') DEFAULT 'active',
    view_count INT DEFAULT 0,
    created_by INT, -- Admin who created the ad
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Watched Ads tracking
CREATE TABLE IF NOT EXISTS watched_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id INT NOT NULL,
    watched_at DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (ad_id) REFERENCES ads(id)
);

-- Investments Table (Active investments by users)
CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    daily_profit DECIMAL(15, 2) DEFAULT 0.00,
    total_profit DECIMAL(15, 2) DEFAULT 0.00,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    last_payout_at TIMESTAMP NULL,
    next_payout TIMESTAMP NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    ads_per_day INT DEFAULT 5,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES investment_plans(id)
);

-- Transactions Table (Deposits, Withdrawals, Profits, etc.)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'investment', 'payout', 'referral', 'commission') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    reference VARCHAR(100), -- For deposit/withdrawal tracking
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Community Messages Table
CREATE TABLE IF NOT EXISTS community_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Guides Table
CREATE TABLE IF NOT EXISTS guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    order_index INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Guide Steps Table
CREATE TABLE IF NOT EXISTS guide_steps (
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
);

-- App Versions Table (APK Upload Management)
CREATE TABLE IF NOT EXISTS app_versions (
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
);

-- Default Admin (Password: password123 - hashed)
INSERT INTO users (name, email, password, role, balance, referral_code) VALUES 
('Admin', 'nelsonsiebi237@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00, 'ADMIN123');

-- Sample Plans
INSERT INTO investment_plans (name, description, price, daily_profit_amount, daily_profit_percent, duration_days, ads_per_day) VALUES 
('Starter Plan', 'Investissement de base pour débuter', 4000.00, 200.00, 5.00, 30, 5),
('Gold Plan', 'Plan Premium avec hauts rendements', 10000.00, 600.00, 6.00, 30, 8);

-- Sample Guides
INSERT INTO guides (title, description, content, image_url, order_index) VALUES 
('Comment Investir', 'Apprenez à faire vos premiers pas sur Investian.', 'Pour commencer votre voyage vers la liberté financière, suivez ces étapes simples : \n1. Rechargez votre compte via Mobile Money.\n2. Choisissez un pack d\'investissement.\n3. Regardez vos publicités quotidiennes.', 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600', 1),
('Comment Gagner Plus', 'Optimisez vos revenus avec le parrainage.', 'Le parrainage est le meilleur moyen de booster vos gains sans effort supplémentaire. Partagez votre code unique et recevez une commission immédiate sur chaque dépôt de vos filleuls.', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=600', 2),
('Faire un Retrait', 'Retirez vos profits en quelques minutes.', 'Vos gains sont disponibles à tout moment. Allez dans votre portefeuille, cliquez sur Retrait, entrez le montant et validez. Les fonds arrivent directement sur votre Mobile Money.', 'https://images.unsplash.com/photo-1580519542036-c47de6196ba5?auto=format&fit=crop&q=80&w=600', 3);
