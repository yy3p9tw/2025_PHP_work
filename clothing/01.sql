-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- 主機： sql306.infinityfree.com
-- 產生時間： 2025 年 06 月 27 日 04:18
-- 伺服器版本： 11.4.7-MariaDB
-- PHP 版本： 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `if0_39295983_store`
--

-- --------------------------------------------------------

--
-- 資料表結構 `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(4, '背心', '2025-06-22 15:42:08'),
(6, '上衣', '2025-06-23 06:53:30'),
(8, '長褲', '2025-06-23 06:53:56'),
(9, '短褲', '2025-06-23 06:54:13'),
(10, '內褲', '2025-06-23 06:54:27'),
(11, '細肩美背', '2025-06-24 14:51:36'),
(12, '寬肩美背', '2025-06-25 08:34:27');

-- --------------------------------------------------------

--
-- 資料表結構 `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `colors`
--

INSERT INTO `colors` (`id`, `name`, `created_at`) VALUES
(15, '深藍', '2025-06-23 15:31:56'),
(16, '淺藍', '2025-06-23 15:32:00'),
(17, '淺綠', '2025-06-23 15:32:11'),
(18, '深綠', '2025-06-23 15:32:15'),
(19, '湖水綠', '2025-06-23 15:32:23'),
(20, '寶寶藍', '2025-06-23 15:32:27'),
(21, '粉紅', '2025-06-23 15:32:36'),
(22, '杏色', '2025-06-23 15:32:40'),
(23, '黃色', '2025-06-23 15:32:50'),
(24, '黑色', '2025-06-23 15:32:58'),
(25, '咖色', '2025-06-23 15:33:05'),
(26, '卡色', '2025-06-23 15:33:09'),
(27, '奶色', '2025-06-23 15:33:31'),
(28, '橘色', '2025-06-23 15:33:37'),
(29, '橘粉', '2025-06-23 15:33:42'),
(30, '深灰', '2025-06-23 15:33:55'),
(31, '淺灰', '2025-06-23 15:34:08'),
(32, '鐵色', '2025-06-23 15:34:13'),
(33, '紫色', '2025-06-23 15:34:34'),
(34, '磚色', '2025-06-23 15:34:58'),
(35, '鵝黃色', '2025-06-23 15:35:40'),
(36, '黃色', '2025-06-23 15:35:45'),
(37, '紅色', '2025-06-23 15:36:00'),
(38, '白色', '2025-06-23 15:36:15'),
(39, '軍綠色', '2025-06-24 14:28:23'),
(40, '綠色', '2025-06-24 15:04:28'),
(41, '霧藍', '2025-06-25 08:35:28'),
(43, '桃色', '2025-06-25 08:35:53'),
(44, '寶藍色', '2025-06-25 08:36:16'),
(45, '紫色', '2025-06-25 08:36:23'),
(46, '焦糖色', '2025-06-25 08:36:34'),
(47, '茶色', '2025-06-25 08:36:46'),
(48, '灰色', '2025-06-25 08:37:11');

-- --------------------------------------------------------

--
-- 資料表結構 `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `notes`) VALUES
(11, '布丁', '0987386322', '', '7-11 成旺店', '');

-- --------------------------------------------------------

--
-- 資料表結構 `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `items`
--

INSERT INTO `items` (`id`, `name`, `category_id`, `color_id`, `cost_price`, `sell_price`, `stock`, `min_stock`, `image`, `description`, `created_at`, `updated_at`) VALUES
(22, '雙色蝴蝶結美背', 4, 0, '0.00', '0.00', 0, 5, 'img_685976b78478e4.32503325.jpeg', '', '2025-06-23 15:45:59', '2025-06-24 15:07:27'),
(23, '涼感冰絲7分闊腿褲裙', 9, 0, '0.00', '0.00', 0, 5, 'img_685ab5f9c3ff48.24665822.jpeg', '黑/咖/綠/磚/杏	250\r\n腰32-50 臀61-72 長68', '2025-06-24 14:28:09', '2025-06-24 14:30:37');

-- --------------------------------------------------------

--
-- 資料表結構 `item_variants`
--

CREATE TABLE `item_variants` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `min_stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `item_variants`
--

INSERT INTO `item_variants` (`id`, `item_id`, `color_id`, `cost_price`, `sell_price`, `stock`, `min_stock`) VALUES
(13, 14, 5, '123123.00', '123.00', 5, 5),
(14, 14, 4, '119.00', '46645.00', 4746, 5),
(15, 14, 6, '4545.00', '645.00', 46546, 5),
(16, 15, 5, '10012.00', '123213.00', 1000, 1000),
(17, 15, 4, '12312.00', '12323.00', 1233123, 1000),
(18, 14, 7, '46456.00', '546456.00', 546456, 5),
(19, 16, 5, '1000.00', '10000.00', 210, 5),
(20, 17, 5, '123.00', '123.00', 12312, 5),
(21, 18, 12, '10.00', '10.00', 10, 5),
(22, 19, 12, '101010.00', '1010.00', 0, 105),
(23, 20, 12, '11.00', '11.00', 11, 115),
(24, 21, 12, '10.00', '10.00', 10, 5),
(25, 22, 22, '170.00', '290.00', -37, 5),
(26, 22, 24, '170.00', '290.00', -9, 5),
(27, 22, 38, '170.00', '290.00', -9, 5),
(28, 23, 22, '250.00', '349.00', 4, 1),
(29, 23, 25, '250.00', '349.00', -8, 1),
(30, 23, 34, '250.00', '349.00', -9, 0),
(31, 23, 40, '250.00', '349.00', -8, 5),
(32, 22, 23, '170.00', '290.00', 1, 5),
(33, 22, 37, '170.00', '290.00', -9, 5),
(34, 22, 15, '170.00', '290.00', 2, 5),
(35, 22, 31, '170.00', '290.00', -8, 5),
(36, 23, 24, '250.00', '349.00', 6, 5),
(37, 24, 31, '10.00', '100.00', 100, 5),
(38, 25, 31, '10.00', '100.00', 100, 5),
(39, 25, 31, '10.00', '100.00', 100, 5),
(40, 26, 30, '10.00', '100.00', 100, 5);

-- --------------------------------------------------------

--
-- 資料表結構 `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `sale_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `spec_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- 資料表索引 `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `item_variants`
--
ALTER TABLE `item_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `color_id` (`color_id`);

--
-- 資料表索引 `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `item_variants`
--
ALTER TABLE `item_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
