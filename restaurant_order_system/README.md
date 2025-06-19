# 餐廳點餐系統

簡單易用的餐廳點餐系統，專為行動裝置優化。

## 主要功能

### 顧客點餐流程
1. 掃描 QR Code 進入系統
2. 輸入桌號（每次重新輸入）
3. 瀏覽菜單並選擇餐點
4. 直接在菜單頁面調整數量
5. 查看購物車及提交訂單

### 特色功能
- ✨ 響應式設計，完美支援手機操作
- 🔢 數字鍵盤輸入桌號
- 🍽️ 分類瀏覽菜單
- ➕ 即時調整餐點數量
- 🛒 即時更新購物車
- 🔄 每次重新輸入桌號，確保隱私

## 專案結構
```
restaurant_order_system/
├── index.html              # 系統入口頁面
├── customer/               # 顧客端
│   ├── table-input.html    # 座位號碼輸入
│   └── menu.html          # 點餐頁面
├── kitchen/               # 廚房端
│   └── orders.html        # 訂單管理
├── admin/                 # 管理端
│   ├── dashboard.html     # 後台首頁
│   ├── menu-manage.html   # 菜單管理
│   └── reports.html       # 報表
├── api/                   # PHP API
│   ├── config.php         # 資料庫設定
│   ├── orders.php         # 訂單相關API
│   └── menu.php           # 菜單相關API
├── css/
│   └── style.css          # 全局樣式
└── js/
    ├── customer.js        # 顧客端邏輯
    ├── kitchen.js         # 廚房端邏輯
    └── admin.js           # 管理端邏輯
```

### 技術實現

### 前端技術
- HTML5
- CSS3（響應式設計）
- Vanilla JavaScript
- LocalStorage（購物車暫存）

### 資料儲存
- 使用 LocalStorage 暫存購物車資料
- 不保存歷史訂單和桌號資訊
- 每次重新整理自動清除歷史資料

## 使用說明

### 顧客使用
1. 掃描餐桌上的 QR Code
2. 輸入桌號（1-99）
3. 瀏覽菜單，選擇餐點
4. 使用 +/- 按鈕調整數量
5. 點擊購物車圖示查看訂單
6. 確認訂單並送出

#### 廚房端 (Kitchen)
- [ ] 即時訂單顯示
- [ ] 訂單狀態管理
- [ ] 訂單完成確認

#### 管理端 (Admin)
- [ ] 菜單管理（新增/修改/刪除）
- [ ] 營業報表
- [ ] 座位管理
- [ ] 系統設定

### 技術規格

#### 前端
- HTML5 + CSS3 + Vanilla JavaScript
- 響應式設計（手機優先）
- localStorage 本地儲存

#### 後端
- PHP 7.4+
- MySQL 資料庫
- RESTful API

#### 資料庫設計
```sql
-- 菜單表
CREATE TABLE menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 訂單表
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_number INT NOT NULL,
    items JSON NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 開發狀態

### 已完成 (2025/6/19更新)
- [x] 基本專案結構與檔案組織
- [x] 全局樣式設計（響應式）
- [x] 座位輸入系統（數字鍵盤）
- [x] 菜單瀏覽與點餐功能
- [x] 即時購物車系統
- [x] LocalStorage 資料管理
- [x] 自動清除歷史資料
- [x] 檔案結構優化

### 近期開發計劃
- [ ] 廚房管理介面實作
  - [ ] 即時訂單顯示
  - [ ] 訂單狀態更新
- [ ] 後台管理系統
  - [ ] 菜單管理
  - [ ] 訂單查詢
  - [ ] 銷售報表
- [ ] 資料庫整合
  - [ ] API 開發
  - [ ] 資料同步機制
- [ ] 系統優化
  - [ ] 效能優化
  - [ ] 錯誤處理
  - [ ] 安全性強化

## 部署說明

### 開發環境
1. 安裝 XAMPP 或類似 PHP 開發環境
2. 將專案放在 htdocs 目錄
3. 啟動 Apache 和 MySQL 服務
4. 訪問 http://localhost/restaurant_order_system/

### 生產環境
1. 設定雲端資料庫連接
2. 修改 api/config.php 中的資料庫設定
3. 上傳檔案到網頁伺服器
4. 建立資料庫表格

## 使用說明

### 顧客使用流程
1. 掃描 QR Code 或訪問網站
2. 輸入座位號碼
3. 瀏覽菜單並加入購物車
4. 確認訂單

### 廚房使用流程
1. 開啟廚房管理頁面
2. 查看新訂單
3. 更新訂單狀態

### 管理使用流程
1. 登入後台管理
2. 管理菜單項目
3. 查看營業報表
