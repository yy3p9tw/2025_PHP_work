// 依賴注入容器 - 提升可測試性
// 管理所有服務的創建和依賴關係

class DIContainer {
    constructor() {
        this.services = new Map();
        this.factories = new Map();
        this.singletons = new Map();
        
        // 註冊預設服務
        this.registerDefaultServices();
        
        console.log('🏗️ DIContainer 初始化完成');
    }
    
    /**
     * 註冊服務
     * @param {string} name - 服務名稱
     * @param {function} factory - 工廠函數
     * @param {Object} options - 選項
     */
    register(name, factory, options = {}) {
        if (typeof factory !== 'function') {
            throw new Error(`DIContainer: 工廠函數必須是函數類型: ${name}`);
        }
        
        const serviceConfig = {
            factory,
            singleton: options.singleton || false,
            dependencies: options.dependencies || [],
            lazy: options.lazy !== false // 預設為懶載入
        };
        
        this.factories.set(name, serviceConfig);
        
        // 如果不是懶載入且是單例，立即創建
        if (!serviceConfig.lazy && serviceConfig.singleton) {
            this.get(name);
        }
        
        console.log(`📝 註冊服務: ${name} (單例: ${serviceConfig.singleton})`);
        
        return this;
    }
    
    /**
     * 獲取服務
     * @param {string} name - 服務名稱
     * @returns {*} 服務實例
     */
    get(name) {
        // 檢查是否已註冊
        if (!this.factories.has(name)) {
            throw new Error(`DIContainer: 服務未註冊: ${name}`);
        }
        
        const config = this.factories.get(name);
        
        // 如果是單例且已創建，直接返回
        if (config.singleton && this.singletons.has(name)) {
            return this.singletons.get(name);
        }
        
        // 解析依賴
        const dependencies = this.resolveDependencies(config.dependencies);
        
        // 創建服務實例
        const instance = config.factory(...dependencies);
        
        // 如果是單例，儲存實例
        if (config.singleton) {
            this.singletons.set(name, instance);
        }
        
        return instance;
    }
    
    /**
     * 檢查服務是否已註冊
     * @param {string} name - 服務名稱
     */
    has(name) {
        return this.factories.has(name);
    }
    
    /**
     * 移除服務
     * @param {string} name - 服務名稱
     */
    remove(name) {
        const removed = this.factories.delete(name);
        this.singletons.delete(name);
        
        if (removed) {
            console.log(`🗑️ 移除服務: ${name}`);
        }
        
        return removed;
    }
    
    /**
     * 清空所有服務
     */
    clear() {
        const factoryCount = this.factories.size;
        const singletonCount = this.singletons.size;
        
        this.factories.clear();
        this.singletons.clear();
        
        console.log(`🧹 清空服務容器: ${factoryCount} 個工廠, ${singletonCount} 個單例`);
    }
    
    /**
     * 解析依賴
     * @param {string[]} dependencies - 依賴列表
     */
    resolveDependencies(dependencies) {
        return dependencies.map(dep => {
            if (typeof dep === 'string') {
                return this.get(dep);
            } else if (typeof dep === 'function') {
                return dep();
            } else {
                return dep;
            }
        });
    }
    
    /**
     * 註冊預設服務
     */
    registerDefaultServices() {
        // 儲存服務
        this.register('storage', () => new LocalStorageService(), {
            singleton: true
        });
        
        // 事件匯流排
        this.register('eventBus', () => EventBus.getInstance(), {
            singleton: true
        });
        
        // 錯誤處理器
        this.register('errorHandler', () => ErrorHandler.getInstance(), {
            singleton: true
        });
        
        // 模態框管理器
        this.register('modalManager', () => new ModalManager(), {
            singleton: true
        });
        
        // 購物車管理器
        this.register('cartManager', () => new CartManager(this.get('storage')), {
            singleton: true,
            dependencies: ['storage']
        });
        
        // 訂單管理器
        this.register('orderManager', () => new OrderManager(this.get('storage')), {
            singleton: true,
            dependencies: ['storage']
        });
        
        // 效能監控器
        this.register('performanceMonitor', () => new PerformanceMonitor(), {
            singleton: true
        });
        
        console.log('✅ 預設服務註冊完成');
    }
    
    /**
     * 創建子容器
     * @param {Object} overrides - 覆寫的服務
     */
    createChild(overrides = {}) {
        const child = new DIContainer();
        
        // 複製父容器的服務
        this.factories.forEach((config, name) => {
            if (!overrides[name]) {
                child.factories.set(name, config);
            }
        });
        
        // 註冊覆寫的服務
        Object.entries(overrides).forEach(([name, factory]) => {
            if (typeof factory === 'function') {
                child.register(name, factory);
            } else {
                child.register(name, () => factory);
            }
        });
        
        return child;
    }
    
    /**
     * 批次註冊服務
     * @param {Object} services - 服務對象
     */
    registerBatch(services) {
        Object.entries(services).forEach(([name, config]) => {
            if (typeof config === 'function') {
                this.register(name, config);
            } else {
                this.register(name, config.factory, config.options);
            }
        });
        
        return this;
    }
    
    /**
     * 獲取服務列表
     */
    getServiceList() {
        return Array.from(this.factories.keys());
    }
    
    /**
     * 獲取容器統計資訊
     */
    getStats() {
        return {
            totalServices: this.factories.size,
            singletonServices: Array.from(this.factories.entries())
                .filter(([name, config]) => config.singleton)
                .map(([name]) => name),
            activeSingletons: this.singletons.size,
            services: Array.from(this.factories.entries()).map(([name, config]) => ({
                name,
                singleton: config.singleton,
                lazy: config.lazy,
                dependencies: config.dependencies,
                active: this.singletons.has(name)
            }))
        };
    }
    
    /**
     * 除錯模式 - 顯示服務依賴圖
     */
    debugDependencyGraph() {
        console.group('🔍 DIContainer 依賴圖');
        
        this.factories.forEach((config, name) => {
            const status = config.singleton ? 
                (this.singletons.has(name) ? '✅ 已創建' : '⏳ 未創建') : 
                '🔄 瞬時';
            
            console.log(`${name} ${status}`);
            
            if (config.dependencies.length > 0) {
                console.log(`  依賴: ${config.dependencies.join(', ')}`);
            }
        });
        
        console.groupEnd();
    }
}

// 效能監控器
class PerformanceMonitor {
    constructor() {
        this.metrics = [];
        this.maxMetrics = 1000;
        this.isEnabled = true;
        
        console.log('⏱️ PerformanceMonitor 初始化完成');
    }
    
    /**
     * 測量執行時間
     * @param {string} name - 測量名稱
     * @param {function} fn - 要測量的函數
     */
    async measure(name, fn) {
        if (!this.isEnabled) {
            return await fn();
        }
        
        const startTime = performance.now();
        const startMemory = this.getMemoryUsage();
        
        try {
            const result = await fn();
            const endTime = performance.now();
            const endMemory = this.getMemoryUsage();
            
            this.recordMetric({
                name,
                duration: endTime - startTime,
                memoryDelta: endMemory - startMemory,
                timestamp: new Date().toISOString(),
                success: true
            });
            
            return result;
        } catch (error) {
            const endTime = performance.now();
            
            this.recordMetric({
                name,
                duration: endTime - startTime,
                error: error.message,
                timestamp: new Date().toISOString(),
                success: false
            });
            
            throw error;
        }
    }
    
    /**
     * 記錄效能指標
     * @param {Object} metric - 效能指標
     */
    recordMetric(metric) {
        this.metrics.push(metric);
        
        // 限制記錄數量
        if (this.metrics.length > this.maxMetrics) {
            this.metrics.shift();
        }
        
        // 如果是較慢的操作，發出警告
        if (metric.duration > 1000) {
            console.warn(`⚠️ 慢操作警告: ${metric.name} 耗時 ${metric.duration.toFixed(2)}ms`);
        }
    }
    
    /**
     * 獲取記憶體使用量
     */
    getMemoryUsage() {
        if (performance.memory) {
            return performance.memory.usedJSHeapSize;
        }
        return 0;
    }
      /**
     * 獲取效能統計
     */
    getStats() {
        const stats = {
            totalMeasurements: this.metrics.length,
            totalOperations: this.metrics.length, // 別名
            averageDuration: 0,
            slowestOperation: null,
            fastestOperation: null,
            successRate: 0,
            recentMetrics: this.metrics.slice(-10)
        };
        
        if (this.metrics.length > 0) {
            const durations = this.metrics.map(m => m.duration);
            const successes = this.metrics.filter(m => m.success).length;
            
            stats.averageDuration = durations.reduce((a, b) => a + b, 0) / durations.length;
            stats.slowestOperation = this.metrics.find(m => m.duration === Math.max(...durations));
            stats.fastestOperation = this.metrics.find(m => m.duration === Math.min(...durations));
            stats.successRate = (successes / this.metrics.length) * 100;
        }
        
        return stats;
    }
    
    /**
     * 清除效能記錄
     */
    clear() {
        const clearedCount = this.metrics.length;
        this.metrics = [];
        
        console.log(`🧹 清除了 ${clearedCount} 筆效能記錄`);
        return clearedCount;
    }
    
    /**
     * 啟用/停用效能監控
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
        console.log(`⏱️ 效能監控 ${enabled ? '啟用' : '停用'}`);
    }
}

// 全域容器實例
const container = new DIContainer();

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DIContainer, PerformanceMonitor, container };
} else {
    window.DIContainer = DIContainer;
    window.PerformanceMonitor = PerformanceMonitor;
    window.container = container;
}
