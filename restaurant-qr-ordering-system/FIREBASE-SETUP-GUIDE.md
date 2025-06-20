# 🔥 Firebase 整合使用指南

## 📋 快速開始

### 1. 設定您的 Firebase 專案

#### Step 1: 建立 Firebase 專案
1. 前往 [Firebase Console](https://console.firebase.google.com/)
2. 點擊「建立專案」
3. 專案名稱：`restaurant-qr-system`
4. 選擇是否啟用 Google Analytics（建議：否）
5. 點擊「建立專案」

#### Step 2: 設定 Firestore 資料庫
1. 左側選單 → 「Firestore Database」
2. 點擊「建立資料庫」
3. 選擇「測試模式」（開發期間）
4. 選擇位置：`asia-east1` (台灣)

#### Step 3: 新增 Web 應用程式
1. 專案設定 → 「您的應用程式」
2. 點擊 Web 圖示 `</>`
3. 應用程式名稱：`restaurant-qr-web`
4. 複製 `firebaseConfig` 設定

---

## 🔧 專案設定

### 1. 更新 Firebase 設定

#### 需要更新的檔案：
- `js/config/firebase-config.js`
- `checkout.html`
- `order-history.html`
- `firebase-test.html`

#### 設定步驟：
```javascript
// 將您從 Firebase Console 複製的設定貼上：
const firebaseConfig = {
  apiKey: "您的-API-KEY",
  authDomain: "您的專案.firebaseapp.com",
  projectId: "您的專案ID",
  storageBucket: "您的專案.appspot.com",
  messagingSenderId: "您的ID",
  appId: "您的APP-ID"
};
```

### 2. 測試 Firebase 連線

#### 使用測試頁面：
1. 開啟 `firebase-test.html`
2. 檢查連線狀態
3. 測試訂單儲存
4. 測試訂單讀取
5. 測試即時監聽

---

## 📱 功能說明

### 1. 混合模式運作
- ✅ **有 Firebase**：訂單儲存到雲端 + 本地備份
- ✅ **無 Firebase**：訂單儲存到本地儲存
- ✅ **Firebase 失敗**：自動回退到本地模式

### 2. 訂單流程

#### 顧客端 (checkout.html)：
```javascript
// 檢查 Firebase 是否可用
if (this.isFirebaseEnabled) {
    // 提交到 Firebase
    await this.submitToFirebase(orderData);
} else {
    // 提交到本地儲存
    await this.submitToLocal(orderData);
}
```

#### 訂單查詢 (order-history.html)：
```javascript
// 優先從 Firebase 載入
if (this.isFirebaseEnabled) {
    orders = await this.loadFromFirebase();
} else {
    orders = this.loadFromLocal();
}
```

### 3. 資料結構

#### Firestore 集合：
- `orders` - 訂單資料
- `menu` - 菜單資料（預留）

#### 訂單文件結構：
```javascript
{
  orderNumber: "ORD20250620001",
  tableNumber: "A01",
  customerName: "顧客姓名",
  items: [
    {
      id: 1,
      name: "牛肉麵",
      price: 200,
      quantity: 1
    }
  ],
  totalAmount: 200,
  paymentMethod: "cash",
  orderStatus: "pending",
  paymentStatus: "pending",
  note: "不要辣椒",
  createdAt: serverTimestamp(),
  updatedAt: serverTimestamp()
}
```

---

## 🎯 測試流程

### 1. 基本測試
1. 開啟 `firebase-test.html`
2. 確認連線狀態為「✅ 已連線到 Firebase」
3. 測試儲存訂單功能
4. 檢查 Firebase Console 中的 Firestore 資料

### 2. 整合測試
1. 前往 `menu.html` 選購商品
2. 進入 `checkout.html` 結帳
3. 提交訂單
4. 前往 `order-history.html` 查看訂單

### 3. 無網路測試
1. 關閉網路連線
2. 重複上述流程
3. 確認系統自動回退到本地模式

---

## 🔍 除錯指南

### 1. 常見問題

#### Firebase 初始化失敗：
```
❌ Firebase 初始化失敗: FirebaseError: Firebase: No Firebase App...
```
**解決方法**：檢查 `firebaseConfig` 設定是否正確

#### 權限錯誤：
```
❌ Missing or insufficient permissions
```
**解決方法**：確認 Firestore 規則設為測試模式

#### 網路連線問題：
```
❌ Firebase 載入失敗，回退到本地儲存
```
**說明**：這是正常行為，系統會自動處理

### 2. 除錯工具

#### 瀏覽器開發者工具：
- **Console**：查看詳細錯誤訊息
- **Network**：檢查 Firebase API 請求
- **Application**：查看 localStorage 資料

#### Firebase Console：
- **Firestore**：查看儲存的訂單資料
- **使用量**：監控 API 使用情況

---

## 📊 費用控制

### Firebase 免費額度 (Spark 方案)：
- **讀取**：50,000 次/天
- **寫入**：20,000 次/天
- **儲存**：1GB
- **傳輸**：10GB/月

### 小型餐廳預估使用量：
- **每日 100 筆訂單**：
  - 寫入：100 次
  - 讀取：~1,000 次
- **完全在免費額度內** ✅

---

## 🚀 進階功能 (未來擴充)

### 1. 餐廳管理端
- 即時訂單通知
- 訂單狀態管理
- 菜單動態更新

### 2. 推播通知
- 訂單狀態變更通知
- 優惠活動推送

### 3. 資料分析
- 銷售統計
- 熱門商品分析
- 營業報表

---

## 📝 檔案清單

### 新增的檔案：
- `js/config/firebase-config.js` - Firebase 設定
- `js/services/FirebaseService.js` - Firebase 服務封裝
- `firebase-test.html` - Firebase 測試頁面
- `order-history.html` - 訂單歷史頁面

### 修改的檔案：
- `checkout.html` - 加入 Firebase 支援
- `js/checkout.js` - 整合 Firebase 提交功能

---

## 🎉 完成檢查清單

- [ ] Firebase 專案建立完成
- [ ] Firestore 資料庫設定完成
- [ ] firebaseConfig 設定更新
- [ ] firebase-test.html 測試通過
- [ ] checkout.html 訂單提交測試
- [ ] order-history.html 訂單查詢測試
- [ ] 離線模式測試通過

**恭喜！您已成功整合 Firebase 到餐廳點餐系統！** 🎉

---

*📅 建立日期：2025年6月20日*  
*👨‍💻 建立者：GitHub Copilot*  
*🎯 版本：v6.0 Firebase 整合版*
