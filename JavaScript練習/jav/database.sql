-- 會員系統資料庫建立腳本
-- 請在 MySQL 中執行此 SQL 腳本

-- 建立資料庫
CREATE DATABASE IF NOT EXISTS member_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE member_system;

-- 建立用戶表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user', 'demo') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    login_count INT DEFAULT 0
);

-- 建立登入日誌表
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    ip VARCHAR(45),
    user_agent TEXT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 插入測試用戶資料
-- 密碼都是經過 password_hash() 加密的

-- admin / 123456
INSERT INTO users (username, password, name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系統管理員', 'admin@example.com', 'admin');

-- user / password  
INSERT INTO users (username, password, name, email, role) VALUES 
('user', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', '一般用戶', 'user@example.com', 'user');

-- demo / demo123
INSERT INTO users (username, password, name, email, role) VALUES 
('demo', '$2y$10$5v4DEJhzRKZBhKmLjKjxsOy7KZYb5JwvDLZ6B9aFl0TjjKJnCXH3q', '演示帳號', 'demo@example.com', 'demo');

-- 顯示建立的表格
SHOW TABLES;

-- 顯示用戶資料
SELECT id, username, name, email, role, status, created_at FROM users;
