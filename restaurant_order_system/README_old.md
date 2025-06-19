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
- 📸 菜品圖片上傳與管理
- 🎛️ 完整的後台管理系統
- 💾 本地儲存與資料同步

## 專案結構
```
restaurant_order_system/
├── index.html                    # 系統入口頁面
├── customer/                     # 顧客端
│   ├── table-input.html         # 座位號碼輸入
│   └── menu.html                # 點餐頁面
├── kitchen/                     # 廚房端
│   └── orders.html              # 訂單管理
├── admin/                       # 管理端
│   ├── dashboard.html           # 管理主控台
│   ├── menu-manage.html         # 菜單管理
│   ├── orders.html              # 訂單管理
│   ├── reports.html             # 營業報表
│   ├── settings.html            # 系統設定
│   └── permissions.html         # 權限管理
├── api/                         # PHP API（待開發）
│   ├── config.php               # 資料庫設定
│   ├── orders.php               # 訂單相關API
│   └── menu.php                 # 菜單相關API
├── css/
│   ├── style.css                # 顧客端樣式
│   └── admin.css                # 管理端樣式
├── js/
│   ├── customer.js              # 顧客端邏輯
│   ├── table-input.js           # 桌號輸入邏輯
│   ├── admin-menu.js            # 菜單管理邏輯
│   ├── admin-dashboard.js       # 主控台邏輯
│   ├── admin-orders.js          # 訂單管理邏輯
│   ├── admin-reports.js         # 報表分析邏輯
│   ├── admin-settings.js        # 系統設定邏輯
│   └── admin-permissions.js     # 權限管理邏輯
├── test-admin.html              # 管理端測試頁面
├── test-image-upload.html       # 圖片上傳測試
└── test-admin-functions.html    # 完整功能測試
```

### 技術實現

### 前端技術
- HTML5
- CSS3（響應式設計）
- Vanilla JavaScript
- LocalStorage（購物車暫存）

### 資料儲存
- 使用 LocalStorage 儲存菜單和購物車資料
- 管理端與顧客端資料即時同步
- 支援圖片 Base64 格式儲存
- 不保存敏感的桌號和訂單歷史資訊
- 每次重新整理自動清除會話資料

### 菜單管理系統
- 完整的 CRUD 操作（新增/修改/刪除/查詢）
- 菜品分類管理（主食/開胃菜/湯品/甜點/飲料）
- 圖片上傳與預覽功能
- 菜品狀態管理（上架/停售）
- 即時搜尋與過濾功能
- 拖拽上傳支援，檔案大小限制 5MB

## 使用說明

### 🎯 快速開始
1. **開啟系統**: 直接在瀏覽器中開啟 `index.html`
2. **管理端**: 訪問 `admin/dashboard.html` 進入管理後台
3. **顧客端**: 訪問 `customer/table-input.html` 開始點餐
4. **測試功能**: 使用 `test-admin-functions.html` 測試所有功能

### 👥 顧客端操作
1. 掃描餐桌上的 QR Code 或直接進入系統
2. 輸入桌號（1-99）
3. 瀏覽分類菜單，點選想要的餐點
4. 使用 +/- 按鈕調整數量
5. 點擊購物車圖示查看訂單
6. 確認訂單內容並送出

### 🎛️ 管理端操作
1. **首次設定**: 進入系統設定頁面配置餐廳資訊
2. **菜單管理**: 上傳菜品、設定價格、管理分類
3. **訂單處理**: 即時查看和處理顧客訂單
4. **營業分析**: 查看銷售報表和營收統計
5. **用戶管理**: 建立員工帳號、分配權限角色
6. **系統維護**: 定期備份資料、調整系統設定

### 🔧 技術特色

#### 無後端設計
- 使用 **localStorage** 實現完整資料管理
- 無需資料庫伺服器，降低部署複雜度
- 適合小型餐廳快速部署使用

#### 響應式設計
- **手機優先**的設計理念
- 完美支援各種螢幕尺寸
- 觸控友好的操作介面

#### 即時同步
- 管理端與顧客端資料即時同步
- 菜單異動立即反映到顧客端
- 訂單狀態即時更新

#### 權限控制
- 基於角色的存取控制 (RBAC)
- 細粒度權限管理
- 完整的操作記錄追蹤

#### 圖片處理
- 支援多種圖片格式（JPG、PNG、GIF、WebP）
- 拖拽上傳，檔案大小限制 5MB
- Base64 編碼儲存，無需額外圖片伺服器

#### 資料安全
- 完整的備份還原機制
- 權限變更記錄追蹤
- 安全設定與密碼政策

## 📈 開發狀態

### ✅ 已完成功能

#### 顧客端 (Customer)
- [x] 桌號輸入系統（數字鍵盤）
- [x] 菜單瀏覽與分類篩選
- [x] 購物車功能與數量調整
- [x] 訂單確認與提交
- [x] 響應式設計（手機優化）
- [x] 菜品圖片顯示支援

#### 廚房端 (Kitchen)
- [ ] 即時訂單接收顯示
- [ ] 訂單狀態管理
- [ ] 出餐完成確認
- [ ] 訂單優先級排序

#### 管理端 (Admin)
- [x] 管理主控台儀表板
- [x] 菜單完整 CRUD 管理
- [x] 菜品圖片上傳與管理
- [x] 菜品分類與狀態管理
- [x] 即時搜尋與過濾功能
- [x] 訂單管理系統
- [x] 營業報表與統計分析
- [x] 系統設定管理
- [x] 用戶與權限管理
- [x] 角色管理系統
- [x] 桌號管理功能
- [x] 備份還原機制
- [x] 安全設定控制
- [x] 多主題支援
- [x] 完整測試工具

### 🚧 待開發功能

#### API 後端整合
- [ ] PHP RESTful API 開發
- [ ] MySQL 資料庫整合
- [ ] 用戶認證與會話管理
- [ ] 檔案上傳 API

#### 進階功能
- [ ] 即時通知系統
- [ ] 訂單即時同步
- [ ] 多語言完整支援
- [ ] 線上支付整合
- [ ] 會員系統
- [ ] 優惠券功能
- [ ] 庫存管理
- [ ] 多店鋪支援

### 🎯 專案里程碑

- **Phase 1**: ✅ 基礎顧客端點餐功能
- **Phase 2**: ✅ 完整管理端系統
- **Phase 3**: 🚧 後端 API 與資料庫整合
- **Phase 4**: 📋 進階功能與優化
- **Phase 5**: 📋 生產環境部署

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
    category ENUM('main', 'appetizer', 'soup', 'dessert', 'drink') NOT NULL,
    image LONGTEXT,  -- Base64 圖片資料
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
- [x] **完整的後台菜單管理系統**
  - [x] 菜品 CRUD 操作
  - [x] 圖片上傳與預覽功能
  - [x] 拖拽上傳支援
  - [x] 分類管理與過濾
  - [x] 菜品狀態切換
  - [x] 即時搜尋功能
  - [x] 資料驗證與錯誤處理
- [x] **管理端與顧客端資料同步**
- [x] **圖片儲存系統（Base64 格式）**
- [x] **測試工具與示範頁面**

### 近期開發計劃
- [ ] 廚房管理介面實作
  - [ ] 即時訂單顯示
  - [ ] 訂單狀態更新
  - [ ] 訂單列印功能
- [ ] 後台管理系統擴展
  - [ ] 營業報表與統計
  - [ ] 座位管理系統
  - [ ] 用戶權限管理
  - [ ] 系統設定介面
- [ ] 資料庫整合
  - [ ] PHP API 開發
  - [ ] 資料同步機制
  - [ ] 離線模式支援
- [ ] 系統優化與功能增強
  - [ ] 效能優化
  - [ ] 進階錯誤處理
  - [ ] 安全性強化
  - [ ] 多語言支援
  - [ ] QR Code 生成器
  - [ ] 訂單通知系統

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
1. 開啟管理頁面 `admin/menu-manage.html`
2. 管理菜單項目（新增/編輯/刪除）
3. 上傳菜品圖片
4. 設定菜品狀態（上架/停售）
5. 使用測試工具驗證功能

### 開發測試流程
1. 使用 `test-admin.html` 測試資料功能
2. 使用 `test-image-upload.html` 測試圖片功能
3. 檢查管理端與顧客端的資料同步
4. 驗證響應式設計在不同裝置上的表現

## 技術特點

### 前端架構
- **模組化設計**：各功能獨立的 JavaScript 類別
- **事件驅動**：完整的事件監聽器系統
- **響應式佈局**：手機優先的設計原則
- **用戶體驗**：即時反饋和友善的錯誤處理

### 資料管理
- **本地優先**：使用 localStorage 確保離線功能
- **資料同步**：管理端與顧客端即時同步
- **格式驗證**：完整的輸入驗證機制
- **錯誤處理**：優雅的錯誤處理和用戶提示

### 圖片處理
- **多格式支援**：JPG、PNG、GIF、WebP
- **大小限制**：5MB 檔案大小限制
- **Base64 儲存**：無需伺服器的圖片儲存方案
- **拖拽上傳**：現代化的上傳體驗

## 🚀 安裝部署

### 💻 本地開發
```bash
# 1. 克隆專案（或下載ZIP）
git clone [repository-url]
cd restaurant_order_system

# 2. 直接在瀏覽器開啟
# 顧客端：開啟 index.html
# 管理端：開啟 admin/dashboard.html
# 測試頁面：開啟 test-admin-functions.html
```

### 🌐 網頁伺服器部署
```bash
# 使用 Python 簡易伺服器
python -m http.server 8000

# 使用 Node.js serve
npx serve .

# 使用 PHP 內建伺服器
php -S localhost:8000
```

### 📱 行動裝置測試
1. 確保電腦和手機在同一網路
2. 查詢電腦 IP 地址
3. 在手機瀏覽器輸入：`http://[IP]:8000`
4. 測試響應式設計和觸控操作

### � 自訂配置
1. **修改預設資料**：編輯各 JavaScript 文件中的預設值
2. **調整樣式**：修改 `css/style.css` 和 `css/admin.css`
3. **新增功能**：擴展對應的 JavaScript 模組
4. **客製化權限**：修改 `js/admin-permissions.js` 中的角色定義

## 📞 技術支援

### 🐛 常見問題
- **Q**: 圖片上傳失敗？
  - **A**: 檢查檔案大小是否超過 5MB，格式是否為 JPG/PNG/GIF/WebP
  
- **Q**: 資料遺失怎麼辦？
  - **A**: 使用系統設定中的備份還原功能，定期備份資料
  
- **Q**: 手機顯示異常？
  - **A**: 確保使用現代瀏覽器，清除快取重新載入

### � 除錯技巧
1. **開啟瀏覽器開發者工具**（F12）
2. **查看 Console 錯誤訊息**
3. **檢查 localStorage 資料**
4. **使用測試頁面驗證功能**

### 📈 效能優化
- 定期清理 localStorage 中的舊資料
- 壓縮圖片以減少儲存空間
- 使用現代瀏覽器以獲得最佳效能

## 🤝 貢獻指南

### � 開發環境
- 任何現代文字編輯器（VS Code 推薦）
- 現代瀏覽器（Chrome/Firefox/Safari/Edge）
- 基本的 HTML/CSS/JavaScript 知識

### 📋 開發流程
1. Fork 專案並建立新分支
2. 開發新功能或修復問題
3. 使用測試頁面驗證功能
4. 提交 Pull Request

### 🏗️ 程式碼規範
- 使用一致的縮進（2 或 4 空格）
- 添加適當的註解說明
- 遵循現有的命名規則
- 保持函數簡潔易讀

---

**📧 聯絡資訊**: 如有問題或建議，歡迎提出 Issue 或 Pull Request

**⭐ 如果這個專案對您有幫助，請給個星星支持！**
- **圖片管理**: 拖拽上傳、即時預覽、Base64 儲存
- **分類管理**: 主食、開胃菜、湯品、甜點、飲料
- **狀態控制**: 上架/下架、庫存管理
- **搜尋過濾**: 即時搜尋、多條件篩選
- **批量操作**: 多選管理、批量上下架

### 📋 訂單管理 (`orders.html`)
- **訂單列表**: 即時訂單顯示與管理
- **狀態追蹤**: 待處理→製作中→完成→取餐
- **訂單詳情**: 完整訂單資訊檢視
- **快速操作**: 一鍵接單、完成、取消
- **歷史查詢**: 訂單歷史記錄查詢
- **統計分析**: 訂單統計與分析

### 📈 營業報表 (`reports.html`)
- **銷售統計**: 日/週/月銷售報表
- **熱門菜品**: 最受歡迎菜品分析
- **營收分析**: 收入趨勢與預測
- **圖表視覺化**: 多種圖表展示
- **報表匯出**: 支援 PDF/Excel 匯出
- **自定義報表**: 可客製化報表項目

### ⚙️ 系統設定 (`settings.html`)
- **餐廳資訊**: 名稱、電話、地址、營業時間
- **用戶管理**: 
  - 新增、編輯、停用用戶
  - 用戶角色分配
  - 登入狀態管理
- **系統配置**:
  - 訂單自動接受設定
  - 主題切換（預設/深色/藍色）
  - 多語言支援（繁中/簡中/英文）
  - 通知設定（音效/郵件）
- **桌號管理**:
  - 最大桌號設定（1-999）
  - 桌號前綴（A、B、VIP等）
  - 桌號狀態視覺化管理
- **備份還原**:
  - 一鍵資料備份
  - 備份檔案下載
  - 資料還原功能
  - 備份歷史記錄
- **安全設定**:
  - 密碼政策設定
  - 登入失敗限制
  - 會話超時控制
  - 資料加密設定

### 🔐 權限管理 (`permissions.html`)
- **角色管理**:
  - 預設角色：管理員、經理、員工
  - 自定義角色建立
  - 角色權限可視化
- **權限矩陣**:
  - 模組化權限控制
  - 視覺化權限分配
  - 細粒度權限管理
- **用戶權限**:
  - 基於角色的存取控制 (RBAC)
  - 個別用戶權限覆蓋
  - 權限繼承機制
- **操作記錄**:
  - 完整權限變更記錄
  - 操作追蹤與審計
  - 可篩選的記錄查詢

### 🧪 測試工具
- **功能測試頁面** (`test-admin-functions.html`):
  - 系統設定功能測試
  - 權限管理功能測試
  - 資料同步測試
  - 本地儲存測試
  - 主題切換測試
  - 自動化測試腳本
  - 測試結果統計

### 🔒 權限系統架構

#### 預設角色權限
```javascript
// 管理員 (admin)
permissions: ['*'] // 所有權限

// 經理 (manager)  
permissions: [
  'menu_view', 'menu_create', 'menu_edit', 'menu_delete',
  'order_view', 'order_process', 'order_cancel',
  'report_sales', 'report_revenue', 'report_dashboard'
]

// 員工 (staff)
permissions: [
  'menu_view',
  'order_view', 'order_process'
]
```

#### 權限模組
- **系統管理**: 系統設定、用戶管理、權限設定
- **菜單管理**: 菜品 CRUD、分類管理、圖片上傳
- **訂單管理**: 訂單處理、狀態管理、歷史查詢
- **報表分析**: 銷售統計、營收分析、數據匯出
- **廚房管理**: 訂單接收、製作狀態、完成確認
- **備份還原**: 資料備份、系統還原、歷史管理

## 資料結構

### 菜單資料 (menuItems)
```javascript
{
  id: "item_timestamp",
  name: "菜品名稱",
  price: 120,
  category: "主食",
  description: "菜品描述",
  image: "base64_string_or_url",
  available: true,
  createdAt: "2024-01-15T10:30:00.000Z",
  updatedAt: "2024-01-15T10:30:00.000Z"
}
```

### 用戶資料 (restaurantUsers)
```javascript
{
  username: "admin",
  email: "admin@restaurant.com",
  role: "admin",
  status: "active",
  lastLogin: "2024-01-15 14:30:25",
  createdAt: "2024-01-01T00:00:00.000Z"
}
```

### 角色資料 (userRoles)
```javascript
{
  id: "admin",
  name: "管理員",
  description: "系統最高權限",
  permissions: ["*"],
  userCount: 1,
  isSystem: true
}
```

### 系統設定 (restaurantSettings)
```javascript
{
  restaurantInfo: {
    name: "餐廳名稱",
    phone: "聯絡電話",
    address: "餐廳地址",
    businessHours: "營業時間",
    description: "餐廳簡介"
  },
  systemConfig: {
    theme: "default",
    language: "zh-TW",
    autoAcceptOrders: false,
    orderTimeout: 30
  },
  securitySettings: {
    minPasswordLength: 8,
    requireUppercase: true,
    maxLoginAttempts: 5,
    sessionTimeout: 60
  }
}
```

## 測試工具

### 🧪 功能測試頁面
- **test-admin-functions.html**: 管理端功能完整測試
- **test-image-upload.html**: 圖片上傳功能測試
- **test-admin.html**: 管理端整合測試

### 測試項目
- ✅ 餐廳資訊管理
- ✅ 用戶管理功能
- ✅ 系統配置儲存
- ✅ 桌號管理
- ✅ 備份還原機制
- ✅ 角色權限管理
- ✅ 資料同步功能
- ✅ 本地儲存測試
- ✅ 主題切換功能
