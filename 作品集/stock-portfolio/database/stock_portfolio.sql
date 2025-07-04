-- 股票投資組合系統資料庫結構
-- 創建時間: 2025-07-04

-- 創建資料庫
CREATE DATABASE IF NOT EXISTS stock_portfolio 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE stock_portfolio;

-- 用戶表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- 股票基本資料表
CREATE TABLE stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    industry VARCHAR(50) DEFAULT NULL,
    current_price DECIMAL(10,4) DEFAULT 0,
    price_change DECIMAL(10,4) DEFAULT 0,
    change_percent DECIMAL(8,4) DEFAULT 0,
    volume BIGINT DEFAULT 0,
    market_cap BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- 股票價格歷史記錄表
CREATE TABLE stock_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_code VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    open_price DECIMAL(10,4) NOT NULL,
    high_price DECIMAL(10,4) NOT NULL,
    low_price DECIMAL(10,4) NOT NULL,
    close_price DECIMAL(10,4) NOT NULL,
    volume BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_stock_date (stock_code, date),
    INDEX idx_stock_date (stock_code, date)
);

-- 用戶投資組合表
CREATE TABLE portfolios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_code VARCHAR(20) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    avg_price DECIMAL(10,4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_stock (user_id, stock_code),
    INDEX idx_user_stock (user_id, stock_code)
);

-- 交易記錄表
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_code VARCHAR(20) NOT NULL,
    type ENUM('buy', 'sell') NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,4) NOT NULL,
    total_amount DECIMAL(15,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at),
    INDEX idx_stock_date (stock_code, created_at)
);

-- 關注清單表
CREATE TABLE watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_stock (user_id, stock_code),
    INDEX idx_user_stock (user_id, stock_code)
);

-- 市場指數表
CREATE TABLE market_indices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    current_value DECIMAL(10,4) DEFAULT 0,
    change_value DECIMAL(10,4) DEFAULT 0,
    change_percent DECIMAL(8,4) DEFAULT 0,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 市場指數歷史記錄表
CREATE TABLE market_index_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    index_code VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    open_value DECIMAL(10,4) NOT NULL,
    high_value DECIMAL(10,4) NOT NULL,
    low_value DECIMAL(10,4) NOT NULL,
    close_value DECIMAL(10,4) NOT NULL,
    volume BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_index_date (index_code, date),
    INDEX idx_index_date (index_code, date)
);

-- 新聞表
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    summary TEXT,
    source VARCHAR(100) DEFAULT NULL,
    url VARCHAR(500) DEFAULT NULL,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- 系統設定表
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 爬蟲記錄表
CREATE TABLE crawler_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    status ENUM('success', 'failed', 'running') NOT NULL,
    message TEXT,
    data_count INT DEFAULT 0,
    execution_time DECIMAL(8,3) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 插入預設管理員用戶
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@stockportfolio.com', '$2y$10$zTejUBReXBt69.Y86B9zn.IL2CAOhYuDehJJCYMqF3Tz7xffCW8Wi', 'admin'),
('demo', 'demo@stockportfolio.com', '$2y$10$OHBaaamtz54/qpB0Y2szQOVINOiw/4Ls.TUQmtp9IbiRHZftn0ACS', 'user');
-- 預設密碼：admin 用戶密碼是 admin123，demo 用戶密碼是 demo123

-- 插入預設股票資料
INSERT INTO stocks (code, name, industry, current_price, price_change, change_percent, volume, market_cap) VALUES 
('2330', '台積電', '半導體', 485.50, 12.50, 2.64, 1250000, 12500000000),
('2317', '鴻海', '電子製造', 95.80, -1.20, -1.23, 850000, 2500000000),
('2454', '聯發科', '半導體', 680.00, 21.00, 3.18, 650000, 8500000000),
('0050', '元大台灣50', 'ETF', 125.45, 1.25, 1.01, 450000, 1500000000),
('AAPL', '蘋果公司', '科技', 150.25, 2.75, 1.86, 2500000, 2500000000000),
('GOOGL', '谷歌', '科技', 2350.75, -21.25, -0.89, 180000, 1600000000000),
('MSFT', '微軟', '科技', 310.45, 5.85, 1.92, 890000, 2300000000000),
('TSLA', '特斯拉', '汽車', 245.67, 8.45, 3.56, 1150000, 780000000000);

-- 插入預設股票價格歷史
INSERT INTO stock_prices (stock_code, date, open_price, high_price, low_price, close_price, volume) VALUES 
('2330', '2025-07-04', 475.00, 490.00, 470.00, 485.50, 1250000),
('2317', '2025-07-04', 97.00, 98.50, 94.50, 95.80, 850000),
('2454', '2025-07-04', 665.00, 685.00, 660.00, 680.00, 650000),
('0050', '2025-07-04', 124.00, 126.00, 123.50, 125.45, 450000),
('AAPL', '2025-07-04', 148.00, 152.00, 147.50, 150.25, 2500000),
('GOOGL', '2025-07-04', 2370.00, 2380.00, 2340.00, 2350.75, 180000),
('MSFT', '2025-07-04', 305.00, 315.00, 302.00, 310.45, 890000),
('TSLA', '2025-07-04', 238.00, 248.00, 235.00, 245.67, 1150000);

-- 插入預設市場指數
INSERT INTO market_indices (code, name, current_value, change_value, change_percent, display_order) VALUES 
('TAIEX', '台灣加權指數', 17250.48, 125.30, 0.73, 1),
('TPEx', '櫃買指數', 185.67, 2.45, 1.34, 2),
('ELECTRONIC', '電子指數', 850.23, 12.80, 1.53, 3),
('FINANCE', '金融指數', 1650.45, -8.20, -0.49, 4),
('SEMICONDUCTOR', '半導體指數', 580.78, 15.60, 2.76, 5),
('BIOTECH', '生技指數', 320.12, -4.30, -1.33, 6);

-- 插入預設新聞
-- 插入預設新聞
INSERT INTO news (title, content, source, url) VALUES 
('台積電Q4財報超預期，股價創新高', '台積電公布第四季財報，營收和獲利均超越市場預期，推動股價創下歷史新高。', '財經日報', 'https://example.com/news/1'),
('美股三大指數收漲，科技股表現亮眼', '美股昨夜收盤，道瓊、標普500、納斯達克均收漲，科技股領漲市場。', '經濟時報', 'https://example.com/news/2'),
('AI概念股持續熱炒，投資人關注度高', '人工智慧相關概念股持續受到市場關注，相關公司股價表現強勁。', '投資週刊', 'https://example.com/news/3'),
('聯發科新產品發布，市場反應熱烈', '聯發科發布新一代晶片產品，獲得市場正面回應，股價應聲上漲。', '科技新聞', 'https://example.com/news/4'),
('台股外資連續買超，指數站穩萬七', '外資連續多日買超台股，推動加權指數站穩17000點大關。', '市場觀察', 'https://example.com/news/5');

-- 插入系統設定
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('site_name', 'Stock Portfolio', '網站名稱'),
('crawler_interval', '300', '爬蟲執行間隔（秒）'),
('max_portfolio_items', '50', '每個用戶最大持股數'),
('api_timeout', '30', 'API請求超時時間（秒）'),
('enable_notifications', '1', '是否啟用通知'),
('maintenance_mode', '0', '維護模式');

-- 創建索引以提升查詢效能
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_stocks_code ON stocks(code);
CREATE INDEX idx_stocks_name ON stocks(name);
CREATE INDEX idx_portfolios_user ON portfolios(user_id);
CREATE INDEX idx_portfolios_stock ON portfolios(stock_code);
CREATE INDEX idx_transactions_user_date ON transactions(user_id, created_at);
CREATE INDEX idx_transactions_stock_date ON transactions(stock_code, created_at);
CREATE INDEX idx_news_published ON news(published_at);
CREATE INDEX idx_news_status ON news(status);
CREATE INDEX idx_settings_key ON settings(setting_key);
CREATE INDEX idx_watchlist_user ON watchlist(user_id);
CREATE INDEX idx_market_indices_code ON market_indices(code);
CREATE INDEX idx_stock_prices_code_date ON stock_prices(stock_code, date);
CREATE INDEX idx_market_index_history_code_date ON market_index_history(index_code, date);
