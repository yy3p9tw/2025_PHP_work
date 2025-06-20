// 事件系統架構 - 中央事件匯流排
// 實現單例模式，提供全域事件管理

class EventBus {    constructor() {
        if (EventBus.instance) {
            return EventBus.instance;
        }
        
        this.events = new Map();
        this.debugMode = true;
        this.maxListeners = 100;
        this.idCounter = 0;
        
        EventBus.instance = this;
        
        if (this.debugMode) {
            console.log('🚌 EventBus 初始化完成');
        }
    }
    
    static getInstance() {
        if (!EventBus.instance) {
            EventBus.instance = new EventBus();
        }
        return EventBus.instance;
    }
    
    /**
     * 註冊事件監聽器
     * @param {string} event - 事件名稱
     * @param {function} callback - 回調函數
     * @param {object} options - 選項 {once: boolean, priority: number}
     */
    on(event, callback, options = {}) {
        this.validateEventName(event);
        
        if (typeof callback !== 'function') {
            throw new Error('EventBus: 回調函數必須是函數類型');
        }
        
        if (!this.events.has(event)) {
            this.events.set(event, []);
        }
        
        const listeners = this.events.get(event);
        
        // 檢查監聽器數量限制
        if (listeners.length >= this.maxListeners) {
            console.warn(`EventBus: 事件 "${event}" 的監聽器數量已達上限 (${this.maxListeners})`);
        }
        
        const listener = {
            callback,
            options,
            id: this.generateId(),
            priority: options.priority || 0
        };
        
        // 按優先級排序插入
        const insertIndex = listeners.findIndex(l => l.priority < listener.priority);
        if (insertIndex === -1) {
            listeners.push(listener);
        } else {
            listeners.splice(insertIndex, 0, listener);
        }
        
        if (this.debugMode) {
            console.log(`📝 EventBus: 註冊事件監聽器 "${event}" (優先級: ${listener.priority})`);
        }
        
        return listener.id;
    }
    
    /**
     * 移除事件監聽器
     * @param {string} event - 事件名稱
     * @param {function|number} callbackOrId - 回調函數或監聽器ID
     */
    off(event, callbackOrId) {
        if (!this.events.has(event)) {
            return false;
        }
        
        const listeners = this.events.get(event);
        const initialLength = listeners.length;
        
        if (typeof callbackOrId === 'function') {
            // 按回調函數移除
            const index = listeners.findIndex(listener => listener.callback === callbackOrId);
            if (index !== -1) {
                listeners.splice(index, 1);
            }
        } else if (typeof callbackOrId === 'number') {
            // 按ID移除
            const index = listeners.findIndex(listener => listener.id === callbackOrId);
            if (index !== -1) {
                listeners.splice(index, 1);
            }
        } else {
            // 移除所有監聽器
            listeners.length = 0;
        }
        
        // 如果沒有監聽器，移除事件
        if (listeners.length === 0) {
            this.events.delete(event);
        }
        
        const removed = initialLength - (listeners.length || 0);
        
        if (this.debugMode && removed > 0) {
            console.log(`🗑️ EventBus: 移除了 ${removed} 個事件監聽器 "${event}"`);
        }
        
        return removed > 0;
    }
    
    /**
     * 觸發事件
     * @param {string} event - 事件名稱
     * @param {*} data - 事件資料
     */
    emit(event, data = null) {
        let executedCount = 0;
        
        // 處理普通監聽器
        if (this.events.has(event)) {
            const listeners = this.events.get(event);
            const onceListeners = [];
            
            if (this.debugMode) {
                console.log(`🚌 Event: ${event}`, data);
            }
            
            listeners.forEach(listener => {
                try {
                    listener.callback(data, event);
                    executedCount++;
                    
                    if (listener.options && listener.options.once) {
                        onceListeners.push(listener);
                    }
                } catch (error) {
                    console.error(`❌ EventBus 監聽器錯誤 (${event}):`, error);
                }
            });
            
            // 移除一次性監聽器
            onceListeners.forEach(listener => {
                this.off(event, listener.callback);
            });
        }
        
        // 處理萬用字元監聽器
        if (this.wildcardListeners) {
            this.wildcardListeners.forEach(listener => {
                if (listener.regex.test(event)) {
                    try {
                        listener.callback(data, event);
                        executedCount++;
                    } catch (error) {
                        console.error(`❌ EventBus 萬用字元監聽器錯誤 (${listener.pattern}):`, error);
                    }
                }
            });
        }
        
        return executedCount;
    }
    
    /**
     * 防抖處理
     * @param {function} callback - 回調函數
     * @param {number} delay - 延遲時間 (毫秒)
     */
    debounce(callback, delay = 300) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => callback.apply(this, args), delay);
        };
    }
    
    /**
     * 節流處理
     * @param {function} callback - 回調函數
     * @param {number} limit - 限制時間 (毫秒)
     */
    throttle(callback, limit = 100) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                callback.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    /**
     * 批次事件處理
     * @param {Array} events - 事件陣列 [{event, data}]
     */
    batchEmit(events) {
        if (!Array.isArray(events)) {
            throw new Error('EventBus: batchEmit 需要陣列參數');
        }
        
        let totalExecuted = 0;
        
        events.forEach(({event, data}) => {
            totalExecuted += this.emit(event, data);
        });
        
        if (this.debugMode) {
            console.log(`📦 EventBus: 批次處理 ${events.length} 個事件，執行了 ${totalExecuted} 個監聽器`);
        }
        
        return totalExecuted;
    }
    
    /**
     * 獲取事件統計資訊
     */
    getStats() {
        const stats = {
            totalEvents: this.events.size,
            totalListeners: 0,
            events: {}
        };
        
        this.events.forEach((listeners, event) => {
            stats.totalListeners += listeners.length;
            stats.events[event] = listeners.length;
        });
        
        return stats;
    }
    
    /**
     * 清空所有事件監聽器
     */
    clear() {
        const eventCount = this.events.size;
        this.events.clear();
        
        if (this.debugMode) {
            console.log(`🧹 EventBus: 清空了 ${eventCount} 個事件`);
        }
    }

    /**
     * 一次性事件監聽
     * @param {string} event - 事件名稱
     * @param {function} callback - 回調函數
     */
    once(event, callback) {
        const onceWrapper = (data) => {
            callback(data);
            this.off(event, onceWrapper);
        };
        
        this.on(event, onceWrapper, { once: true });
    }

    /**
     * 萬用字元事件監聽
     * @param {string} pattern - 事件模式（支援 * 萬用字元）
     * @param {function} callback - 回調函數
     */
    onWildcard(pattern, callback) {
        if (!this.wildcardListeners) {
            this.wildcardListeners = [];
        }
        
        const regex = new RegExp('^' + pattern.replace(/\*/g, '.*') + '$');
        
        this.wildcardListeners.push({
            pattern,
            regex,
            callback,
            id: this.generateId()
        });
        
        if (this.debugMode) {
            console.log(`🌟 EventBus: 註冊萬用字元監聽器 "${pattern}"`);
        }
    }

    /**
     * 移除萬用字元事件監聽
     * @param {string} pattern - 事件模式
     * @param {function} callback - 回調函數
     */
    offWildcard(pattern, callback) {
        if (!this.wildcardListeners) return;
        
        const initialLength = this.wildcardListeners.length;
        
        this.wildcardListeners = this.wildcardListeners.filter(listener => 
            !(listener.pattern === pattern && listener.callback === callback)
        );
        
        const removed = initialLength - this.wildcardListeners.length;
        
        if (this.debugMode && removed > 0) {
            console.log(`🗑️ EventBus: 移除了 ${removed} 個萬用字元監聽器 "${pattern}"`);
        }
    }

    /**
     * 獲取事件統計資訊（增強版）
     */
    getEventStats() {
        const stats = {};
        
        this.events.forEach((listeners, event) => {
            stats[event] = {
                listenerCount: listeners.length,
                emitCount: 0, // 這需要在 emit 時記錄
                lastEmitted: null
            };
        });
        
        return stats;
    }

    /**
     * 清空所有監聽器
     */
    clearAllListeners() {
        this.events.clear();
        if (this.wildcardListeners) {
            this.wildcardListeners = [];
        }
        
        if (this.debugMode) {
            console.log('🧹 EventBus: 清空了所有監聽器');
        }
    }    /**
     * 驗證事件名稱
     * @param {string} event - 事件名稱
     */
    validateEventName(event) {
        if (!event || typeof event !== 'string') {
            throw new Error('EventBus: 事件名稱必須是字串');
        }
        
        if (event.trim() === '') {
            throw new Error('EventBus: 事件名稱不能為空');
        }
        
        return true;
    }

    /**
     * 生成唯一 ID
     * @returns {number} 唯一 ID
     */
    generateId() {
        if (!this.idCounter) {
            this.idCounter = 0;
        }
        return ++this.idCounter;
    }
}

// 事件常數定義
const EVENTS = {
    // 購物車相關事件
    CART: {
        ITEM_ADDED: 'cart:item-added',
        ITEM_REMOVED: 'cart:item-removed',
        QUANTITY_UPDATED: 'cart:quantity-updated',
        CLEARED: 'cart:cleared',
        LOADED: 'cart:loaded'
    },
    
    // 訂單相關事件
    ORDER: {
        CREATED: 'order:created',
        SUBMITTED: 'order:submitted',
        STATUS_UPDATED: 'order:status-updated',
        PAYMENT_UPDATED: 'order:payment-updated'
    },
    
    // UI 相關事件
    UI: {
        MODAL_OPENED: 'ui:modal-opened',
        MODAL_CLOSED: 'ui:modal-closed',
        LOADING_START: 'ui:loading-start',
        LOADING_END: 'ui:loading-end',
        PAGE_LOADED: 'ui:page-loaded',
        TOAST_SHOW: 'ui:toast-show'
    },
    
    // 頁面相關事件
    PAGE: {
        INITIALIZED: 'page:initialized',
        DESTROYED: 'page:destroyed',
        ERROR: 'page:error'
    },
    
    // 導航相關事件
    NAVIGATION: {
        ROUTE_CHANGED: 'navigation:route-changed',
        BACK_PRESSED: 'navigation:back-pressed'
    }
};

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { EventBus, EVENTS };
} else {
    window.EventBus = EventBus;
    window.EVENTS = EVENTS;
}
