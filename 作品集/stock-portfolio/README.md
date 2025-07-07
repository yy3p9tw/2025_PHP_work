# 股票投資組合管理系統

這是一個現代化的股票投資組合管理系統，提供完整的前台用戶功能和後台管理功能。

## 功能特色

### 前台功能
- **用戶註冊/登入系統**
- **股票瀏覽與搜索**
- **投資組合管理**
- **關注清單功能**
- **交易記錄追蹤**
- **財經新聞中心**
- **個人資料管理**
- **市場指數即時顯示**

### 後台功能
- **用戶管理**
- **股票資料管理**
- **新聞內容管理**
- **系統設定管理**
- **統計報表功能**

## 技術架構

### 前端技術
- **HTML5 / CSS3**
- **Bootstrap 5** - 響應式設計框架
- **JavaScript** - 互動功能
- **Font Awesome** - 圖標庫

### 後端技術
- **PHP 7.4+** - 主要程式語言
- **MySQL** - 資料庫系統
- **PDO** - 資料庫連接
- **Session** - 用戶狀態管理

### 安全特色
- **密碼加密** (password_hash)
- **CSRF 防護**
- **SQL 注入防護**
- **XSS 防護**
- **用戶權限管理**

## 安裝說明

### 1. 環境需求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Apache 或 Nginx 網頁伺服器
- 支援 PDO 擴展

### 2. 安裝步驟

#### 步驟 1: 克隆專案
```bash
git clone [repository-url]
cd stock-portfolio
```

#### 步驟 2: 設定資料庫
1. 建立 MySQL 資料庫
2. 匯入 `database/stock_portfolio.sql` 檔案
```sql
mysql -u root -p
CREATE DATABASE stock_portfolio;
USE stock_portfolio;
SOURCE database/stock_portfolio.sql;
```

#### 步驟 3: 設定配置
1. 複製並編輯 `config.php`
2. 修改資料庫連接設定
```php
// 資料庫設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'stock_portfolio');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

#### 步驟 4: 設定權限
確保以下目錄有寫入權限：
- `assets/` 目錄
- `uploads/` 目錄（如有）

#### 步驟 5: 測試安裝
訪問 `test.php` 頁面進行系統測試

## 預設帳戶

### 管理員帳戶
- **帳號:** admin
- **密碼:** admin123
- **權限:** 管理員

### 測試用戶
- **帳號:** demo
- **密碼:** demo123
- **權限:** 一般用戶

## 專案結構

```
stock-portfolio/
├── admin/                  # 管理後台
│   ├── index.php          # 後台首頁
│   ├── users.php          # 用戶管理
│   ├── stocks.php         # 股票管理
│   ├── news.php           # 新聞管理
│   └── settings.php       # 系統設定
├── assets/                # 靜態資源
│   ├── css/               # CSS 樣式
│   └── js/                # JavaScript 檔案
├── database/              # 資料庫檔案
│   └── stock_portfolio.sql # 資料庫結構
├── includes/              # 共用檔案
│   ├── config.php         # 系統設定
│   ├── database.php       # 資料庫連接
│   ├── auth.php           # 驗證功能
│   ├── header.php         # 頁首
│   └── footer.php         # 頁尾
├── index.php              # 首頁
├── login.php              # 登入頁
├── register.php           # 註冊頁
├── portfolio.php          # 投資組合
├── watchlist.php          # 關注清單
├── stocks.php             # 股票列表
├── news.php               # 新聞中心
├── profile.php            # 個人資料
├── test.php               # 系統測試
└── README.md              # 說明文件
```

## 資料庫結構

### 主要資料表
- `users` - 用戶資料
- `stocks` - 股票基本資料
- `portfolios` - 投資組合
- `transactions` - 交易記錄
- `watchlist` - 關注清單
- `news` - 新聞資料
- `market_indices` - 市場指數
- `settings` - 系統設定

## 開發指南

### 開發環境設置
1. 使用 XAMPP 或 WAMP 等本地伺服器
2. 啟動 Apache 和 MySQL 服務
3. 將專案放在 `htdocs` 目錄下

### 程式碼規範
- 使用 PSR-4 自動載入標準
- 遵循 PHP 程式碼風格指南
- 使用準備語句防止 SQL 注入
- 所有用戶輸入都需要驗證和過濾

### 安全最佳實踐
- 密碼使用 `password_hash()` 加密
- 表單包含 CSRF token 驗證
- 使用 `htmlspecialchars()` 防止 XSS
- 適當的用戶權限檢查

## 版本歷史

### v1.0.0 (2025-07-07)
- 初始版本發布
- 完整的前台和後台功能
- 用戶認證系統
- 投資組合管理功能
- 新聞管理系統

## 授權條款

本專案採用 MIT 授權條款。詳細資訊請查看 LICENSE 檔案。

## 支援與回饋

如果您在使用過程中遇到問題或有建議，請透過以下方式聯繫：

- 建立 Issue 回報問題
- 提交 Pull Request 貢獻程式碼
- 發送電子郵件至 [your-email@example.com]

## 更新日誌

### 計劃中的功能
- [ ] 股價即時更新功能
- [ ] 圖表視覺化
- [ ] 行動端應用
- [ ] API 介面
- [ ] 多語言支援
- [ ] 報表匯出功能

### 已知問題
- 股價資料為模擬資料，需要整合真實的股價 API
- 圖表功能尚未完全實現
- 部分樣式在舊版瀏覽器中可能不相容

## 致謝

感謝所有為此專案做出貢獻的開發者和使用者。
