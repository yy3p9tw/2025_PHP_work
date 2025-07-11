-- 建立分類相關資料表
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  KEY `sort_order` (`sort_order`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 建立商品分類關聯表
CREATE TABLE IF NOT EXISTS `product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_category_unique` (`product_id`, `category_id`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入範例分類資料
INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `sort_order`, `status`) VALUES
(1, '動漫公仔', '各種動漫角色公仔', NULL, 1, 'active'),
(2, '遊戲公仔', '各種遊戲角色公仔', NULL, 2, 'active'),
(3, '電影公仔', '各種電影角色公仔', NULL, 3, 'active'),
(4, '原創公仔', '原創設計公仔', NULL, 4, 'active'),
(11, '火影忍者', '火影忍者系列公仔', 1, 1, 'active'),
(12, '鬼滅之刃', '鬼滅之刃系列公仔', 1, 2, 'active'),
(13, '海賊王', '海賊王系列公仔', 1, 3, 'active'),
(14, '進擊的巨人', '進擊的巨人系列公仔', 1, 4, 'active'),
(21, '英雄聯盟', '英雄聯盟角色公仔', 2, 1, 'active'),
(22, '原神', '原神角色公仔', 2, 2, 'active'),
(23, '最終幻想', '最終幻想系列公仔', 2, 3, 'active'),
(31, '漫威', '漫威電影公仔', 3, 1, 'active'),
(32, 'DC', 'DC電影公仔', 3, 2, 'active');
