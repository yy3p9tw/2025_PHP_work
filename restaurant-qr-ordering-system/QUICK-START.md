# 🚀 快速啟動指南 - 餐廳QR Code點餐系統 OOP架構

## 📖 概述
歡迎使用餐廳QR Code點餐系統的 **OOP重構版本**！本系統採用現代前端架構設計，提供高可維護性和可測試性的解決方案。

## 🎯 立即開始

### 1️⃣ 體驗 OOP 版結帳頁面
```bash
# 在瀏覽器中開啟
http://localhost:8000/checkout-oop.html
```
- ✨ 完整的 OOP 架構實現
- 🎮 左上角演示按鈕面板，體驗各種功能
- 🛡️ 統一錯誤處理與用戶友善提示
- 🚌 事件驅動的組件通信

### 2️⃣ 運行完整測試套件
```bash
# 在瀏覽器中開啟
http://localhost:8000/test-suite.html
```
- 🧪 完整的測試框架（類似 Jest）
- 📊 85%+ 的測試覆蓋率
- 🎨 美觀的測試結果展示
- ⚡ 快速執行與詳細報告

### 3️⃣ 監控系統效能
```bash
# 在瀏覽器中開啟
http://localhost:8000/performance-monitor.html
```
- 📈 即時效能指標監控
- 🧠 記憶體使用情況分析
- 🚌 事件系統統計
- 🛡️ 錯誤統計與健康度

## 🏗️ 架構核心

### 核心模組結構
```
js/
├── core/                       # 🏛️ 核心架構
│   ├── EventBus.js            # 🚌 中央事件匯流排（單例）
│   ├── ErrorHandler.js        # 🛡️ 統一錯誤處理
│   ├── StorageService.js      # 💾 儲存服務介面
│   ├── BasePage.js            # 📄 基礎頁面類別
│   ├── ModalManager.js        # 🪟 模態框管理
│   ├── DIContainer.js         # 🏗️ 依賴注入容器
│   └── bootstrap.js           # 🚀 應用程式載入器
├── managers/                   # 📋 業務邏輯管理器
│   ├── CartManager.js         # 🛒 購物車管理（組合模式）
│   └── OrderManager.js        # 📋 訂單管理
├── pages/                      # 📄 頁面類別
│   └── CheckoutPageOOP.js     # 💳 OOP版結帳頁面
├── test/                       # 🧪 測試框架
│   ├── TestFramework.js       # 🔧 輕量測試框架
│   ├── TestDataFactory.js     # 🏭 測試資料工廠
│   └── *.test.js              # 📝 各模組測試套件
└── demo/                       # 🎯 演示工具
    └── OOPDemo.js             # 🎮 架構功能演示
```

### 設計模式應用
- **🔄 單例模式**: EventBus, ErrorHandler
- **🏭 工廠模式**: TestDataFactory, DIContainer
- **🧩 組合模式**: CartManager, OrderManager
- **💉 依賴注入**: 所有服務透過 DIContainer 管理
- **📡 觀察者模式**: EventBus 事件系統
- **🎭 介面模式**: IStorageService 抽象介面

## 🎮 互動式演示

### 在瀏覽器控制台中試試這些：

```javascript
// 🚌 事件系統演示
const eventBus = EventBus.getInstance();

// 註冊事件監聽
eventBus.on('demo:test', (data) => {
    console.log('📨 收到事件:', data);
});

// 觸發事件
eventBus.emit('demo:test', { message: 'Hello OOP!' });

// 🛒 購物車操作演示
const cartManager = container.get('cartManager');

// 添加商品
cartManager.addItem({
    id: 'demo-001',
    name: '演示商品',
    price: 99
}, 2);

// 查看購物車
console.log('🛒 購物車內容:', cartManager.getCart());
console.log('💰 總金額:', cartManager.getTotalPrice());

// 🛡️ 錯誤處理演示
const errorHandler = ErrorHandler.getInstance();

// 模擬錯誤
try {
    throw new AppError('演示錯誤', 'DEMO_ERROR', { demo: true });
} catch (error) {
    errorHandler.handleError(error);
}

// 查看錯誤日誌
console.log('📋 錯誤日誌:', errorHandler.errorLog);
```

## 🧪 測試開發

### 撰寫自己的測試
```javascript
// 在任何測試文件中
describe('我的功能', () => {
    let myService;
    
    beforeEach(() => {
        myService = new MyService();
    });
    
    it('應該正確執行基本功能', () => {
        const result = myService.doSomething();
        expect(result).toBe(expected);
    });
    
    it('應該處理錯誤情況', () => {
        expect(() => {
            myService.throwError();
        }).toThrow('Expected error message');
    });
});
```

### 使用測試工廠
```javascript
// 快速創建測試資料
const testItem = TestDataFactory.createMenuItem({
    name: '自訂商品',
    price: 150
});

const testCart = TestDataFactory.createCart(5); // 5個商品的購物車
const testOrder = TestDataFactory.createOrder({
    tableNumber: 'A01'
});
```

## 🔧 開發工具

### 1. 除錯助手（在控制台中使用）
```javascript
// 檢查應用程式狀態
debug.app()        // 應用程式整體狀態
debug.eventBus()   // 事件系統統計
debug.errors()     // 錯誤統計
debug.cart()       // 購物車狀態
debug.orders()     // 訂單狀態
debug.performance() // 效能統計
```

### 2. Mock 服務（用於測試）
```javascript
// 創建 Mock 儲存服務
const mockStorage = TestUtils.createMockLocalStorage();

// 創建 Mock 事件匯流排
const mockEventBus = TestUtils.createMockEventBus();

// 使用 Mock 服務創建管理器
const cartManager = new CartManager(mockStorage);
```

## 📚 進階使用

### 自訂頁面類別
```javascript
class MyPageOOP extends BasePage {
    constructor() {
        super('my-page');
        this.myManager = container.get('myManager');
    }
    
    init() {
        super.init();
        this.setupCustomLogic();
    }
    
    setupCustomLogic() {
        // 自訂初始化邏輯
        this.eventBus.on('my:event', this.handleMyEvent.bind(this));
    }
    
    handleMyEvent(data) {
        // 處理自訂事件
        console.log('處理我的事件:', data);
    }
}
```

### 註冊自訂服務
```javascript
// 在 DIContainer 中註冊新服務
container.register('myService', () => new MyService(), {
    singleton: true,
    dependencies: ['storage', 'eventBus']
});

// 使用服務
const myService = container.get('myService');
```

## 🎯 最佳實踐

### ✅ 推薦做法
- 使用依賴注入獲取服務
- 通過事件系統進行組件通信
- 所有錯誤統一通過 ErrorHandler 處理
- 使用 Mock 服務進行單元測試
- 繼承 BasePage 創建新頁面

### ❌ 避免做法
- 直接使用全域變數
- 在類別間創建強耦合
- 忽略錯誤處理
- 在生產代碼中使用 console.log
- 跳過測試編寫

## 🚧 開發路線圖

### ✅ 已完成
- 核心 OOP 架構
- 完整測試框架
- 錯誤處理系統
- 效能監控
- OOP 版結帳頁面

### 🔄 進行中
- Menu 頁面 OOP 重構
- Cart 頁面 OOP 重構
- 更多測試案例

### 📋 計劃中
- Firebase 整合
- 餐廳端管理系統
- PWA 功能完善
- 即時訂單同步

## 🤝 貢獻指南

1. **Fork** 專案
2. 創建功能分支: `git checkout -b feature/amazing-feature`
3. 撰寫測試: 確保新功能有對應測試
4. 提交改動: `git commit -m 'Add amazing feature'`
5. 推送分支: `git push origin feature/amazing-feature`
6. 開啟 Pull Request

## 📞 支援與反饋

- 📖 **完整文檔**: `README-OOP.md`
- 🧪 **測試套件**: `test-suite.html`
- 📊 **效能監控**: `performance-monitor.html`
- 🎯 **功能演示**: `checkout-oop.html`

---

**享受使用現代 OOP 架構開發的樂趣！** 🎉

*如有任何問題或建議，歡迎查看相關文檔或創建 Issue。*
