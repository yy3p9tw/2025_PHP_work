# 🍽️ 餐廳QR Code點餐系統

一個現代化的餐廳點餐系統，讓顧客透過掃描QR Code或輸入座號進行線上點餐。

## 📋 專案特色

- 🔥 **Firebase整合** - 即時資料同步
- 📱 **PWA支援** - 可安裝到手機桌面
- 🎨 **響應式設計** - 手機優先，適配所有裝置
- 🌈 **暖色系UI** - 溫馨的用餐體驗
- ⚡ **高效能** - 快速載入，流暢操作
- 🔄 **離線支援** - 網路中斷時仍可使用基本功能

## 🚀 快速開始

### 1. 複製專案
```bash
git clone <repository-url>
cd restaurant-qr-ordering-system
```

### 2. Firebase設定

1. 前往 [Firebase Console](https://console.firebase.google.com)
2. 建立新專案或選擇現有專案
3. 啟用以下服務：
   - **Firestore Database** (資料庫)
   - **Storage** (圖片儲存)
   - **Hosting** (網站託管)
4. 在專案設定中獲取配置物件
5. 將配置資訊填入 `js/firebase-config.js`

### 3. 本地開發

```bash
# 使用任何靜態檔案伺服器
# 例如：Python
python -m http.server 8000

# 或者：Node.js
npx serve .

# 或者：PHP
php -S localhost:8000
```

### 4. 開啟瀏覽器
```
http://localhost:8000
```

## 📁 專案結構

```
restaurant-qr-ordering-system/
├── index.html              # 首頁 - 座號輸入
├── menu.html              # 菜單頁面 (待建立)
├── cart.html              # 購物車頁面 (待建立)
├── manifest.json          # PWA設定
├── sw.js                  # Service Worker
├── css/
│   └── styles.css         # 全域樣式
├── js/
│   ├── firebase-config.js # Firebase設定
│   └── app.js            # 主要應用邏輯
├── images/               # 圖片資源 (待添加)
└── admin/               # 管理端 (待建立)
    └── login.html
```

## 🛠️ 技術堆疊

- **前端**: HTML5, CSS3, JavaScript (ES6+)
- **後端**: Firebase (Firestore, Storage, Hosting)
- **PWA**: Service Worker, Web App Manifest
- **樣式**: 自定義CSS框架，響應式設計

## 📱 功能模組

### 顧客端功能
- [x] 座號輸入與驗證
- [x] PWA支援與離線功能
- [ ] 菜單瀏覽與搜尋
- [ ] 購物車管理
- [ ] 訂單確認與提交
- [ ] 訂單狀態追蹤
- [ ] 歷史訂單查詢

### 餐廳端功能
- [ ] 管理員登入
- [ ] 菜單管理 (CRUD)
- [ ] 即時訂單接收
- [ ] 訂單狀態更新
- [ ] 基本統計報表

## 🎨 設計規範

### 色彩系統
```css
--primary-color: #FF6B35;      /* 主橙色 */
--secondary-color: #D32F2F;    /* 輔助紅色 */
--accent-color: #FFA726;       /* 強調色 */
--background: #FAFAFA;         /* 背景色 */
--surface: #FFFFFF;            /* 表面色 */
```

### 響應式斷點
- 手機: < 768px
- 平板: 768px - 1024px  
- 桌面: > 1024px

## 🔧 開發指南

### 座號驗證規則
支援以下格式：
- 數字: `1` - `99`
- 字母+數字: `A1` - `Z99`
- 字母+兩位數字: `A01` - `Z99`

### Firebase資料結構

#### 菜單集合 (menu)
```javascript
{
  id: "menu_001",
  name: "招牌牛肉麵",
  description: "精選牛肉，香濃湯頭",
  price: 180,
  category: "主食",
  image: "https://...",
  available: true,
  createdAt: timestamp,
  updatedAt: timestamp
}
```

#### 訂單集合 (orders)
```javascript
{
  id: "order_001",
  tableNumber: "A05",
  items: [...],
  totalAmount: 360,
  paymentMethod: "cash",
  status: "pending",
  createdAt: timestamp,
  updatedAt: timestamp
}
```

## 📈 效能優化

- **圖片優化**: WebP格式，響應式圖片
- **程式碼分割**: 按需載入
- **快取策略**: Service Worker快取
- **CDN加速**: Firebase Hosting

## 🧪 測試

### 手動測試檢查清單
- [ ] 座號輸入驗證
- [ ] PWA安裝功能
- [ ] 離線模式運作
- [ ] 響應式設計
- [ ] 跨瀏覽器相容性

### 支援的瀏覽器
- Chrome 80+
- Safari 13+
- Firefox 75+
- Edge 80+

## 🚀 部署

### Firebase Hosting 部署
```bash
# 安裝 Firebase CLI
npm install -g firebase-tools

# 登入 Firebase
firebase login

# 初始化專案
firebase init hosting

# 部署
firebase deploy
```

### 部署檢查清單
- [ ] Firebase設定正確
- [ ] 圖片資源已上傳
- [ ] PWA圖標齊全
- [ ] 安全規則設定
- [ ] 自定義域名設定 (選擇性)

## 🤝 開發團隊

- **開發者**: YU
- **設計參考**: 麥味登APP點餐系統
- **開發時間**: 2025年6月

## 📄 授權

此專案僅供學習與練習使用。

## 🆘 常見問題

### Q: 如何更新Firebase配置？
A: 編輯 `js/firebase-config.js` 檔案，替換為您的專案配置。

### Q: PWA如何安裝到手機？
A: 在支援的瀏覽器中開啟網站，會出現「加入主畫面」提示。

### Q: 離線模式有哪些限制？
A: 離線模式僅支援瀏覽快取的頁面，無法同步新資料。

### Q: 如何自定義座號格式？
A: 修改 `js/app.js` 中的 `isValidTableNumber` 函數。

---

**版本**: 1.0  
**最後更新**: 2025年6月20日  
**狀態**: 開發中 (Phase 1 完成)
