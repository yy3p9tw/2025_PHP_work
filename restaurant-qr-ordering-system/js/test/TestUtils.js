// 測試工具類 - 提供測試中常用的工具函數

class TestUtils {
    /**
     * 清理測試環境
     */
    static cleanup() {
        // 清理 localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.clear();
        }
        
        // 清理 sessionStorage
        if (typeof sessionStorage !== 'undefined') {
            sessionStorage.clear();
        }
        
        // 清理事件監聽器
        if (typeof window !== 'undefined' && window.eventBus) {
            if (typeof window.eventBus.clearAllListeners === 'function') {
                window.eventBus.clearAllListeners();
            }
        }
        
        // 清理 DOM
        const testElements = document.querySelectorAll('[data-test]');
        testElements.forEach(element => element.remove());
    }
    
    /**
     * 延遲執行
     * @param {number} ms - 延遲毫秒數
     * @returns {Promise}
     */
    static sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * 創建模擬的 EventBus
     * @returns {object} Mock EventBus
     */
    static createMockEventBus() {
        const events = new Map();
        
        return {
            on: jest.fn((event, callback) => {
                if (!events.has(event)) {
                    events.set(event, []);
                }
                events.get(event).push(callback);
            }),
            
            off: jest.fn((event, callback) => {
                if (events.has(event)) {
                    const callbacks = events.get(event);
                    const index = callbacks.indexOf(callback);
                    if (index > -1) {
                        callbacks.splice(index, 1);
                    }
                }
            }),
            
            emit: jest.fn((event, data) => {
                if (events.has(event)) {
                    events.get(event).forEach(callback => {
                        try {
                            callback(data);
                        } catch (error) {
                            console.error('EventBus callback error:', error);
                        }
                    });
                }
            }),
            
            once: jest.fn((event, callback) => {
                const onceCallback = (data) => {
                    callback(data);
                    this.off(event, onceCallback);
                };
                this.on(event, onceCallback);
            }),
            
            clear: jest.fn((event) => {
                if (event) {
                    events.delete(event);
                } else {
                    events.clear();
                }
            }),
            
            getEventStats: jest.fn(() => ({
                totalEvents: events.size,
                events: Array.from(events.keys()),
                totalListeners: Array.from(events.values()).reduce((total, callbacks) => total + callbacks.length, 0)
            })),
            
            clearAllListeners: jest.fn(() => {
                events.clear();
            }),
            
            // 內部狀態用於測試驗證
            _events: events
        };
    }
    
    /**
     * 創建模擬的 DOM 元素
     * @param {string} tagName - 標籤名稱
     * @param {object} attributes - 屬性物件
     * @param {string} innerHTML - 內部 HTML
     * @returns {HTMLElement} DOM 元素
     */
    static createElement(tagName = 'div', attributes = {}, innerHTML = '') {
        const element = document.createElement(tagName);
        
        Object.entries(attributes).forEach(([key, value]) => {
            element.setAttribute(key, value);
        });
        
        if (innerHTML) {
            element.innerHTML = innerHTML;
        }
        
        // 標記為測試元素
        element.setAttribute('data-test', 'true');
        
        return element;
    }
    
    /**
     * 創建模擬的購物車數據
     * @returns {object} 購物車數據
     */
    static createMockCartData() {
        return {
            items: [
                {
                    id: 1,
                    name: '測試商品1',
                    price: 100,
                    quantity: 2,
                    image: 'test1.jpg',
                    category: '主食'
                },
                {
                    id: 2,
                    name: '測試商品2',
                    price: 50,
                    quantity: 1,
                    image: 'test2.jpg',
                    category: '飲品'
                }
            ],
            total: 250,
            itemCount: 3
        };
    }
    
    /**
     * 創建模擬的訂單數據
     * @returns {object} 訂單數據
     */
    static createMockOrderData() {
        return {
            id: 'TEST_ORDER_001',
            tableNumber: 'A01',
            items: this.createMockCartData().items,
            total: 250,
            paymentMethod: 'cash',
            customerInfo: {
                name: '測試客戶',
                phone: '0912345678',
                note: '測試訂單'
            },
            status: 'pending',
            timestamp: new Date().toISOString()
        };
    }
    
    /**
     * 模擬 API 響應
     * @param {*} data - 響應數據
     * @param {number} delay - 延遲時間
     * @param {boolean} shouldReject - 是否拒絕
     * @returns {Promise}
     */
    static mockApiResponse(data, delay = 0, shouldReject = false) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (shouldReject) {
                    reject(new Error(typeof data === 'string' ? data : '模擬 API 錯誤'));
                } else {
                    resolve(data);
                }
            }, delay);
        });
    }
    
    /**
     * 觸發 DOM 事件
     * @param {HTMLElement} element - 目標元素
     * @param {string} eventType - 事件類型
     * @param {object} eventData - 事件數據
     */
    static triggerEvent(element, eventType, eventData = {}) {
        const event = new CustomEvent(eventType, {
            detail: eventData,
            bubbles: true,
            cancelable: true
        });
        
        element.dispatchEvent(event);
    }
    
    /**
     * 驗證對象結構
     * @param {object} obj - 要驗證的對象
     * @param {object} schema - 期望的結構
     * @returns {boolean} 驗證結果
     */
    static validateObjectSchema(obj, schema) {
        for (const [key, expectedType] of Object.entries(schema)) {
            if (!(key in obj)) {
                console.error(`Missing property: ${key}`);
                return false;
            }
            
            const actualType = typeof obj[key];
            if (actualType !== expectedType) {
                console.error(`Property ${key} expected ${expectedType}, got ${actualType}`);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 創建性能監控 Mock
     * @returns {object} 性能監控器
     */
    static createMockPerformanceMonitor() {
        const operations = new Map();
        
        return {
            startOperation: jest.fn((name) => {
                operations.set(name, { startTime: performance.now() });
                return name;
            }),
            
            endOperation: jest.fn((name) => {
                const operation = operations.get(name);
                if (operation) {
                    operation.endTime = performance.now();
                    operation.duration = operation.endTime - operation.startTime;
                }
            }),
            
            getStats: jest.fn(() => ({
                totalOperations: operations.size,
                operations: Array.from(operations.entries()).map(([name, data]) => ({
                    name,
                    duration: data.duration || 0,
                    status: data.endTime ? 'completed' : 'running'
                }))
            })),
            
            clear: jest.fn(() => {
                operations.clear();
            }),
            
            // 內部狀態
            _operations: operations
        };
    }
}

// 導出全域使用
if (typeof window !== 'undefined') {
    window.TestUtils = TestUtils;
}

console.log('🧪 TestUtils 載入完成');
