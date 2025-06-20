// OOP 架構測試套件
// 展示新架構的功能和使用方式

class OOPArchitectureDemo {
    constructor() {
        this.container = window.container;
        this.eventBus = null;
        this.cartManager = null;
        this.orderManager = null;
        this.modalManager = null;
        
        console.log('🎯 OOP 架構演示初始化...');
    }
    
    async init() {
        try {
            // 等待應用程式初始化完成
            await this.waitForApp();
            
            // 獲取服務
            this.eventBus = this.container.get('eventBus');
            this.cartManager = this.container.get('cartManager');
            this.orderManager = this.container.get('orderManager');
            this.modalManager = this.container.get('modalManager');
            
            console.log('✅ OOP 架構演示準備完成');
            
            // 設置演示按鈕
            this.setupDemoButtons();
            
        } catch (error) {
            console.error('❌ OOP 架構演示初始化失敗:', error);
        }
    }
    
    async waitForApp() {
        return new Promise((resolve) => {
            if (window.app && window.app.isInitialized) {
                resolve();
            } else {
                const checkInterval = setInterval(() => {
                    if (window.app && window.app.isInitialized) {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
            }
        });
    }
    
    setupDemoButtons() {
        // 創建演示按鈕容器
        const demoContainer = document.createElement('div');
        demoContainer.id = 'oop-demo';
        demoContainer.style.cssText = `
            position: fixed;
            top: 10px;
            left: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 9998;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 300px;
        `;
        
        demoContainer.innerHTML = `
            <h6 style="margin: 0 0 10px 0; color: #495057;">
                🎯 OOP 架構演示
                <button onclick="this.parentElement.parentElement.style.display='none'" 
                        style="float: right; border: none; background: none; font-size: 18px; cursor: pointer;">×</button>
            </h6>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button onclick="oopDemo.demoEventBus()" class="demo-btn">事件系統演示</button>
                <button onclick="oopDemo.demoCartManager()" class="demo-btn">購物車管理演示</button>
                <button onclick="oopDemo.demoOrderManager()" class="demo-btn">訂單管理演示</button>
                <button onclick="oopDemo.demoErrorHandler()" class="demo-btn">錯誤處理演示</button>
                <button onclick="oopDemo.demoModalManager()" class="demo-btn">模態框管理演示</button>
                <button onclick="oopDemo.demoPerformance()" class="demo-btn">效能監控演示</button>
                <button onclick="oopDemo.showStats()" class="demo-btn">系統統計</button>
            </div>
        `;
        
        // 添加按鈕樣式
        const style = document.createElement('style');
        style.textContent = `
            .demo-btn {
                padding: 6px 10px;
                border: 1px solid #007bff;
                background: #007bff;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.2s;
            }
            .demo-btn:hover {
                background: #0056b3;
                border-color: #0056b3;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(demoContainer);
        
        // 設置全域參照
        window.oopDemo = this;
    }
    
    // 事件系統演示
    demoEventBus() {
        console.group('🚌 事件系統演示');
        
        // 註冊事件監聽器
        const listener1 = this.eventBus.on('demo:test', (data) => {
            console.log('監聽器 1 收到事件:', data);
        });
        
        const listener2 = this.eventBus.on('demo:test', (data) => {
            console.log('監聽器 2 收到事件:', data);
        }, { priority: 10 }); // 高優先級
        
        // 一次性監聽器
        this.eventBus.on('demo:test', (data) => {
            console.log('一次性監聽器收到事件:', data);
        }, { once: true });
        
        // 觸發事件
        this.eventBus.emit('demo:test', { message: '這是測試事件', timestamp: Date.now() });
        
        // 再次觸發（一次性監聽器不會執行）
        this.eventBus.emit('demo:test', { message: '第二次觸發', timestamp: Date.now() });
        
        // 移除監聽器
        this.eventBus.off('demo:test', listener1);
        console.log('移除監聽器 1');
        
        // 第三次觸發
        this.eventBus.emit('demo:test', { message: '第三次觸發', timestamp: Date.now() });
        
        // 清理
        this.eventBus.off('demo:test');
        
        console.log('事件系統統計:', this.eventBus.getStats());
        console.groupEnd();
    }
    
    // 購物車管理演示
    demoCartManager() {
        console.group('🛒 購物車管理演示');
        
        try {
            // 清空購物車
            this.cartManager.clearCart();
            
            // 添加商品
            const item1 = {
                id: 'demo001',
                name: '演示牛肉麵',
                price: 150,
                image: 'images/demo.jpg',
                category: 'main'
            };
            
            const item2 = {
                id: 'demo002',
                name: '演示珍珠奶茶',
                price: 60,
                image: 'images/demo.jpg',
                category: 'drink'
            };
            
            console.log('添加商品到購物車...');
            this.cartManager.addItem(item1, 2);
            this.cartManager.addItem(item2, 1);
            
            console.log('當前購物車:', this.cartManager.getCartSummary());
            
            // 更新數量
            console.log('更新商品數量...');
            this.cartManager.updateQuantity('demo001', 3);
            
            console.log('更新後購物車:', this.cartManager.getCartSummary());
            
            // 移除商品
            console.log('移除商品...');
            this.cartManager.removeItem('demo002');
            
            console.log('最終購物車:', this.cartManager.getCartSummary());
            console.log('購物車統計:', this.cartManager.getStats());
            
        } catch (error) {
            console.error('購物車演示錯誤:', error);
        }
        
        console.groupEnd();
    }
    
    // 訂單管理演示
    demoOrderManager() {
        console.group('📋 訂單管理演示');
        
        try {
            // 先確保購物車有商品
            const cartItems = this.cartManager.getCart();
            
            if (cartItems.length === 0) {
                console.log('購物車是空的，先添加演示商品...');
                this.cartManager.addItem({
                    id: 'demo003',
                    name: '演示訂單商品',
                    price: 200,
                    image: 'images/demo.jpg',
                    category: 'main'
                }, 1);
            }
            
            // 創建訂單
            console.log('創建訂單...');
            const order = this.orderManager.createOrder(
                this.cartManager.getCart(),
                'A99', // 演示座號
                { customerNote: '這是演示訂單' }
            );
            
            console.log('創建的訂單:', order);
            
            // 提交訂單
            console.log('提交訂單...');
            this.orderManager.submitOrder(order, 'cash', '演示備註').then(submittedOrder => {
                console.log('提交成功的訂單:', submittedOrder);
                console.log('訂單歷史:', this.orderManager.getOrderHistory());
                console.log('訂單統計:', this.orderManager.getOrderStats());
            });
            
        } catch (error) {
            console.error('訂單演示錯誤:', error);
        }
        
        console.groupEnd();
    }
    
    // 錯誤處理演示
    demoErrorHandler() {
        console.group('🛡️ 錯誤處理演示');
        
        const errorHandler = this.container.get('errorHandler');
        
        try {
            // 創建自定義錯誤
            const customError = errorHandler.createError(
                '這是演示錯誤',
                ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                { demo: true, timestamp: Date.now() }
            );
            
            console.log('創建的自定義錯誤:', customError);
            
            // 處理同步錯誤
            try {
                throw new Error('演示同步錯誤');
            } catch (error) {
                errorHandler.handleError(error, { source: 'demo', type: 'sync' });
            }
            
            // 處理異步錯誤
            const failingPromise = Promise.reject(new Error('演示異步錯誤'));
            errorHandler.handleAsyncError(failingPromise, { source: 'demo', type: 'async' })
                .catch(() => {
                    console.log('異步錯誤已被處理');
                });
            
            // 顯示錯誤統計
            setTimeout(() => {
                console.log('錯誤統計:', errorHandler.getErrorStats());
            }, 100);
            
        } catch (error) {
            console.error('錯誤處理演示失敗:', error);
        }
        
        console.groupEnd();
    }
    
    // 模態框管理演示
    demoModalManager() {
        console.group('🗂️ 模態框管理演示');
        
        // 顯示確認對話框
        this.modalManager.confirm('這是演示確認對話框，您確定要繼續嗎？', {
            title: '演示確認',
            confirmText: '繼續',
            cancelText: '取消'
        }).then(result => {
            console.log('確認對話框結果:', result);
            
            if (result) {
                // 顯示警告對話框
                this.modalManager.alert('您選擇了繼續！這是演示警告對話框。', {
                    title: '演示警告',
                    okText: '知道了'
                }).then(() => {
                    console.log('警告對話框已關閉');
                });
            }
        });
        
        console.log('模態框統計:', this.modalManager.getStats());
        console.groupEnd();
    }
    
    // 效能監控演示
    async demoPerformance() {
        console.group('⏱️ 效能監控演示');
        
        const performanceMonitor = this.container.get('performanceMonitor');
        
        // 測量同步操作
        await performanceMonitor.measure('sync_operation', () => {
            console.log('執行同步操作...');
            // 模擬 CPU 密集操作
            const start = Date.now();
            while (Date.now() - start < 50) {
                Math.random();
            }
        });
        
        // 測量異步操作
        await performanceMonitor.measure('async_operation', async () => {
            console.log('執行異步操作...');
            return new Promise(resolve => {
                setTimeout(() => {
                    console.log('異步操作完成');
                    resolve();
                }, 100);
            });
        });
        
        // 測量失敗操作
        try {
            await performanceMonitor.measure('failing_operation', async () => {
                throw new Error('演示操作失敗');
            });
        } catch (error) {
            console.log('捕獲到預期的錯誤:', error.message);
        }
        
        console.log('效能統計:', performanceMonitor.getStats());
        console.groupEnd();
    }
    
    // 顯示系統統計
    showStats() {
        console.group('📊 系統統計資訊');
        
        console.log('🚌 事件系統:', this.eventBus.getStats());
        console.log('🛒 購物車:', this.cartManager.getStats());
        console.log('📋 訂單:', this.orderManager.getOrderStats());
        console.log('🛡️ 錯誤處理:', this.container.get('errorHandler').getErrorStats());
        console.log('🗂️ 模態框:', this.modalManager.getStats());
        console.log('⏱️ 效能監控:', this.container.get('performanceMonitor').getStats());
        console.log('🏗️ 依賴注入:', this.container.getStats());
        console.log('📱 應用程式:', window.app.getStatus());
        
        console.groupEnd();
    }
}

// 當頁面載入完成且應用程式初始化後，自動啟動演示
document.addEventListener('DOMContentLoaded', () => {
    // 延遲啟動以確保所有模組載入完成
    setTimeout(async () => {
        try {
            window.oopArchitectureDemo = new OOPArchitectureDemo();
            await window.oopArchitectureDemo.init();
            
            console.log(`
                🎉 OOP 架構演示準備完成！
                
                查看左上角的演示按鈕面板，您可以：
                1. 測試事件系統的監聽和觸發
                2. 演示購物車的增刪改查操作
                3. 體驗訂單的創建和提交流程
                4. 觀察錯誤處理機制
                5. 試用模態框管理功能
                6. 監控系統效能表現
                
                或者直接在控制台使用：
                - window.debug.* 系列函數
                - window.oopDemo.* 演示函數
                
                享受新的 OOP 架構吧！ 🚀
            `);
            
        } catch (error) {
            console.error('❌ OOP 架構演示啟動失敗:', error);
        }
    }, 1000);
});

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { OOPArchitectureDemo };
} else {
    window.OOPArchitectureDemo = OOPArchitectureDemo;
}
