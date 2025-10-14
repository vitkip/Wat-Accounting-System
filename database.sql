-- ລະບົບບັນຊີວັດ (Wat Accounting System)
-- Database Schema

CREATE DATABASE IF NOT EXISTS wat_accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wat_accounting;

-- ຕາຕະລາງຜູ້ໃຊ້
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຕາຕະລາງລາຍຮັບ
CREATE TABLE income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'ທົ່ວໄປ',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຕາຕະລາງລາຍຈ່າຍ
CREATE TABLE expense (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'ທົ່ວໄປ',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຕາຕະລາງບັນທຶກການເຄື່ອນໄຫວ (Audit Log)
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຕາຕະລາງໝວດໝູ່ລາຍຮັບ
CREATE TABLE income_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຕາຕະລາງໝວດໝູ່ລາຍຈ່າຍ
CREATE TABLE expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ຂໍ້ມູນຕົ້ນແບບ

-- ສ້າງຜູ້ໃຊ້ແອດມິນ (username: admin, password: admin123)
-- ສ້າງຜູ້ໃຊ້ທົ່ວໄປ (username: user1, password: admin123)
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$YourPasswordHashWillBeGeneratedDynamically', 'ຜູ້ດູແລລະບົບ', 'admin'),
('user1', '$2y$10$YourPasswordHashWillBeGeneratedDynamically', 'ຜູ້ໃຊ້ທົ່ວໄປ', 'user')
ON DUPLICATE KEY UPDATE password=password;

-- ໝວດໝູ່ລາຍຮັບ
INSERT INTO income_categories (name, description) VALUES
('ເງິນບໍລິຈາກ', 'ເງິນບໍລິຈາກຈາກພະສົງ ແລະ ປະຊາຊົນ'),
('ເງິນຄ່າບໍລິການ', 'ຄ່າບໍລິການຕ່າງໆ ຂອງວັດ'),
('ລາຍຮັບອື່ນໆ', 'ລາຍຮັບອື່ນໆ ທີ່ບໍ່ໄດ້ລະບຸ'),
('ທົ່ວໄປ', 'ລາຍຮັບທົ່ວໄປ');

-- ໝວດໝູ່ລາຍຈ່າຍ
INSERT INTO expense_categories (name, description) VALUES
('ຄ່າໄຟຟ້າ-ນ້ຳປະປາ', 'ຄ່າໄຟຟ້າ ນ້ຳປະປາ ປະຈຳເດືອນ'),
('ຄ່າອາຫານ', 'ຄ່າອາຫານສຳລັບພະສົງ'),
('ຄ່າສ້ອມແປງ', 'ຄ່າສ້ອມແປງອາຄານ ສິ່ງກໍ່ສ້າງ'),
('ຄ່າວັດຖຸສະຫນັບສະຫນູນ', 'ວັດຖຸສະຫນັບສະຫນູນຕ່າງໆ'),
('ລາຍຈ່າຍອື່ນໆ', 'ລາຍຈ່າຍອື່ນໆ ທີ່ບໍ່ໄດ້ລະບຸ'),
('ທົ່ວໄປ', 'ລາຍຈ່າຍທົ່ວໄປ');

-- ຂໍ້ມູນທົດສອບ (ຕົວຢ່າງ)
INSERT INTO income (date, description, amount, category, created_by) VALUES
('2025-10-01', 'ເງິນບໍລິຈາກກົດສະຫນາ', 5000000, 'ເງິນບໍລິຈາກ', 1),
('2025-10-05', 'ເງິນບໍລິຈາກງານບຸນ', 3500000, 'ເງິນບໍລິຈາກ', 1),
('2025-10-10', 'ຄ່າບໍລິການງານບຸນ', 2000000, 'ເງິນຄ່າບໍລິການ', 1);

INSERT INTO expense (date, description, amount, category, created_by) VALUES
('2025-10-02', 'ຄ່າໄຟຟ້າປະຈຳເດືອນ', 800000, 'ຄ່າໄຟຟ້າ-ນ້ຳປະປາ', 1),
('2025-10-03', 'ຄ່າອາຫານພະສົງ', 1500000, 'ຄ່າອາຫານ', 1),
('2025-10-08', 'ຄ່າສ້ອມແປງຫຼັງຄາ', 2500000, 'ຄ່າສ້ອມແປງ', 1);
