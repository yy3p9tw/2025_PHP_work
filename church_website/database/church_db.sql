-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-07-02 04:13:07
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
-- 資料庫： `church_db`
--

-- --------------------------------------------------------

--
-- 資料表結構 `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `media_library`
--

CREATE TABLE `media_library` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `published_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `updated_at`) VALUES
(1, 'about', '教會介紹', '<!-- 建議插入於 <body> 主要內容區塊 -->\n<div class=\"mb-5\">\n    <h2 class=\"text-center mb-4\">教會同工</h2>\n    <div class=\"overflow-auto\">\n        <div class=\"d-flex flex-row\" style=\"gap: 1.5rem; min-width: 700px;\">\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_1.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名A\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 A</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責青年事工，熱愛音樂和服事。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_2.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名B\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 B</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責兒童主日學，對教育充滿熱情。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_3.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名C\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 C</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責敬拜團，擅長樂器與帶領敬拜。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_4.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名D\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 D</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責關懷探訪，熱心助人。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_5.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名E\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 E</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責行政協調，細心負責。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_6.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名F\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 F</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責小組牧養，關心弟兄姊妹。</p>\n                </div>\n            </div>\n            <div class=\"card text-center flex-shrink-0\" style=\"width: 260px;\">\n                <img src=\"assets/images/staff_member_7.jpg\" class=\"card-img-top rounded-circle mx-auto mt-3\" alt=\"同工姓名G\" style=\"width: 150px; height: 150px; object-fit: cover;\">\n                <div class=\"card-body\">\n                    <h5 class=\"card-title\">同工姓名 G</h5>\n                    <p class=\"card-text text-muted\">職稱</p>\n                    <p class=\"card-text\" style=\"white-space:normal;\">簡短介紹，例如：負責影音事工，專長多媒體製作。</p>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n<!-- 教會異象卡片化 -->\n<section class=\"mb-5\">\n    <h2 class=\"text-center mb-4\">教會異象</h2>\n    <div class=\"row row-cols-1 row-cols-md-3 g-4 justify-content-center mb-4\">\n        <div class=\"col\">\n            <div class=\"card h-100 text-center shadow-sm border-0\" style=\"background:rgba(255,255,255,0.07);\">\n                <div class=\"card-body\">\n                    <div class=\"fs-5\">願我們成為一個充滿愛、真理與盼望的教會</div>\n                </div>\n            </div>\n        </div>\n        <div class=\"col\">\n            <div class=\"card h-100 text-center shadow-sm border-0\" style=\"background:rgba(255,255,255,0.07);\">\n                <div class=\"card-body\">\n                    <div class=\"fs-5\">以基督為中心，服事社區，傳揚福音，培育門徒</div>\n                </div>\n            </div>\n        </div>\n        <div class=\"col\">\n            <div class=\"card h-100 text-center shadow-sm border-0\" style=\"background:rgba(255,255,255,0.07);\">\n                <div class=\"card-body\">\n                    <div class=\"fs-5\">讓每個人都能經歷神的恩典與改變</div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- 我們的信仰區塊 -->\n<section class=\"mb-5\">\n    <h2 class=\"text-center mb-4\">我們的信仰</h2>\n    <div class=\"row justify-content-center mb-4\">\n        <div class=\"col-md-8\">\n            <ul class=\"list-group list-group-flush fs-5\">\n                <li class=\"list-group-item\">我們相信聖父、聖子、聖靈三位一體的真神。</li>\n                <li class=\"list-group-item\">我們相信聖經是神所默示、信仰與生活最高準則。</li>\n                <li class=\"list-group-item\">我們相信耶穌基督為世人贖罪，死而復活，賜下永生。</li>\n                <li class=\"list-group-item\">我們相信因信稱義，靠恩得救，並領受聖靈新生命。</li>\n                <li class=\"list-group-item\">我們相信教會是基督的身體，蒙召彼此相愛、服事、傳揚福音。</li>\n            </ul>\n        </div>\n    </div>\n</section>', '2025-07-02 01:44:09'),
(2, 'contact', '聯絡資訊', '教會地址：xxx<br>電話：xxx-xxxxxxx<br>Email：xxx@xxx.com', '2025-07-02 02:03:25');

-- --------------------------------------------------------

--
-- 資料表結構 `sermons`
--

CREATE TABLE `sermons` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `speaker` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `audio_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'password', 'admin', '2025-06-30 08:21:15', '2025-06-30 08:21:15');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `media_library`
--
ALTER TABLE `media_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- 資料表索引 `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- 資料表索引 `sermons`
--
ALTER TABLE `sermons`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

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
-- 使用資料表自動遞增(AUTO_INCREMENT) `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `media_library`
--
ALTER TABLE `media_library`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sermons`
--
ALTER TABLE `sermons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `media_library`
--
ALTER TABLE `media_library`
  ADD CONSTRAINT `media_library_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
