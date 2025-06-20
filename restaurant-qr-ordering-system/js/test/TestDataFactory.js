// 測試資料工廠 - 生成測試用資料
// 實現工廠模式，提供標準化測試資料

class TestDataFactory {
    /**
     * 創建菜單項目
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 菜單項目物件
     */
    static createMenuItem(overrides = {}) {
        return {
            id: 'menu001',
            name: '招牌牛肉麵',
            price: 150,
            category: 'main',
            description: '精選牛肉配上特製湯頭，香氣濃郁',
            image: 'images/menu/beef_noodle.jpg',
            available: true,
            tags: ['招牌', '牛肉', '麵食'],
            preparationTime: 15,
            ...overrides
        };
    }

    /**
     * 創建購物車項目
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 購物車項目物件
     */
    static createCartItem(overrides = {}) {
        const menuItem = this.createMenuItem(overrides.menuItem || {});
        return {
            id: menuItem.id,
            name: menuItem.name,
            price: menuItem.price,
            quantity: 2,
            image: menuItem.image,
            addedAt: new Date().toISOString(),
            ...overrides
        };
    }

    /**
     * 創建訂單
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 訂單物件
     */
    static createOrder(overrides = {}) {
        const items = overrides.items || [this.createCartItem()];
        const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        return {
            orderNumber: 'ORD20250620103500ABCD',
            tableNumber: 'A12',
            items,
            subtotal,
            total: subtotal,
            paymentMethod: 'cash',
            customerNote: '',
            status: 'pending',
            paymentStatus: 'pending',
            submittedAt: new Date().toISOString(),
            estimatedTime: '15-20分鐘',
            ...overrides
        };
    }

    /**
     * 創建完整的購物車
     * @param {number} itemCount - 項目數量
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Array} 購物車項目陣列
     */
    static createCart(itemCount = 3, overrides = {}) {
        const items = [];
        
        for (let i = 0; i < itemCount; i++) {
            const item = this.createCartItem({
                id: `menu00${i + 1}`,
                name: `測試商品 ${i + 1}`,
                price: 100 + (i * 50),
                quantity: i + 1,
                ...overrides
            });
            items.push(item);
        }
        
        return items;
    }

    /**
     * 創建座號資訊
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 座號資訊物件
     */
    static createTableInfo(overrides = {}) {
        return {
            number: 'A12',
            timestamp: new Date().toISOString(),
            validated: true,
            ...overrides
        };
    }

    /**
     * 創建錯誤物件
     * @param {Object} overrides - 覆蓋預設值  
     * @returns {Error} 錯誤物件
     */
    static createError(overrides = {}) {
        const defaults = {
            message: '測試錯誤',
            code: 'TEST_ERROR',
            context: { test: true }
        };
        
        const errorData = { ...defaults, ...overrides };
        const error = new Error(errorData.message);
        error.code = errorData.code;
        error.context = errorData.context;
        
        return error;
    }

    /**
     * 創建事件資料
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 事件資料物件
     */
    static createEventData(overrides = {}) {
        return {
            type: 'test-event',
            timestamp: new Date().toISOString(),
            source: 'test',
            data: { test: true },
            ...overrides
        };
    }

    /**
     * 創建性能指標
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Object} 性能指標物件
     */
    static createPerformanceMetric(overrides = {}) {
        return {
            action: 'test-action',
            duration: 100,
            timestamp: new Date().toISOString(),
            memoryUsage: {
                usedJSHeapSize: 10000000,
                totalJSHeapSize: 20000000,
                jsHeapSizeLimit: 100000000
            },
            ...overrides
        };
    }

    /**
     * 創建多個測試資料
     * @param {string} type - 資料類型
     * @param {number} count - 數量
     * @param {Object} overrides - 覆蓋預設值
     * @returns {Array} 測試資料陣列
     */
    static createMultiple(type, count = 3, overrides = {}) {
        const items = [];
        const methodName = `create${type.charAt(0).toUpperCase() + type.slice(1)}`;
        
        if (typeof this[methodName] !== 'function') {
            throw new Error(`未知的資料類型: ${type}`);
        }
        
        for (let i = 0; i < count; i++) {
            const item = this[methodName]({
                ...overrides,
                id: `${type}${String(i + 1).padStart(3, '0')}`
            });
            items.push(item);
        }
        
        return items;
    }

    /**
     * 創建隨機測試資料
     * @param {string} type - 資料類型
     * @param {Object} options - 選項
     * @returns {Object} 隨機測試資料
     */
    static createRandom(type, options = {}) {
        const randomId = Math.random().toString(36).substr(2, 9);
        const randomPrice = Math.floor(Math.random() * 300) + 50;
        const randomQuantity = Math.floor(Math.random() * 5) + 1;
        
        const randomOverrides = {
            id: randomId,
            price: randomPrice,
            quantity: randomQuantity,
            name: `隨機${type} ${randomId}`,
            ...options
        };
        
        const methodName = `create${type.charAt(0).toUpperCase() + type.slice(1)}`;
        
        if (typeof this[methodName] !== 'function') {
            throw new Error(`未知的資料類型: ${type}`);
        }
        
        return this[methodName](randomOverrides);
    }
}

// 測試輔助工具
class TestUtils {
    /**
     * 等待條件滿足
     * @param {Function} condition - 條件函數
     * @param {number} timeout - 超時時間（毫秒）
     * @param {number} interval - 檢查間隔（毫秒）
     * @returns {Promise<boolean>} 是否滿足條件
     */
    static async waitFor(condition, timeout = 5000, interval = 50) {
        const start = Date.now();
        
        while (Date.now() - start < timeout) {
            if (condition()) {
                return true;
            }
            await new Promise(resolve => setTimeout(resolve, interval));
        }
        
        throw new Error(`條件在 ${timeout}ms 內未滿足`);
    }

    /**
     * 等待指定時間
     * @param {number} ms - 等待時間（毫秒）
     * @returns {Promise<void>}
     */
    static async sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * 模擬 DOM 元素
     * @param {string} tagName - 標籤名稱
     * @param {Object} attributes - 屬性
     * @param {string} textContent - 文字內容
     * @returns {HTMLElement} DOM 元素
     */
    static createMockElement(tagName = 'div', attributes = {}, textContent = '') {
        const element = document.createElement(tagName);
        
        Object.entries(attributes).forEach(([key, value]) => {
            element.setAttribute(key, value);
        });
        
        if (textContent) {
            element.textContent = textContent;
        }
        
        return element;
    }

    /**
     * 模擬事件物件
     * @param {string} type - 事件類型
     * @param {Object} properties - 事件屬性
     * @returns {Event} 事件物件
     */
    static createMockEvent(type, properties = {}) {
        const event = new Event(type);
        
        Object.entries(properties).forEach(([key, value]) => {
            Object.defineProperty(event, key, {
                value,
                writable: true
            });
        });
        
        return event;
    }

    /**
     * 模擬 localStorage
     * @returns {Object} Mock localStorage 物件
     */
    static createMockLocalStorage() {
        const store = new Map();
        
        return {
            getItem: jest.fn((key) => store.get(key) || null),
            setItem: jest.fn((key, value) => store.set(key, value)),
            removeItem: jest.fn((key) => store.delete(key)),
            clear: jest.fn(() => store.clear()),
            key: jest.fn((index) => Array.from(store.keys())[index] || null),
            get length() { return store.size; }
        };
    }

    /**
     * 模擬 EventBus
     * @returns {Object} Mock EventBus 物件
     */
    static createMockEventBus() {
        const events = new Map();
        
        return {
            on: jest.fn((event, callback, options = {}) => {
                if (!events.has(event)) {
                    events.set(event, []);
                }
                events.get(event).push({ callback, options });
            }),
            
            off: jest.fn((event, callback) => {
                if (events.has(event)) {
                    const listeners = events.get(event);
                    const index = listeners.findIndex(l => l.callback === callback);
                    if (index !== -1) {
                        listeners.splice(index, 1);
                    }
                }
            }),
            
            emit: jest.fn((event, data) => {
                if (events.has(event)) {
                    events.get(event).forEach(({ callback }) => {
                        try {
                            callback(data);
                        } catch (error) {
                            console.error('Mock EventBus 回調錯誤:', error);
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
            
            // 測試輔助方法
            getListeners: (event) => events.get(event) || [],
            getAllEvents: () => Array.from(events.keys()),
            clearAllListeners: () => events.clear()
        };
    }

    /**
     * 模擬 HTTP 請求
     * @param {Object} options - 請求選項
     * @returns {Promise} Mock Promise
     */
    static createMockHttpRequest(options = {}) {
        const {
            shouldResolve = true,
            delay = 0,
            data = {},
            error = new Error('Mock HTTP Error')
        } = options;
        
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (shouldResolve) {
                    resolve({
                        ok: true,
                        status: 200,
                        json: () => Promise.resolve(data),
                        text: () => Promise.resolve(JSON.stringify(data))
                    });
                } else {
                    reject(error);
                }
            }, delay);
        });
    }

    /**
     * 清理測試環境
     */
    static cleanup() {
        // 清理 localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.clear();
        }
        
        // 清理計時器
        const highestTimeoutId = setTimeout(() => {}, 0);
        for (let i = 0; i < highestTimeoutId; i++) {
            clearTimeout(i);
        }
        
        // 清理事件監聽器
        const elements = document.querySelectorAll('*');
        elements.forEach(element => {
            const clone = element.cloneNode(true);
            element.parentNode?.replaceChild(clone, element);
        });
    }

    /**
     * 深度比較物件
     * @param {*} obj1 - 物件1
     * @param {*} obj2 - 物件2
     * @returns {boolean} 是否相等
     */
    static deepEqual(obj1, obj2) {
        if (obj1 === obj2) return true;
        
        if (obj1 == null || obj2 == null) return false;
        
        if (typeof obj1 !== typeof obj2) return false;
        
        if (typeof obj1 !== 'object') return obj1 === obj2;
        
        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);
        
        if (keys1.length !== keys2.length) return false;
        
        for (const key of keys1) {
            if (!keys2.includes(key)) return false;
            if (!this.deepEqual(obj1[key], obj2[key])) return false;
        }
        
        return true;
    }

    /**
     * 產生隨機字串
     * @param {number} length - 長度
     * @returns {string} 隨機字串
     */
    static randomString(length = 10) {
        return Math.random().toString(36).substr(2, length);
    }

    /**
     * 產生隨機數字
     * @param {number} min - 最小值
     * @param {number} max - 最大值
     * @returns {number} 隨機數字
     */
    static randomNumber(min = 0, max = 100) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
}

// 全域導出
window.TestDataFactory = TestDataFactory;
window.TestUtils = TestUtils;

console.log('🏭 測試資料工廠和輔助工具載入完成');
