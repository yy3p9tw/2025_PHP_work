# 公仔電商網站 專案規格書

## 1. 專案簡介
以 PHP + MySQL + Bootstrap 打造現代化公仔電商網站，支援前後台管理、商品展示、購物車、會員、訂單等功能。

---

## 2. 目前專案狀況

### ✅ 已完成功能

#### 前台基礎功能
- **首頁 (index.html)**：✅ 動態頁面，包含現代化導航和動態內容載入
- **商品詳情頁 (product_detail.html)**：✅ 商品詳細資訊展示頁面，支援 API 串接
- **共用導航 (navbar.html)**：✅ 多層分類下拉選單、購物車徽章、登入/後台連結
- **前台 JavaScript**：✅ 模組化 JS 檔案（index.js、category-navbar.js、product-detail.js）
- **圖片錯誤處理**：✅ 所有圖片都有適當的 onerror 處理和備用圖片
- **Favicon**：✅ 添加網站圖示，避免 404 錯誤

#### 後台管理功能
- **管理員登入系統 (admin/index.php)**：使用者驗證與 Session 管理
- **儀表板 (admin/dashboard.php)**：管理首頁
- **商品管理 (admin/products.php)**：商品 CRUD 功能
- **會員管理 (admin/users.php)**：使用者管理
- **輪播管理 (admin/carousel.php)**：輪播圖管理
- **共用側邊欄 (admin/sidebar.php)**：後台導航組件

#### API 功能
- **商品列表 API (api/products.php)**：✅ 支援分頁、圖片路徑處理、分類篩選、價格篩選、排序
- **商品詳情 API (api/product_detail.php)**：單一商品查詢
- **輪播 API (api/carousel.php)**：輪播圖資料查詢
- **分類 API (api/categories.php)**：✅ 商品分類查詢（暫以靜態資料）

#### 資料庫結構
- **products 表**：商品基本資料（id, name, description, price, image_url, created_at）
- **users 表**：使用者資料（id, username, password, role, created_at）
- **carousel_slides 表**：輪播圖資料（id, title, description, image_url, slide_order, created_at）
- **cart_items 表**：✅ 購物車項目（id, session_id, product_id, quantity, price, added_at, updated_at）

### ❌ 待開發功能

#### 前台進階功能
- **分類頁面 (category.html)**：✅ 商品分類瀏覽頁面，支援篩選、排序、檢視模式切換
- **購物車功能**：✅ 加入購物車、購物車頁面、數量管理、商品移除、清空購物車
- **商品搜尋與篩選**：✅ 價格範圍、分類篩選、關鍵字搜尋（已實作於分類頁面）
- **會員系統**：註冊、登入、個人資料管理
- **現代化 UI/UX 優化**：商品卡片動畫、RWD 優化、互動體驗增強

#### 後台進階功能
- **分類管理**：✅ 多層分類 CRUD、樹狀顯示、Modal 表單、防循環引用檢查（拖曳排序功能已備妥）
- **訂單管理**：訂單查詢、狀態更新、出貨管理
- **庫存管理**：商品庫存追蹤、警告機制
- **報表統計**：銷售報表、商品統計、會員分析

#### 資料庫擴充
- **categories 表**：商品分類系統
- **product_category 表**：商品分類關聯
- **cart_items 表**：✅ 購物車項目（已實作）
- **orders 表**：訂單主表
- **order_items 表**：訂單明細

#### 系統架構優化
- **API 架構完善**：錯誤處理、資料驗證、安全機制
- **前後台 JavaScript 分離**：模組化開發、功能分離
- **圖片上傳系統**：檔案管理、路徑統一、圖片壓縮
- **SEO 優化**：Meta 標籤、結構化資料、sitemap

### 目前 API 架構

#### 已實作 API (`/api/`)
- **products.php**：商品列表查詢
  - 功能：分頁查詢、圖片路徑處理、分類篩選、價格篩選、排序
  - 參數：page（頁碼）、limit（每頁數量）、category（分類ID）、price_min（最低價格）、price_max（最高價格）、sort（排序方式）
  - 回傳：商品陣列、分頁資訊

- **product_detail.php**：單一商品詳情
  - 功能：根據 ID 查詢單一商品
  - 參數：id（商品 ID）
  - 回傳：完整商品資訊

- **carousel.php**：輪播圖查詢
  - 功能：取得所有輪播圖資料
  - 回傳：輪播圖陣列（依 slide_order 排序）

- **categories.php**：✅ 分類查詢
  - 功能：取得商品分類資料（從資料庫動態讀取，支援多層樹狀結構）
  - 回傳：多層分類結構

#### 購物車 API (`/api/`)
- **cart_get.php**：✅ 取得購物車內容
- **cart_add.php**：✅ 加入商品到購物車
- **cart_update.php**：✅ 更新購物車數量
- **cart_remove.php**：✅ 移除購物車商品
- **cart_clear.php**：✅ 清空購物車

#### 後台管理 API (`/admin/api/`)
- **category_detail.php**：✅ 單一分類詳情查詢
- **category_manage.php**：✅ 分類 CRUD 操作（POST/PUT/DELETE）
- **category_sort.php**：✅ 分類排序更新

#### 待開發 API
- **admin/api/** 目錄：後台管理 API 組

### 目前頁面結構

#### 已實作頁面
- **index.html**：首頁
  - 狀態：✅ 完成 - 動態載入 navbar.html、API 串接、動態內容載入、現代化 UI

- **product_detail.html**：商品詳情頁
  - 狀態：✅ 完成 - API 串接、共用導航、現代化互動功能

- **navbar.html**：共用導航組件
  - 狀態：✅ 完成 - 多層分類下拉、購物車徽章、登入/後台連結

- **category.html**：✅ 商品分類頁面
  - 狀態：✅ 完成 - 分類篩選、價格篩選、排序、檢視模式切換、分頁

- **cart.html**：✅ 購物車頁面
  - 狀態：✅ 完成 - 購物車管理、數量調整、商品移除、結帳流程UI、推薦商品

#### 待開發頁面
- **login.html / register.html**：會員登入註冊頁面

### 目前後台管理結構

#### 已實作管理頁面 (`/admin/`)
- **index.php**：管理員登入頁
  - 功能：使用者驗證、Session 管理
  - 狀態：完成，支援 admin/staff 角色區分

- **dashboard.php**：儀表板首頁
  - 功能：管理概覽
  - 狀態：基礎完成

- **sidebar.php**：側邊欄組件
  - 功能：所有管理頁面共用導航
  - 狀態：完成

- **products.php**：商品管理頁面
  - 功能：商品 CRUD、圖片管理
  - 狀態：基礎完成

- **users.php**：會員管理頁面
  - 功能：使用者 CRUD、權限管理
  - 狀態：基礎完成

- **carousel.php**：輪播管理頁面
  - 功能：輪播圖 CRUD
  - 狀態：基礎完成

- **categories.php**：✅ 分類管理頁面
  - 狀態：✅ 完成 - 多層分類樹狀顯示、CRUD 操作、Modal 表單、拖曳排序、防循環引用檢查
- **orders.php**：訂單管理頁面
- **admin/api/** 目錄：後台 API 組
- **報表統計頁面**：銷售分析、數據報表

### 目前 JavaScript 架構

#### 已實作 JS (`/assets/js/`)
- **script.js**：基礎前台功能
  - 功能：平滑滾動等基本互動
  - 使用於：前台頁面

- **index.js**：✅ 首頁動態功能
  - 功能：輪播圖載入、商品列表載入、分頁處理
  - 使用於：index.html

- **category-navbar.js**：✅ 導航與分類功能
  - 功能：動態載入分類選單、下拉選單互動、分類頁面連結
  - 使用於：navbar.html

- **product-detail.js**：✅ 商品詳情互動
  - 功能：商品詳情載入、數量調整、加入購物車
  - 使用於：product_detail.html

- **category-page.js**：✅ 分類頁面功能
  - 功能：商品篩選、排序、分頁、檢視模式切換
  - 使用於：category.html

- **cart.js**：✅ 購物車功能
  - 功能：購物車管理、數量調整、商品移除、結帳UI、推薦商品
  - 使用於：cart.html

#### 後台 JS (`/admin/assets/js/`)
- **admin_script.js**：後台管理功能
  - 功能：後台通用 JavaScript
  - 使用於：後台管理頁面

#### 待開發 JavaScript
- **admin-carousel.js**：後台輪播管理
- **其他模組化 JS 檔案**

### 目前資料流向

```
前台使用者 → 動態頁面 → 模組化 JavaScript → API → 資料庫
管理員 → 後台登入 → 管理頁面 → 直接 PHP 操作 → 資料庫
```

### 目前路徑結構

#### 前台路徑
- `/index.html` → ✅ 首頁（動態化完成）
- `/product_detail.html` → ✅ 商品詳情頁（動態化完成）
- `/category.html` → ✅ 商品分類頁（篩選、排序、分頁功能完成）
- `/cart.html` → ✅ 購物車頁面（購物車管理功能完成）

#### 後台路徑
- `/admin/index.php` → 管理員登入
- `/admin/dashboard.php` → 儀表板
- `/admin/products.php` → 商品管理
- `/admin/users.php` → 會員管理
- `/admin/carousel.php` → 輪播管理

#### API 路徑
- `/api/products.php` → 商品列表 API
- `/api/product_detail.php` → 商品詳情 API
- `/api/carousel.php` → 輪播 API
- `/api/categories.php` → ✅ 分類 API
- `/api/cart_*.php` → ✅ 購物車 API 組（get, add, update, remove, clear）

---

## 3. 待辦/可新增功能


### 商品/前台 & 管理端
- 商品多層分類、分類篩選、分類管理（前台可瀏覽/篩選，管理端可 CRUD 分類）
- 商品排序（價格、上架時間、人氣，前台可選擇，管理端可設定排序規則）
- 商品特色、規格、標籤顯示（前台顯示，管理端可編輯）
- 相關推薦商品區塊（前台顯示，管理端可設定推薦）
- 商品卡片 hover 動畫、RWD 美化（前台 UI，管理端可預覽）

### 購物/會員 & 管理端
- **會員註冊/登入/登出、會員專區**（前台），**會員管理**（管理端）
- **訂單管理、訂單查詢**（前台查詢，管理端審核/出貨/狀態更新）
- **結帳流程完善**（付款確認、訂單生成、庫存扣減）
- **支援多種付款/運送方式**（前台選擇，管理端設定/維護）

### 系統優化
- **API 錯誤處理優化**
- **前後台 JavaScript 完全分離**
- **購物車 Session 管理優化**
- **圖片上傳與路徑處理統一**
- **RWD 響應式設計完善**
- **SEO 優化、Meta 標籤管理**

### 其他
- 客服聯絡表單、FAQ、品牌故事、門市資訊（前台顯示，管理端可維護內容）
- SEO、GA/FB Pixel 支援

---

## 4. 目前資料庫結構

### 已實作資料表

#### 核心資料表
- **products**：商品主表
  - 欄位：id, name, description, price, image_url, created_at
  - 狀態：基礎完成
  - 缺少：stock_quantity, status, updated_at, category_id 等欄位

- **users**：使用者表
  - 欄位：id, username, password, role, created_at
  - 狀態：完成
  - 支援：admin/staff 角色區分

- **carousel_slides**：輪播圖表
  - 欄位：id, title, description, image_url, slide_order, created_at
  - 狀態：基礎完成
  - 缺少：link_url, status, updated_at 等欄位

- **cart_items**：✅ 購物車項目表
  - 欄位：id, session_id, product_id, quantity, price, added_at, updated_at
  - 狀態：✅ 完成

### 待建立資料表

#### 分類系統
- **categories**：✅ 商品分類表
  - 欄位：id, name, description, parent_id, sort_order, status, created_at, updated_at
  - 狀態：✅ 完成

- **product_category**：✅ 商品分類對應表
  - 欄位：product_id, category_id, created_at
  - 狀態：✅ 完成

#### 訂單系統

- **orders**：訂單主表
  - 規劃欄位：id, user_id, session_id, total_amount, status, payment_method, shipping_method, created_at, updated_at

- **order_items**：訂單明細表
  - 規劃欄位：id, order_id, product_id, quantity, price, subtotal

### 目前資料表關係

```
products ←→ product_category ←→ categories (✅ 已建立)
    ↓                              ↓
cart_items (✅ 已建立)         (階層關係)
    ↓
orders (待建立) ←→ order_items (待建立)
    ↓
users (已建立)

carousel_slides (獨立表，已建立)
```

### 規劃資料表關係

```
完整架構已大部分實現：
- 商品分類系統 ✅ 
- 購物車系統 ✅
- 基礎會員系統 ✅
- 待完成：訂單系統
```

---

## 5. UI/UX
- 採用 Bootstrap 5，RWD 響應式設計
- 商品卡片、詳情頁、購物車、分頁、分類等皆美觀現代
- 支援社群分享、收藏、推薦商品（可擴充）

---

## 6. 參考網站功能
- 商品多層分類、篩選、排序
- 商品詳情頁：大圖、特色、規格、付款/運送方式、推薦商品
- 購物車、會員、訂單、客服、品牌故事、門市資訊

---

## 7. 目前檔案結構

### 實際專案結構
```
action_figure_store/
├── index.html                 # ✅ 首頁（動態化完成）
├── product_detail.html        # ✅ 商品詳情頁（動態化完成）
├── navbar.html                # ✅ 共用導航組件
├── category.html                # ✅ 商品分類頁（篩選、排序、分頁功能完成）
├── cart.html                    # ✅ 購物車頁面（購物車管理功能完成）
├── spec.md                    # 專案規格書
├── action_figure_store.sql    # 資料庫結構檔案
│
├── api/                       # 前台 API
│   ├── products.php           # 商品列表 API
│   ├── product_detail.php     # 商品詳情 API
│   ├── carousel.php           # 輪播 API
│   ├── categories.php         # ✅ 分類 API（暫以靜態資料）
│   ├── cart_get.php           # ✅ 取得購物車 API
│   ├── cart_add.php           # ✅ 加入購物車 API
│   ├── cart_update.php        # ✅ 更新購物車 API
│   ├── cart_remove.php        # ✅ 移除購物車 API
│   └── cart_clear.php         # ✅ 清空購物車 API
│
├── admin/                     # 後台管理
│   ├── index.php              # 管理員登入
│   ├── dashboard.php          # 儀表板
│   ├── sidebar.php            # 側邊欄組件
│   ├── products.php           # 商品管理
│   ├── users.php              # 會員管理
│   ├── carousel.php           # 輪播管理
│   └── logout.php             # 登出
│   │
│   └── assets/                # 後台專用資源
│       ├── css/
│       │   └── admin_style.css
│       └── js/
│           └── admin_script.js
│
├── assets/                    # 前台資源
│   ├── css/
│   │   └── style.css          # 前台主樣式
│   │
│   ├── js/                    # JavaScript 檔案
│   │   ├── script.js          # 基礎前台 JS
│   │   ├── index.js           # ✅ 首頁動態功能
│   │   ├── category-navbar.js # ✅ 導航與分類功能
│   │   ├── category-page.js   # ✅ 分類頁面功能
│   │   ├── cart.js            # ✅ 購物車功能
│   │   └── product-detail.js  # ✅ 商品詳情互動
│   │
│   └── images/                # 圖片資源
│
├── includes/                  # 共用檔案
│   ├── config.php             # 資料庫配置
│   ├── db.php                 # 資料庫連線類別
│   ├── db_connect.php         # 資料庫連線（備用）
│   ├── auth.php               # 驗證功能
│   └── functions.php          # 共用函數
│
└── uploads/                   # 上傳檔案目錄
```

### 缺少的檔案（相比完整版）

#### 前台頁面
- `login.html / register.html` - 會員登入註冊頁面

#### 後台功能
- `admin/categories.php` - ✅ 分類管理（完整 CRUD、樹狀顯示、Modal 介面）
- `admin/api/category_detail.php` - ✅ 分類詳情 API
- `admin/api/category_manage.php` - ✅ 分類 CRUD API
- `admin/api/category_sort.php` - ✅ 分類排序 API
- `admin/test_categories.php` - ✅ 分類系統測試頁面
- `admin/orders.php` - 訂單管理
- `admin/api/` - 後台 API 目錄

#### 其他
- 會員前台功能
- 訂單系統
