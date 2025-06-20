# 🍽️ 餐廳 QR Code 點餐系統 - OOP 重構版

## 📋 專案概述

這是餐廳 QR Code 點餐系統的 **OOP 重構版本**，採用現代前端架構設計原則，提供更好的可維護性、可測試性和擴展性。

## 🏗️ 架構特點

### 核心設計原則
- **組合優於繼承**：使用 CartManager、OrderManager 等管理器類別
- **依賴注入**：透過 DIContainer 管理服務依賴關係
- **事件驅動**：EventBus 實現解耦的組件通信
- **統一錯誤處理**：ErrorHandler 提供全域錯誤管理
- **單一職責**：每個類別專注單一功能

### 技術架構

```
📁 js/
├── 📂 core/                    # 核心架構模組
│   ├── EventBus.js            # 事件系統 (單例模式)
│   ├── ErrorHandler.js        # 錯誤處理 (單例模式)
│   ├── StorageService.js      # 儲存服務介面
│   ├── BasePage.js            # 基礎頁面類別
│   ├── ModalManager.js        # 模態框管理
│   ├── DIContainer.js         # 依賴注入容器
│   └── bootstrap.js           # 應用程式啟動器
├── 📂 managers/                # 業務邏輯管理器
│   ├── CartManager.js         # 購物車管理 (組合模式)
│   └── OrderManager.js        # 訂單管理 (組合模式)
├── 📂 pages/                   # 頁面類別
│   └── CheckoutPageOOP.js     # 重構後的結帳頁面
└── 📂 demo/                    # 演示工具
    └── OOPDemo.js             # 架構演示與測試
```

## 🚀 快速開始

### 1. 開啟 OOP 版本頁面
```bash
# 在瀏覽器中開啟
checkout-oop.html
```

### 2. 查看演示功能
頁面載入後，左上角會出現演示按鈕面板，您可以：
- 測試事件系統
- 演示購物車管理
- 體驗訂單處理
- 觀察錯誤處理
- 試用模態框管理
- 監控系統效能

### 3. 開發除錯
在瀏覽器控制台中使用除錯工具：
```javascript
// 應用程式狀態
debug.app()

// 事件系統統計
debug.eventBus()

// 錯誤統計
debug.errors()

// 購物車統計
debug.cart()

// 訂單統計
debug.orders()

// 效能統計
debug.performance()
```

## 🧪 核心功能演示

### 事件系統 (EventBus)
```javascript
// 註冊事件監聽器
eventBus.on('cart:item-added', (data) => {
    console.log('商品已加入購物車:', data);
});

// 觸發事件
eventBus.emit('cart:item-added', { item, quantity });

// 防抖處理
const debouncedHandler = eventBus.debounce(handler, 300);
```

### 購物車管理 (CartManager)
```javascript
const cartManager = new CartManager(storageService);

// 添加商品
cartManager.addItem(item, quantity);

// 更新數量
cartManager.updateQuantity(itemId, newQuantity);

// 獲取摘要
const summary = cartManager.getCartSummary();
```

### 訂單管理 (OrderManager)
```javascript
const orderManager = new OrderManager(storageService);

// 創建訂單
const order = orderManager.createOrder(cartItems, tableNumber);

// 提交訂單
const submittedOrder = await orderManager.submitOrder(order, paymentMethod, note);
```

### 錯誤處理 (ErrorHandler)
```javascript
const errorHandler = ErrorHandler.getInstance();

// 同步錯誤處理
try {
    // 業務邏輯
} catch (error) {
    errorHandler.handleError(error, { context: 'operation' });
}

// 異步錯誤處理
const result = await errorHandler.handleAsyncError(
    someAsyncOperation(),
    { context: 'async_operation' }
);
```

### 依賴注入 (DIContainer)
```javascript
// 註冊服務
container.register('cartManager', () => new CartManager(storage), {
    singleton: true,
    dependencies: ['storage']
});

// 獲取服務
const cartManager = container.get('cartManager');
```

## 🎯 設計模式應用

### 1. 單例模式 (Singleton)
- EventBus
- ErrorHandler

### 2. 工廠模式 (Factory)
- DIContainer 服務創建
- 測試資料工廠

### 3. 觀察者模式 (Observer)
- EventBus 事件系統

### 4. 策略模式 (Strategy)
- 不同儲存服務實現
- 錯誤處理策略

### 5. 組合模式 (Composition)
- CartManager, OrderManager
- 優於繼承的設計選擇

## 🧪 測試支援

### Mock 服務
```javascript
// 測試用 Mock 儲存
const mockStorage = new MockStorageService({
    'cart': [{ id: 'test1', name: '測試商品', price: 100, quantity: 1 }]
});

// 創建測試用購物車管理器
const cartManager = new CartManager(mockStorage);
```

### 測試工廠
```javascript
// 創建測試商品
const testItem = TestDataFactory.createMenuItem({
    name: '自定義測試商品',
    price: 200
});

// 創建測試訂單
const testOrder = TestDataFactory.createOrder({
    tableNumber: 'TEST01'
});
```

## 📊 效能監控

系統內建效能監控功能：
- 頁面載入時間追蹤
- 操作執行時間測量
- 記憶體使用量監控
- 錯誤發生率統計

```javascript
// 測量操作效能
const result = await performanceMonitor.measure('operation_name', async () => {
    // 要測量的操作
    return await someOperation();
});
```

## 🛡️ 錯誤處理機制

### 分層錯誤處理
1. **業務邏輯層**：特定錯誤代碼和上下文
2. **UI 層**：用戶友善的錯誤訊息
3. **系統層**：全域錯誤監聽和記錄

### 錯誤類型
- 驗證錯誤 (`VALIDATION.*`)
- 網路錯誤 (`NETWORK.*`)
- 資料錯誤 (`DATA.*`)
- 系統錯誤 (`SYSTEM.*`)

## 🔧 開發指南

### 創建新頁面
```javascript
class NewPage extends BasePage {
    constructor(dependencies = {}) {
        super('new-page', dependencies);
        // 頁面特定初始化
    }
    
    async loadData() {
        // 載入頁面資料
    }
    
    async render() {
        // 渲染頁面內容
    }
    
    setupPageSpecificEventListeners() {
        // 設置頁面專用事件監聽器
    }
}
```

### 添加新服務
```javascript
// 在 DIContainer 中註冊
container.register('newService', () => new NewService(), {
    singleton: true,
    dependencies: ['storage', 'eventBus']
});
```

### 擴展事件系統
```javascript
// 在 EVENTS 常數中添加新事件
const EVENTS = {
    // 現有事件...
    NEW_MODULE: {
        STARTED: 'new-module:started',
        COMPLETED: 'new-module:completed'
    }
};
```

## 📝 與舊版本比較

| 特性 | 舊版本 | OOP 重構版 |
|------|--------|------------|
| **架構** | 程序式 + 全域函數 | OOP + 設計模式 |
| **依賴管理** | 全域變數 | 依賴注入容器 |
| **錯誤處理** | 分散處理 | 統一錯誤管理 |
| **事件通信** | 直接調用 | 事件驅動架構 |
| **測試支援** | 困難 | Mock 和工廠支援 |
| **可維護性** | 低 | 高 |
| **可擴展性** | 低 | 高 |

## 🎉 總結

新的 OOP 架構提供了：
- ✅ **更好的代碼組織**：清晰的模組分離
- ✅ **提升的可測試性**：依賴注入和 Mock 支援
- ✅ **統一的錯誤處理**：全域錯誤管理機制
- ✅ **靈活的事件系統**：解耦的組件通信
- ✅ **內建效能監控**：系統性能追蹤
- ✅ **完整的除錯工具**：開發友善的除錯介面

這個重構版本為未來的功能擴展（如 Firebase 整合、餐廳端管理系統）奠定了堅實的架構基礎。

---

**版本：3.0 (OOP 重構版)**  
**日期：2025年6月20日**  
**狀態：架構重構完成，準備生產部署**
