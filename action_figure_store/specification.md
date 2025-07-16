# 公仔模型商店技術規格書

## 0. 專案概述與目標

### 0.1 專案名稱
公仔模型商店 (Action Figure Store)

### 0.2 專案目標
建立一個專門銷售各類公仔模型、動漫周邊的線上電子商務平台。目標是提供流暢的用戶購買體驗，豐富的商品資訊展示，以及高效的後台管理功能，以滿足公仔收藏家和愛好者的需求，並擴大市場份額。

### 0.3 目標受眾
主要目標受眾為公仔收藏家、動漫愛好者、模型玩家以及尋找特色禮品的消費者。

## 1. 專案結構概覽

## 1. 專案結構概覽

一個典型的電子商務網站專案可能包含以下主要檔案和資料夾：

```
/project_root/
├───public/                 # 公開可訪問的檔案 (HTML, CSS, JS, 圖片)
│   ├───index.html          # 首頁
│   ├───products.html       # 商品列表頁
│   ├───product_detail.html # 商品詳情頁
│   ├───cart.html           # 購物車頁面
│   ├───checkout.html       # 結帳頁面
│   ├───login.html          # 登入頁面
│   ├───register.html       # 註冊頁面
│   ├───profile.html        # 用戶個人資料頁
│   ├───assets/             # 靜態資源
│   │   ├───css/            # 樣式表
│   │   ├───js/             # JavaScript 腳本
│   │   │   ├───main.js     # 主要前端邏輯
│   │   │   ├───api.js      # API 請求封裝
│   │   │   └───...         # 其他頁面專用腳本
│   │   └───images/         # 圖片
│   └───...                 # 其他前端頁面或資源
├───includes/               # 後端共用程式碼 (PHP)
│   ├───config.php          # 網站配置 (資料庫連線, 常數等)
│   ├───db.php              # 資料庫連接類/函數
│   ├───functions.php       # 通用函數 (例如：CSRF, 圖片上傳, 格式化資料)
│   ├───auth.php            # 身份驗證相關函數
│   └───...                 # 其他共用模組
├───api/                    # 後端 API 接口 (PHP)
│   ├───products.php        # 商品相關 API (獲取列表, 詳情)
│   ├───categories.php      # 分類相關 API
│   ├───cart.php            # 購物車相關 API (新增, 更新, 刪除, 清空, 獲取)
│   ├───auth.php            # 認證 API (登入, 註冊)
│   ├───user.php            # 用戶資料 API
│   └───...                 # 其他 API 接口
├───admin/                  # 後台管理介面 (PHP, HTML, CSS, JS)
│   ├───dashboard.php       # 後台儀表板
│   ├───products.php        # 商品管理
│   ├───categories.php      # 分類管理
│   ├───users.php           # 用戶管理
│   ├───orders.php          # 訂單管理
│   ├───assets/             # 後台靜態資源
│   │   ├───css/
│   │   ├───js/
│   │   └───...             
│   └───...                 # 其他後台模組
├───database/               # 資料庫相關檔案
│   ├───schema.sql          # 資料庫結構定義
│   ├───seed.sql            # 初始資料填充
│   └───...                 
├───.htaccess               # Apache 伺服器配置 (URL 重寫, 安全設定)
├───README.md               # 專案說明文件
├───composer.json           # PHP 依賴管理 (如果使用 Composer)
├───package.json            # Node.js 依賴管理 (如果使用 npm/yarn)
└───...                     # 其他配置或文件
```

## 1.1 技術棧 (Technology Stack)

*   **前端**: HTML5, CSS3, JavaScript (ES6+), jQuery (基於現有檔案結構推斷，可替換為 React/Vue.js 等框架), Bootstrap (用於響應式設計和UI組件)。
*   **後端**: PHP (版本待定，例如 7.4+), 原生 PHP (或基於輕量級框架如 CodeIgniter/Slim)。
*   **資料庫**: MySQL (版本待定，例如 8.0+)。
*   **伺服器**: Apache HTTP Server。
*   **版本控制**: Git。
*   **依賴管理**: Composer (PHP), npm/yarn (如果前端使用Node.js相關工具)。

## 2. 網頁清單與功能

### 2.1 前台網頁

*   **首頁 (`index.html` / `index.php`)**
    *   **功能**: 網站入口，展示最新商品、熱門商品、輪播廣告、促銷活動等。
    *   **主要區塊**: 導航欄、輪播圖、商品推薦區、分類導航、頁腳。
*   **商品列表頁 (`products.html` / `products.php`)**
    *   **功能**: 顯示所有或特定分類下的商品列表，提供篩選、排序、分頁功能。
    *   **主要區塊**: 商品卡片列表、篩選器、排序選項、分頁導航。
*   **商品詳情頁 (`product_detail.html` / `product_detail.php`)**
    *   **功能**: 顯示單一商品的詳細資訊，包括圖片、描述、價格、庫存、評論等，並提供加入購物車功能。針對公仔模型，需額外展示以下屬性：品牌/製造商、系列/IP、角色名稱、比例/尺寸、材質、發售日期/預計發售日期、預購/現貨狀態、限量版/特典資訊、商品狀態（全新/二手/盒損）。
    *   **主要區塊**: 商品圖片畫廊、商品名稱、價格、描述、數量選擇器、加入購物車按鈕、商品評論區、相關商品推薦、詳細屬性列表.
*   **購物車頁面 (`cart.html` / `cart.php`)**
    *   **功能**: 顯示用戶已加入購物車的商品，允許修改數量、移除商品、清空購物車，並顯示總價。
    *   **主要區塊**: 購物車商品列表 (商品名稱、圖片、單價、數量、小計)、總計、清空購物車按鈕、前往結帳按鈕。
*   **結帳頁面 (`checkout.html` / `checkout.php`)**
    *   **功能**: 引導用戶完成訂單提交流程，包括填寫收貨地址、選擇配送方式、選擇支付方式。
    *   **主要區塊**: 收貨地址表單、配送方式選擇、支付方式選擇、訂單摘要、提交訂單按鈕。
*   **登入頁面 (`login.html` / `login.php`)**
    *   **功能**: 用戶輸入帳號密碼進行登入。
    *   **主要區塊**: 登入表單 (用戶名/郵箱、密碼)、登入按鈕、忘記密碼連結、註冊連結。
*   **註冊頁面 (`register.html` / `register.php`)**
    *   **功能**: 新用戶註冊帳號。
    *   **主要區塊**: 註冊表單 (用戶名、郵箱、密碼、確認密碼)、註冊按鈕。
*   **用戶個人資料頁 (`profile.html` / `profile.php`)**
    *   **功能**: 顯示用戶個人資訊、訂單歷史、收貨地址管理等。
    *   **主要區塊**: 個人資訊展示、編輯按鈕、訂單列表、地址列表。

### 2.2 後台管理網頁 (僅限管理員訪問)

*   **儀表板 (`admin/dashboard.php`)**
    *   **功能**: 提供網站運營概覽，如銷售額、訂單數、用戶數、商品數等統計數據。
*   **商品管理 (`admin/products.php`)**
    *   **功能**: 增、刪、改、查商品資訊，包括商品名稱、描述、價格、圖片、分類、庫存等。需支援公仔模型特有屬性的管理：品牌/製造商、系列/IP、角色名稱、比例/尺寸、材質、發售日期/預計發售日期、預購/現貨狀態、限量版/特典資訊、商品狀態。
*   **分類管理 (`admin/categories.php`)**
    *   **功能**: 增、刪、改、查商品分類，支持多級分類。
*   **用戶管理 (`admin/users.php`)**
    *   **功能**: 增、刪、改、查用戶資訊，管理用戶角色 (管理員、普通用戶等)。
*   **訂單管理 (`admin/orders.php`)**
    *   **功能**: 查看所有訂單，更新訂單狀態 (待處理、已發貨、已完成等)。

## 3. 頁面間導航與按鈕功能

### 3.1 導航 (連結)

*   **導航欄 (Header)**:
    *   **Logo**: 點擊返回首頁 (`index.html` / `index.php`)。
    *   **分類選單**: 點擊分類名稱導航到對應的商品列表頁 (`products.html?category_id=X`)。
    *   **搜尋框**: 輸入關鍵字後提交，導航到商品列表頁 (`products.html?search=keyword`)。
    *   **購物車圖示**: 點擊導航到購物車頁面 (`cart.html` / `cart.php`)。
    *   **用戶圖示/名稱**: 點擊導航到登入/註冊頁面 (`login.html` / `login.php`) 或用戶個人資料頁 (`profile.html` / `profile.php`)。
*   **頁腳 (Footer)**:
    *   **版權資訊**: 通常為靜態文本。
    *   **管理後台連結**: 點擊導航到後台登入頁面 (`admin/index.php`)。
*   **麵包屑導航**:
    *   **各級連結**: 點擊返回上一級頁面或首頁。
*   **商品卡片/列表**:
    *   **商品圖片/名稱**: 點擊導航到商品詳情頁 (`product_detail.html?id=X`)。

### 3.2 按鈕功能 (主要透過表單提交或 JavaScript 事件)

*   **加入購物車按鈕 (商品詳情頁)**:
    *   **功能**: 將當前商品及選定數量加入購物車。
    *   **觸發**: JavaScript `onclick` 事件，發送 AJAX (Fetch API) 請求到 `api/cart.php` (POST)。
    *   **成功**: 提示加入成功，更新購物車圖示上的商品數量。
    *   **失敗**: 提示錯誤訊息。
*   **更新購物車數量按鈕 (購物車頁面)**:
    *   **功能**: 修改購物車中商品的數量。
    *   **觸發**: JavaScript `onchange` (數量輸入框) 或 `onclick` (增減按鈕)，發送 AJAX 請求到 `api/cart.php` (PUT)。
    *   **成功**: 更新商品小計和購物車總計。
    *   **失敗**: 提示錯誤訊息。
*   **移除商品按鈕 (購物車頁面)**:
    *   **功能**: 從購物車中移除指定商品。
    *   **觸發**: JavaScript `onclick` 事件，發送 AJAX 請求到 `api/cart.php` (DELETE)。
    *   **成功**: 移除商品行，更新購物車總計。
    *   **失敗**: 提示錯誤訊息。
*   **清空購物車按鈕 (購物車頁面)**:
    *   **功能**: 移除購物車中所有商品。
    *   **觸發**: JavaScript `onclick` 事件，發送 AJAX 請求到 `api/cart.php` (DELETE 或 POST)。
    *   **成功**: 清空購物車顯示，顯示空購物車提示。
    *   **失敗**: 提示錯誤訊息。
*   **前往結帳按鈕 (購物車頁面)**:
    *   **功能**: 導航到結帳頁面。
    *   **觸發**: JavaScript `onclick` 事件，導航到 `checkout.html` / `checkout.php`。
*   **提交訂單按鈕 (結帳頁面)**:
    *   **功能**: 提交訂單資訊到後端。
    *   **觸發**: 表單提交 (`<form method="POST">`) 或 JavaScript `onclick` 事件發送 AJAX 請求到 `api/orders.php` (POST)。
    *   **成功**: 導航到訂單確認頁或支付頁。
    *   **失敗**: 提示表單驗證錯誤或訂單提交失敗。
*   **登入/註冊按鈕 (登入/註冊頁面)**:
    *   **功能**: 提交用戶憑證進行身份驗證或帳號創建。
    *   **觸發**: 表單提交 (`<form method="POST">`) 或 JavaScript `onclick` 事件發送 AJAX 請求到 `api/auth.php` (POST)。
    *   **成功**: 導航到首頁或用戶個人資料頁。
    *   **失敗**: 提示錯誤訊息 (例如：帳號或密碼錯誤)。
*   **後台管理頁面中的增/刪/改按鈕**:
    *   **功能**: 執行對應資源 (商品、分類、用戶等) 的 CRUD 操作。
    *   **觸發**: 通常是表單提交 (`<form method="POST">`) 或 JavaScript `onclick` 事件發送 AJAX 請求到對應的後台 API (例如 `admin/api/products.php`，使用 POST/PUT/DELETE 方法)。
    *   **成功**: 刷新列表或顯示成功提示。
    *   **失敗**: 顯示錯誤提示。

## 4. API 接口說明

以下是常見的 API 接口及其功能，通常以 RESTful 風格設計：

### 4.1 商品相關 API

*   **獲取商品列表**
    *   **URL**: `/api/products.php`
    *   **方法**: `GET`
    *   **參數**: `category_id` (可選), `search` (可選), `page` (可選), `limit` (可選), `sort` (可選)
    *   **回應**: `JSON` 格式的商品列表。
*   **獲取商品詳情**
    *   **URL**: `/api/products.php?id={product_id}` 或 `/api/product_detail.php?id={product_id}`
    *   **方法**: `GET`
    *   **參數**: `id` (商品 ID)
    *   **回應**: `JSON` 格式的單一商品詳細資訊。

### 4.2 分類相關 API

*   **獲取分類列表**
    *   **URL**: `/api/categories.php`
    *   **方法**: `GET`
    *   **參數**: 無
    *   **回應**: `JSON` 格式的分類列表 (可能包含樹狀結構)。

### 4.3 購物車相關 API

*   **獲取購物車內容**
    *   **URL**: `/api/cart.php` 或 `/api/cart_get.php`
    *   **方法**: `GET`
    *   **參數**: 無 (基於 Session 或用戶 ID)
    *   **回應**: `JSON` 格式的購物車商品列表及總計。
*   **新增商品到購物車**
    *   **URL**: `/api/cart.php` 或 `/api/cart_add.php`
    *   **方法**: `POST`
    *   **請求體**: `JSON` 格式，例如 `{"product_id": 123, "quantity": 1}`
    *   **回應**: `JSON` 格式，表示操作成功或失敗。
*   **更新購物車商品數量**
    *   **URL**: `/api/cart.php` 或 `/api/cart_update.php`
    *   **方法**: `PUT`
    *   **請求體**: `JSON` 格式，例如 `{"product_id": 123, "quantity": 5}`
    *   **回應**: `JSON` 格式，表示操作成功或失敗。
*   **從購物車移除商品**
    *   **URL**: `/api/cart.php` 或 `/api/cart_remove.php`
    *   **方法**: `DELETE`
    *   **請求體**: `JSON` 格式，例如 `{"product_id": 123}`
    *   **回應**: `JSON` 格式，表示操作成功或失敗。
*   **清空購物車**
    *   **URL**: `/api/cart.php` 或 `/api/cart_clear.php`
    *   **方法**: `DELETE` 或 `POST`
    *   **請求體**: 無或空 `JSON`
    *   **回應**: `JSON` 格式，表示操作成功或失敗。

### 4.4 認證與用戶相關 API

*   **用戶登入**
    *   **URL**: `/api/auth.php` 或 `/api/login.php`
    *   **方法**: `POST`
    *   **請求體**: `JSON` 格式，例如 `{"username": "user", "password": "password"}`
    *   **回應**: `JSON` 格式，包含登入狀態和用戶資訊 (如果成功)。
*   **用戶註冊**
    *   **URL**: `/api/auth.php` 或 `/api/register.php`
    *   **方法**: `POST`
    *   **請求體**: `JSON` 格式，例如 `{"username": "new_user", "email": "email@example.com", "password": "password"}`
    *   **回應**: `JSON` 格式，表示註冊成功或失敗。
*   **獲取用戶個人資料**
    *   **URL**: `/api/user.php` 或 `/api/profile.php`
    *   **方法**: `GET`
    *   **參數**: 無 (基於 Session 或 Token)
    *   **回應**: `JSON` 格式的用戶個人資料。
*   **更新用戶個人資料**
    *   **URL**: `/api/user.php` 或 `/api/profile.php`
    *   **方法**: `PUT`
    *   **請求體**: `JSON` 格式，包含要更新的用戶資訊。
    *   **回應**: `JSON` 格式，表示更新成功或失敗。

### 4.5 後台管理 API (僅限管理員訪問)

*   **商品管理 API**
    *   **URL**: `/admin/api/products.php`
    *   **方法**: `GET` (列表), `POST` (新增), `PUT` (更新), `DELETE` (刪除)
    *   **請求體/參數**: 根據操作類型而定。
*   **分類管理 API**
    *   **URL**: `/admin/api/categories.php`
    *   **方法**: `GET` (列表), `POST` (新增), `PUT` (更新), `DELETE` (刪除)
    *   **請求體/參數**: 根據操作類型而定。
*   **用戶管理 API**
    *   **URL**: `/admin/api/users.php`
    *   **方法**: `GET` (列表), `POST` (新增), `PUT` (更新), `DELETE` (刪除)
    *   **請求體/參數**: 根據操作類型而定。
*   **訂單管理 API**
    *   **URL**: `/admin/api/orders.php`
    *   **方法**: `GET` (列表), `PUT` (更新狀態)
    *   **請求體/參數**: 根據操作類型而定。

---