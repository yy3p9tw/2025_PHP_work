-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-07-11 07:37:38
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `action_figure_store`
--

-- --------------------------------------------------------

--
-- 資料表結構 `carousel_slides`
--

CREATE TABLE `carousel_slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `slide_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1 COMMENT '狀態：0=停用, 1=啟用',
  `link_url` varchar(500) DEFAULT NULL COMMENT '點擊連結',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `carousel_slides`
--

INSERT INTO `carousel_slides` (`id`, `title`, `description`, `image_url`, `slide_order`, `created_at`, `status`, `link_url`, `updated_at`) VALUES
(1, '最新上市', '騎士王阿尼亞', 'Mv9AUgL2F2Z.jpg', 0, '2025-07-02 03:10:34', 1, '#featured-products', '2025-07-10 06:35:06'),
(2, '最強熱賣', '', 'Ek_A8sAu_2Z.jpg', 1, '2025-07-02 03:11:58', 1, 'category.html?category_id=8', '2025-07-10 06:35:06');

-- --------------------------------------------------------

--
-- 資料表結構 `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=啟用,0=停用',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `sort_order`, `description`, `status`, `created_at`) VALUES
(1, '動漫公仔', NULL, 1, '各種動漫角色公仔', 1, '2025-07-11 00:46:11'),
(2, '遊戲公仔', NULL, 2, '各種遊戲角色公仔', 0, '2025-07-11 00:46:11'),
(3, '電影公仔', NULL, 3, '各種電影角色公仔', 0, '2025-07-11 00:46:11'),
(4, '原創公仔', NULL, 4, '原創設計公仔', 0, '2025-07-11 00:46:11'),
(11, '火影忍者', 1, 1, '火影忍者系列公仔', 1, '2025-07-11 00:46:11'),
(12, '鬼滅之刃', 1, 2, '鬼滅之刃系列公仔', 1, '2025-07-11 00:46:11'),
(13, '海賊王', 1, 3, '海賊王系列公仔', 0, '2025-07-11 00:46:11'),
(14, '進擊的巨人', 1, 4, '進擊的巨人系列公仔', 0, '2025-07-11 00:46:11'),
(21, '英雄聯盟', 2, 1, '英雄聯盟角色公仔', 0, '2025-07-11 00:46:11'),
(22, '原神', 2, 2, '原神角色公仔', 0, '2025-07-11 00:46:11'),
(23, '最終幻想', 2, 3, '最終幻想系列公仔', 0, '2025-07-11 00:46:11'),
(31, '漫威', 3, 1, '漫威電影公仔', 0, '2025-07-11 00:46:11'),
(32, 'DC', 3, 2, 'DC電影公仔', 0, '2025-07-11 00:46:11'),
(34, '鋼彈模型', NULL, 2, '高品質鋼彈模型系列', 1, '2025-07-11 03:22:23'),
(35, '手辦模型', NULL, 3, '精緻手工製作模型', 1, '2025-07-11 03:22:23'),
(36, '盒玩系列', NULL, 4, '驚喜盒裝公仔', 0, '2025-07-11 03:22:23'),
(37, '一番賞', 1, 1, '一番賞限定公仔', 0, '2025-07-11 03:22:23'),
(38, '景品公仔', 1, 2, '夾娃娃機景品', 1, '2025-07-11 03:22:23'),
(39, 'PVC模型', 1, 3, 'PVC材質精品模型', 1, '2025-07-11 03:22:23'),
(40, 'RG系列', 2, 1, 'Real Grade 系列', 1, '2025-07-11 03:22:23'),
(41, 'HG系列', 2, 2, 'High Grade 系列', 1, '2025-07-11 03:22:23'),
(42, 'MG系列', 2, 3, 'Master Grade 系列', 1, '2025-07-11 03:22:23');

-- --------------------------------------------------------

--
-- 資料表結構 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=上架,0=下架',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `status`, `created_at`) VALUES
(1, '阿尼亞-火影', '', 1000.00, 'product_1.svg', 1, '2025-07-02 03:02:10'),
(2, '阿尼亞-火影', '', 1000.00, 'product_2.svg', 1, '2025-07-02 03:02:54'),
(3, '阿尼亞-鬼滅', '', 1000.00, 'product_3.svg', 1, '2025-07-02 03:03:13'),
(6, '阿尼亞-火影', '', 1000.00, 'product_6.svg', 1, '2025-07-02 03:09:22'),
(7, '阿尼亞-火影', '', 10000.00, 'product_7.svg', 1, '2025-07-02 03:09:32'),
(10, '海賊王 路飛 公仔', '草帽海賊團船長蒙其·D·路飞精品公仔', 1200.00, 'product_10.svg', 1, '2025-07-11 03:22:23'),
(11, '鬼滅之刃 炭治郎 模型', '竈門炭治郎戰鬥姿態模型', 980.00, 'product_11.svg', 1, '2025-07-11 03:22:23'),
(12, '新世紀福音戰士 初號機 RG', 'RG系列 EVA初號機模型', 1500.00, 'product_12.svg', 1, '2025-07-11 03:22:23'),
(13, '獵人 小傑 景品公仔', '小傑·富力士夾娃娃機景品', 650.00, 'product_13.svg', 1, '2025-07-11 03:22:23'),
(14, '自由鋼彈 MG版', 'MG 1/100 ZGMF-X10A Freedom 模型', 2200.00, 'product_14.svg', 1, '2025-07-11 03:22:23'),
(15, '進擊的巨人 兵長 手辦', '里維·阿卡曼精緻手辦模型', 1800.00, 'product_15.svg', 1, '2025-07-11 03:22:23');

-- --------------------------------------------------------

--
-- 資料表結構 `product_category`
--

CREATE TABLE `product_category` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `product_category`
--

INSERT INTO `product_category` (`id`, `product_id`, `category_id`, `created_at`) VALUES
(6, 10, 1, '2025-07-11 03:22:23'),
(7, 10, 38, '2025-07-11 03:22:23'),
(8, 11, 1, '2025-07-11 03:22:23'),
(9, 11, 39, '2025-07-11 03:22:23'),
(10, 12, 34, '2025-07-11 03:22:23'),
(11, 12, 40, '2025-07-11 03:22:23'),
(12, 13, 1, '2025-07-11 03:22:23'),
(13, 13, 38, '2025-07-11 03:22:23'),
(14, 14, 34, '2025-07-11 03:22:23'),
(15, 14, 42, '2025-07-11 03:22:23'),
(16, 15, 1, '2025-07-11 03:22:23'),
(17, 15, 35, '2025-07-11 03:22:23'),
(18, 6, 11, '2025-07-11 03:31:59'),
(19, 7, 11, '2025-07-11 03:32:03'),
(20, 2, 11, '2025-07-11 03:32:12'),
(21, 1, 11, '2025-07-11 03:32:16'),
(22, 3, 12, '2025-07-11 03:33:20');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$dRw8woxkN3vZ6O5tnPrFx.sc99aGQWWFvude.VPhiZnS5RyU0DrmG', 'admin', '2025-07-02 02:54:25');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `carousel_slides`
--
ALTER TABLE `carousel_slides`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- 資料表索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- 資料表索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_status` (`status`);

--
-- 資料表索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- 資料表索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_category_unique` (`product_id`,`category_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `carousel_slides`
--
ALTER TABLE `carousel_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `product_category`
--
ALTER TABLE `product_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- 資料表的限制式 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 資料表的限制式 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
