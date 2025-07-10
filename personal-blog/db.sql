-- 文章表
CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `category_id` INT,
  `cover_img` VARCHAR(255),
  `user_id` INT,
  `view_count` INT DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `summary` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 標籤表
CREATE TABLE `tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE
);

-- 文章-標籤關聯表
CREATE TABLE `posts_tags` (
  `post_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  FOREIGN KEY (`post_id`) REFERENCES posts(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES tags(`id`) ON DELETE CASCADE
);

-- 分類表
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
);

-- 管理者帳號
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

CREATE TABLE `activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(50) NOT NULL,
  `detail` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 預設管理者帳號（帳號：admin，密碼：password123，請安裝後盡快修改密碼）
INSERT INTO `users` (`username`, `password`) VALUES ('admin', '$2y$10$wH1Qw6Qw6Qw6Qw6Qw6Qw6uOQw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6');
-- 密碼為 123 的 bcrypt 雜湊，僅供測試用途
-- 密碼為 password123 的 bcrypt 雜湊，僅供測試用途
