// 儲存服務介面定義 - 提升可測試性
// 實現介面分離原則，支援多種儲存後端

// 抽象儲存服務介面
class IStorageService {
    /**
     * 獲取儲存項目
     * @param {string} key - 鍵名
     * @returns {*} 儲存的值
     */
    getItem(key) {
        throw new Error('Method getItem must be implemented');
    }
    
    /**
     * 設置儲存項目
     * @param {string} key - 鍵名
     * @param {*} value - 要儲存的值
     * @returns {boolean} 是否成功
     */
    setItem(key, value) {
        throw new Error('Method setItem must be implemented');
    }
    
    /**
     * 移除儲存項目
     * @param {string} key - 鍵名
     * @returns {boolean} 是否成功
     */
    removeItem(key) {
        throw new Error('Method removeItem must be implemented');
    }
    
    /**
     * 清空所有儲存項目
     * @returns {boolean} 是否成功
     */
    clear() {
        throw new Error('Method clear must be implemented');
    }
    
    /**
     * 獲取所有鍵名
     * @returns {string[]} 鍵名陣列
     */
    keys() {
        throw new Error('Method keys must be implemented');
    }
    
    /**
     * 檢查是否存在指定鍵
     * @param {string} key - 鍵名
     * @returns {boolean} 是否存在
     */
    hasKey(key) {
        throw new Error('Method hasKey must be implemented');
    }
}

// LocalStorage 實現
class LocalStorageService extends IStorageService {
    constructor() {
        super();
        this.storage = window.localStorage;
        this.prefix = 'restaurant_';
        
        // 檢查 localStorage 是否可用
        this.isAvailable = this.checkAvailability();
        
        if (!this.isAvailable) {
            console.warn('⚠️ LocalStorage 不可用，將使用記憶體儲存');
            this.fallbackStorage = new Map();
        }
    }
    
    /**
     * 檢查 localStorage 可用性
     */
    checkAvailability() {
        try {
            const testKey = '__storage_test__';
            this.storage.setItem(testKey, 'test');
            this.storage.removeItem(testKey);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    /**
     * 獲取完整鍵名
     * @param {string} key - 原始鍵名
     */
    getFullKey(key) {
        return `${this.prefix}${key}`;
    }
    
    getItem(key) {
        try {
            const fullKey = this.getFullKey(key);
            
            if (!this.isAvailable) {
                const value = this.fallbackStorage.get(fullKey);
                return value !== undefined ? value : null;
            }
            
            const item = this.storage.getItem(fullKey);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error(`LocalStorageService getItem error for key "${key}":`, error);
            return null;
        }
    }
    
    setItem(key, value) {
        try {
            const fullKey = this.getFullKey(key);
            
            if (!this.isAvailable) {
                this.fallbackStorage.set(fullKey, value);
                return true;
            }
            
            this.storage.setItem(fullKey, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error(`LocalStorageService setItem error for key "${key}":`, error);
            
            // 如果是配額錯誤，嘗試清理舊資料
            if (error.name === 'QuotaExceededError') {
                this.cleanupOldData();
                
                // 再次嘗試
                try {
                    this.storage.setItem(this.getFullKey(key), JSON.stringify(value));
                    return true;
                } catch (retryError) {
                    console.error('LocalStorageService retry after cleanup failed:', retryError);
                }
            }
            
            return false;
        }
    }
    
    removeItem(key) {
        try {
            const fullKey = this.getFullKey(key);
            
            if (!this.isAvailable) {
                return this.fallbackStorage.delete(fullKey);
            }
            
            this.storage.removeItem(fullKey);
            return true;
        } catch (error) {
            console.error(`LocalStorageService removeItem error for key "${key}":`, error);
            return false;
        }
    }
    
    clear() {
        try {
            if (!this.isAvailable) {
                this.fallbackStorage.clear();
                return true;
            }
            
            // 只清理應用相關的資料
            const keysToRemove = [];
            
            for (let i = 0; i < this.storage.length; i++) {
                const key = this.storage.key(i);
                if (key && key.startsWith(this.prefix)) {
                    keysToRemove.push(key);
                }
            }
            
            keysToRemove.forEach(key => {
                this.storage.removeItem(key);
            });
            
            return true;
        } catch (error) {
            console.error('LocalStorageService clear error:', error);
            return false;
        }
    }
    
    keys() {
        try {
            if (!this.isAvailable) {
                return Array.from(this.fallbackStorage.keys())
                    .filter(key => key.startsWith(this.prefix))
                    .map(key => key.replace(this.prefix, ''));
            }
            
            const keys = [];
            
            for (let i = 0; i < this.storage.length; i++) {
                const key = this.storage.key(i);
                if (key && key.startsWith(this.prefix)) {
                    keys.push(key.replace(this.prefix, ''));
                }
            }
            
            return keys;
        } catch (error) {
            console.error('LocalStorageService keys error:', error);
            return [];
        }
    }
    
    hasKey(key) {
        try {
            const fullKey = this.getFullKey(key);
            
            if (!this.isAvailable) {
                return this.fallbackStorage.has(fullKey);
            }
            
            return this.storage.getItem(fullKey) !== null;
        } catch (error) {
            console.error(`LocalStorageService hasKey error for key "${key}":`, error);
            return false;
        }
    }
    
    /**
     * 清理舊資料
     */
    cleanupOldData() {
        try {
            const now = Date.now();
            const oneWeekAgo = now - (7 * 24 * 60 * 60 * 1000); // 一週前
            
            // 清理舊的錯誤日誌
            const errorLog = this.getItem('errorLog');
            if (errorLog && Array.isArray(errorLog)) {
                const filteredLog = errorLog.filter(error => {
                    const errorTime = new Date(error.timestamp).getTime();
                    return errorTime > oneWeekAgo;
                });
                
                if (filteredLog.length < errorLog.length) {
                    this.setItem('errorLog', filteredLog);
                    console.log(`🧹 清理了 ${errorLog.length - filteredLog.length} 筆舊錯誤日誌`);
                }
            }
            
            // 清理舊的效能記錄
            const performanceMetrics = this.getItem('performanceMetrics');
            if (performanceMetrics && Array.isArray(performanceMetrics)) {
                const filteredMetrics = performanceMetrics.filter(metric => {
                    const metricTime = new Date(metric.timestamp).getTime();
                    return metricTime > oneWeekAgo;
                });
                
                if (filteredMetrics.length < performanceMetrics.length) {
                    this.setItem('performanceMetrics', filteredMetrics);
                    console.log(`🧹 清理了 ${performanceMetrics.length - filteredMetrics.length} 筆舊效能記錄`);
                }
            }
            
        } catch (error) {
            console.error('LocalStorageService cleanupOldData error:', error);
        }
    }
    
    /**
     * 獲取儲存使用情況
     */
    getStorageUsage() {
        if (!this.isAvailable) {
            return {
                used: this.fallbackStorage.size,
                available: Infinity,
                percentage: 0
            };
        }
        
        try {
            let totalSize = 0;
            
            for (let i = 0; i < this.storage.length; i++) {
                const key = this.storage.key(i);
                if (key && key.startsWith(this.prefix)) {
                    const value = this.storage.getItem(key);
                    totalSize += key.length + (value ? value.length : 0);
                }
            }
            
            // 粗略估計 localStorage 限制為 5MB
            const maxSize = 5 * 1024 * 1024;
            const percentage = (totalSize / maxSize) * 100;
            
            return {
                used: totalSize,
                available: maxSize - totalSize,
                percentage: Math.round(percentage)
            };
        } catch (error) {
            console.error('LocalStorageService getStorageUsage error:', error);
            return {
                used: 0,
                available: 0,
                percentage: 0
            };
        }
    }
}

// 測試用 Mock 實現
class MockStorageService extends IStorageService {
    constructor(initialData = {}) {
        super();
        this.store = new Map();
        
        // 初始化資料
        Object.entries(initialData).forEach(([key, value]) => {
            this.store.set(key, value);
        });
    }
    
    getItem(key) {
        const value = this.store.get(key);
        return value !== undefined ? value : null;
    }
    
    setItem(key, value) {
        try {
            this.store.set(key, value);
            return true;
        } catch (error) {
            console.error(`MockStorageService setItem error for key "${key}":`, error);
            return false;
        }
    }
    
    removeItem(key) {
        return this.store.delete(key);
    }
    
    clear() {
        this.store.clear();
        return true;
    }
    
    keys() {
        return Array.from(this.store.keys());
    }
    
    hasKey(key) {
        return this.store.has(key);
    }
    
    /**
     * 獲取所有資料 (測試用)
     */
    getAllData() {
        const data = {};
        this.store.forEach((value, key) => {
            data[key] = value;
        });
        return data;
    }
    
    /**
     * 設置多筆資料 (測試用)
     */
    setMultipleItems(data) {
        Object.entries(data).forEach(([key, value]) => {
            this.store.set(key, value);
        });
    }
}

// 記憶體儲存服務 (作為備用方案)
class MemoryStorageService extends IStorageService {
    constructor() {
        super();
        this.store = new Map();
        this.maxSize = 100; // 最大儲存項目數
    }
    
    getItem(key) {
        const value = this.store.get(key);
        return value !== undefined ? value : null;
    }
    
    setItem(key, value) {
        try {
            // 檢查大小限制
            if (this.store.size >= this.maxSize && !this.store.has(key)) {
                console.warn(`MemoryStorageService: 達到最大儲存限制 (${this.maxSize})`);
                // 移除最舊的項目 (FIFO)
                const firstKey = this.store.keys().next().value;
                if (firstKey) {
                    this.store.delete(firstKey);
                }
            }
            
            this.store.set(key, value);
            return true;
        } catch (error) {
            console.error(`MemoryStorageService setItem error for key "${key}":`, error);
            return false;
        }
    }
    
    removeItem(key) {
        return this.store.delete(key);
    }
    
    clear() {
        this.store.clear();
        return true;
    }
    
    keys() {
        return Array.from(this.store.keys());
    }
    
    hasKey(key) {
        return this.store.has(key);
    }
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        IStorageService,
        LocalStorageService,
        MockStorageService,
        MemoryStorageService
    };
} else {
    window.IStorageService = IStorageService;
    window.LocalStorageService = LocalStorageService;
    window.MockStorageService = MockStorageService;
    window.MemoryStorageService = MemoryStorageService;
}
