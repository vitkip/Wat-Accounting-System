-- ============================================================================
-- ລະບົບບັນຊີວັດ (Wat Accounting System) - Version 2.0.0
-- Database Schema for Multi-Temple System
-- Author: Ananthasak Phommasone & GitHub Copilot
-- Last Updated: 2025-10-15
-- ============================================================================

-- This script creates the complete database schema from scratch, 
-- including all tables, relationships, views, and initial data for the multi-temple system.

CREATE DATABASE IF NOT EXISTS wat_accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wat_accounting;

-- ----------------------------------------------------------------------------
-- Table structure for `temples`
-- (Stores information about each temple)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS temples (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'ລະຫັດວັດ ເຊັ່ນ: WAT001',
    temple_name VARCHAR(200) NOT NULL COMMENT 'ຊື່ວັດ',
    temple_name_lao VARCHAR(200) NOT NULL COMMENT 'ຊື່ວັດພາສາລາວ',
    abbot_name VARCHAR(100) COMMENT 'ຊື່ເຈົ້າອະທິການ',
    address TEXT COMMENT 'ທີ່ຢູ່',
    district VARCHAR(100) COMMENT 'ເມືອງ',
    province VARCHAR(100) COMMENT 'ແຂວງ',
    phone VARCHAR(20) COMMENT 'ເບີໂທ',
    email VARCHAR(100) COMMENT 'ອີເມລ',
    logo_url VARCHAR(255) COMMENT 'URL ຮູບໂລໂກ້ວັດ',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'ສະຖານະ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_temple_code (temple_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `users`
-- (Stores user accounts, roles, and their assigned temple)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT DEFAULT NULL COMMENT 'NULL for Super Admin, otherwise links to a temple',
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_super_admin BOOLEAN DEFAULT FALSE COMMENT 'TRUE for Super Admin who can manage all temples',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE RESTRICT,
    INDEX idx_username (username),
    INDEX idx_temple_id (temple_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `income_categories` & `expense_categories`
-- (Stores categories for income/expense. NULL temple_id means it's a global category)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS income_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT DEFAULT NULL COMMENT 'NULL = Global category for all temples',
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE CASCADE,
    UNIQUE INDEX unique_name_per_temple (name, temple_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT DEFAULT NULL COMMENT 'NULL = Global category for all temples',
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE CASCADE,
    UNIQUE INDEX unique_name_per_temple (name, temple_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `income` & `expense`
-- (Stores financial transaction records, linked to a specific temple)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT NOT NULL,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category_id INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES income_categories(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_temple_id (temple_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expense (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT NOT NULL,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category_id INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_temple_id (temple_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `audit_log`
-- (Tracks all major changes in the system)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `temple_settings`
-- (Stores specific settings for each temple)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS temple_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    temple_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (temple_id) REFERENCES temples(id) ON DELETE CASCADE,
    UNIQUE INDEX unique_setting_per_temple (temple_id, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- Initial Data Insertion
-- ============================================================================

-- 1. Create sample temples
INSERT INTO temples (id, temple_code, temple_name, temple_name_lao, abbot_name, address, district, province, phone, status) VALUES
(1, 'WAT001', 'Wat Pa Nongboua Tongtai', 'ວັດປ່າໜອງບົວທອງໃຕ້', 'ພະອາຈານສະໝານ', 'ບ້ານໜອງບົວທອງໃຕ້', 'ໄຊເສດຖາ', 'ນະຄອນຫຼວງວຽງຈັນ', '020 5555 1111', 'active'),
(2, 'WAT002', 'Wat Sisaket', 'ວັດສີສະເກດ', 'ພະອາຈານສົມສະຫວັດ', 'ບ້ານສີສະເກດ', 'ຈັນທະບູລີ', 'ນະຄອນຫຼວງວຽງຈັນ', '020 5555 2222', 'active'),
(3, 'WAT003', 'Wat Phou', 'ວັດພູ', 'ພະອາຈານບຸນມີ', 'ບ້ານວັດພູ', 'ປາກເຊ', 'ຈຳປາສັກ', '020 5555 3333', 'active')
ON DUPLICATE KEY UPDATE temple_name=temple_name;

-- 2. Create Super Admin user (password: admin123)
INSERT INTO users (id, temple_id, username, password, full_name, role, is_super_admin) VALUES
(1, NULL, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 'admin', TRUE)
ON DUPLICATE KEY UPDATE is_super_admin=TRUE, password='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 3. Create Admin user for the first temple (password: admin123)
INSERT INTO users (id, temple_id, username, password, full_name, role, is_super_admin) VALUES
(2, 1, 'admin_wat1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin for Wat Nongboua', 'admin', FALSE)
ON DUPLICATE KEY UPDATE temple_id=1, password='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 4. Create global categories (available to all temples)
INSERT INTO income_categories (temple_id, name, description) VALUES
(NULL, 'ເງິນບໍລິຈາກ', 'ເງິນບໍລິຈາກຈາກພະສົງ ແລະ ປະຊາຊົນ'),
(NULL, 'ເງິນຄ່າບໍລິການ', 'ຄ່າບໍລິການຕ່າງໆ ຂອງວັດ'),
(NULL, 'ລາຍຮັບອື່ນໆ', 'ລາຍຮັບອື່ນໆ ທີ່ບໍ່ໄດ້ລະບຸ')
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO expense_categories (temple_id, name, description) VALUES
(NULL, 'ຄ່າໄຟຟ້າ-ນ້ຳປະປາ', 'ຄ່າໄຟຟ້າ ນ້ຳປະປາ ປະຈຳເດືອນ'),
(NULL, 'ຄ່າອາຫານ', 'ຄ່າອາຫານສຳລັບພະສົງ'),
(NULL, 'ຄ່າສ້ອມແປງ', 'ຄ່າສ້ອມແປງອາຄານ ສິ່ງກໍ່ສ້າງ'),
(NULL, 'ລາຍຈ່າຍອື່ນໆ', 'ລາຍຈ່າຍອື່ນໆ ທີ່ບໍ່ໄດ້ລະບຸ')
ON DUPLICATE KEY UPDATE name=name;

-- 5. Insert sample transactions for the first temple
INSERT INTO income (temple_id, date, description, amount, category_id, created_by) VALUES
(1, '2025-10-01', 'ເງິນບໍລິຈາກກອງບຸນ', 5000000, 1, 2),
(1, '2025-10-05', 'ເງິນບໍລິຈາກງານບຸນປະຈຳປີ', 3500000, 1, 2),
(1, '2025-10-10', 'ຄ່າບໍລິການຈັດງານ', 2000000, 2, 2);

INSERT INTO expense (temple_id, date, description, amount, category_id, created_by) VALUES
(1, '2025-10-02', 'ຄ່າໄຟຟ້າປະຈຳເດືອນ 9', 800000, 4, 2),
(1, '2025-10-03', 'ຄ່າອາຫານສຳລັບພະສົງ-ສຳມະເນນ', 1500000, 5, 2),
(1, '2025-10-08', 'ຄ່າສ້ອມແປງຫຼັງຄາໂບດ', 2500000, 6, 2);

-- 6. Insert default settings for the first temple
INSERT INTO temple_settings (temple_id, setting_key, setting_value) VALUES
(1, 'fiscal_year_start', '10'),
(1, 'currency_symbol', '₭'),
(1, 'date_format', 'd/m/Y'),
(1, 'timezone', 'Asia/Vientiane')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);


-- ============================================================================
-- Views and Stored Procedures
-- ============================================================================

-- ----------------------------------------------------------------------------
-- View `temple_statistics`
-- (Provides a summary of key metrics for each temple)
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW temple_statistics AS
SELECT 
    t.id as temple_id,
    t.temple_code,
    t.temple_name_lao,
    COALESCE((SELECT SUM(amount) FROM income WHERE temple_id = t.id), 0) as total_income,
    COALESCE((SELECT SUM(amount) FROM expense WHERE temple_id = t.id), 0) as total_expense,
    COALESCE((SELECT SUM(amount) FROM income WHERE temple_id = t.id), 0) - COALESCE((SELECT SUM(amount) FROM expense WHERE temple_id = t.id), 0) as balance,
    (SELECT COUNT(*) FROM users WHERE temple_id = t.id) as total_users,
    t.status
FROM temples t;

-- ----------------------------------------------------------------------------
-- Stored Procedure `create_new_temple`
-- (Automates the process of creating a new temple and its admin user)
-- ----------------------------------------------------------------------------
DELIMITER //

CREATE OR REPLACE PROCEDURE create_new_temple(
    IN p_temple_code VARCHAR(20),
    IN p_temple_name VARCHAR(200),
    IN p_temple_name_lao VARCHAR(200),
    IN p_abbot_name VARCHAR(100),
    IN p_province VARCHAR(100),
    IN p_admin_username VARCHAR(50),
    IN p_admin_password VARCHAR(255),
    IN p_admin_fullname VARCHAR(100)
)
BEGIN
    DECLARE new_temple_id INT;
    DECLARE hashed_password VARCHAR(255);
    
    -- It's better to hash password in the application layer, 
    -- but for this procedure, we'll assume it's pre-hashed.
    SET hashed_password = p_admin_password;
    
    -- Start Transaction
    START TRANSACTION;
    
    -- 1. Create the temple
    INSERT INTO temples (temple_code, temple_name, temple_name_lao, abbot_name, province, status)
    VALUES (p_temple_code, p_temple_name, p_temple_name_lao, p_abbot_name, p_province, 'active');
    
    SET new_temple_id = LAST_INSERT_ID();
    
    -- 2. Create the admin user for that temple
    INSERT INTO users (temple_id, username, password, full_name, role, is_super_admin)
    VALUES (new_temple_id, p_admin_username, hashed_password, p_admin_fullname, 'admin', FALSE);
    
    -- 3. Create default settings for the new temple
    INSERT INTO temple_settings (temple_id, setting_key, setting_value)
    VALUES 
        (new_temple_id, 'fiscal_year_start', '10'),
        (new_temple_id, 'currency_symbol', '₭'),
        (new_temple_id, 'date_format', 'd/m/Y'),
        (new_temple_id, 'timezone', 'Asia/Vientiane');
    
    COMMIT;
    
    SELECT new_temple_id as temple_id, 'New temple created successfully' as message;
END //

DELIMITER ;

-- ============================================================================
-- Migration Scripts for Production Compatibility
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Fix: Add 'category' column to income/expense tables
-- (For backwards compatibility with code that uses VARCHAR category field)
-- ----------------------------------------------------------------------------

-- Add category column to income table (if not exists)
ALTER TABLE income
ADD COLUMN IF NOT EXISTS category VARCHAR(100) NULL AFTER amount,
ADD INDEX IF NOT EXISTS idx_income_category (category);

-- Add category column to expense table (if not exists)
ALTER TABLE expense
ADD COLUMN IF NOT EXISTS category VARCHAR(100) NULL AFTER amount,
ADD INDEX IF NOT EXISTS idx_expense_category (category);

-- Update existing records to populate category field from category_id
UPDATE income i
LEFT JOIN income_categories ic ON i.category_id = ic.id
SET i.category = ic.name
WHERE i.category IS NULL OR i.category = '';

UPDATE expense e
LEFT JOIN expense_categories ec ON e.category_id = ec.id
SET e.category = ec.name
WHERE e.category IS NULL OR e.category = '';

-- ----------------------------------------------------------------------------
-- Optional: Add email column to users table
-- (For future use, not required for current system)
-- ----------------------------------------------------------------------------
ALTER TABLE users
ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER full_name,
ADD INDEX IF NOT EXISTS idx_users_email (email);

-- ============================================================================
-- End of script
-- ============================================================================
SELECT 'Database schema created/updated successfully for Multi-Temple System. 🎉' as status;
SELECT 'Migration scripts applied. System ready for production deployment. ✅' as migration_status;
