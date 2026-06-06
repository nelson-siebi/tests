-- Migration script to update ads table
-- Run this if you already have an existing ads table

ALTER TABLE ads 
ADD COLUMN IF NOT EXISTS reward DECIMAL(15, 2) DEFAULT 50.00 COMMENT 'Amount earned per view',
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active',
ADD COLUMN IF NOT EXISTS view_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS created_by INT,
ADD CONSTRAINT fk_ads_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Update existing ads to have default reward if NULL
UPDATE ads SET reward = 50.00 WHERE reward IS NULL OR reward = 0;
