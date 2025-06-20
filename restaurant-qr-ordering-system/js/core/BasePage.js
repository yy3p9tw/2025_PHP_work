// 基礎頁面類別 - 提供共同功能
// 實現繼承架構，所有頁面都從此類別繼承

class BasePage {
    constructor(pageName, dependencies = {}) {
        this.pageName = pageName;
        this.isInitialized = false;
        this.isDestroyed = false;
        
        // 依賴注入
        this.eventBus = dependencies.eventBus || EventBus.getInstance();
        this.errorHandler = dependencies.errorHandler || ErrorHandler.getInstance();
        this.storageService = dependencies.storageService || new LocalStorageService();
        
        // 頁面狀態
        this.currentTable = null;
        this.elements = {};
        this.eventListeners = [];
        this.timers = [];
        
        // 效能監控
        this.performanceMonitor = this.createPerformanceMonitor();
        
        console.log(`📄 ${this.pageName} 頁面建立中...`);
    }
    
    /**
     * 初始化頁面
     */
    async init() {
        if (this.isInitialized) {
            console.warn(`⚠️ ${this.pageName} 頁面已經初始化`);
            return;
        }
        
        const monitor = this.performanceMonitor.start('page_init');
        
        try {
            console.log(`🚀 ${this.pageName} 頁面初始化中...`);
            
            // 驗證座號
            await this.validateTable();
            
            // 快取 DOM 元素
            this.cacheElements();
            
            // 設置事件監聽器
            this.setupEventListeners();
            
            // 載入資料
            await this.loadData();
            
            // 渲染頁面
            await this.render();
            
            // 設置頁面專用功能
            await this.setupPageSpecific();
            
            this.isInitialized = true;
            
            // 觸發初始化完成事件
            this.eventBus.emit(EVENTS.PAGE.INITIALIZED, {
                pageName: this.pageName,
                timestamp: Date.now()
            });
            
            console.log(`✅ ${this.pageName} 頁面初始化完成`);
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                page: this.pageName,
                action: 'init'
            });
            throw error;
        } finally {
            monitor.end();
        }
    }
    
    /**
     * 驗證座號
     */
    async validateTable() {
        try {
            const savedTable = this.storageService.getItem('currentTable');
            
            if (!savedTable) {
                throw this.errorHandler.createError(
                    '沒有找到座號資訊',
                    ERROR_CODES.VALIDATION.INVALID_TABLE,
                    { page: this.pageName }
                );
            }
            
            // 檢查座號是否過期 (30分鐘)
            const tableData = savedTable;
            const now = Date.now();
            const tableTime = new Date(tableData.timestamp).getTime();
            const maxAge = 30 * 60 * 1000; // 30分鐘
            
            if (now - tableTime > maxAge) {
                this.storageService.removeItem('currentTable');
                throw this.errorHandler.createError(
                    '座號已過期，請重新掃描QR Code',
                    ERROR_CODES.VALIDATION.INVALID_TABLE,
                    { page: this.pageName, expired: true }
                );
            }
            
            this.currentTable = tableData.number;
            this.updateTableDisplay();
            
        } catch (error) {
            // 如果驗證失敗，導向首頁
            console.warn(`⚠️ ${this.pageName} 座號驗證失敗:`, error.message);
            this.redirectToHome();
            throw error;
        }
    }
    
    /**
     * 更新座號顯示
     */
    updateTableDisplay() {
        const tableDisplay = document.getElementById('currentTableNumber');
        if (tableDisplay && this.currentTable) {
            tableDisplay.textContent = `座號：${this.currentTable}`;
        }
    }
    
    /**
     * 快取常用 DOM 元素
     */
    cacheElements() {
        // 基礎元素
        this.elements = {
            loading: document.getElementById('loading'),
            errorMessage: document.getElementById('errorMessage'),
            tableDisplay: document.getElementById('currentTableNumber'),
            backButton: document.querySelector('.back-btn'),
            ...this.getPageSpecificElements()
        };
    }
    
    /**
     * 獲取頁面專用元素 (由子類別覆寫)
     */
    getPageSpecificElements() {
        return {};
    }
    
    /**
     * 設置事件監聽器
     */
    setupEventListeners() {
        // 返回按鈕
        if (this.elements.backButton) {
            this.addEventListener(
                this.elements.backButton,
                'click',
                () => this.handleBackButton()
            );
        }
        
        // 全域鍵盤事件
        this.addEventListener(
            document,
            'keydown',
            (e) => this.handleGlobalKeydown(e)
        );
        
        // 頁面可見性變化
        this.addEventListener(
            document,
            'visibilitychange',
            () => this.handleVisibilityChange()
        );
        
        // 設置頁面專用事件監聽器
        this.setupPageSpecificEventListeners();
    }
    
    /**
     * 添加事件監聽器 (統一管理)
     */
    addEventListener(element, event, handler, options = {}) {
        const boundHandler = handler.bind(this);
        element.addEventListener(event, boundHandler, options);
        
        // 記錄監聽器以便後續清理
        this.eventListeners.push({
            element,
            event,
            handler: boundHandler,
            options
        });
        
        return boundHandler;
    }
    
    /**
     * 設置頁面專用事件監聽器 (由子類別覆寫)
     */
    setupPageSpecificEventListeners() {
        // 由子類別實現
    }
    
    /**
     * 載入資料 (由子類別覆寫)
     */
    async loadData() {
        // 由子類別實現
    }
    
    /**
     * 渲染頁面 (由子類別覆寫)
     */
    async render() {
        // 由子類別實現
    }
    
    /**
     * 設置頁面專用功能 (由子類別覆寫)
     */
    async setupPageSpecific() {
        // 由子類別實現
    }
    
    /**
     * 處理返回按鈕
     */
    handleBackButton() {
        this.eventBus.emit(EVENTS.NAVIGATION.BACK_PRESSED, {
            page: this.pageName
        });
        
        // 預設行為：返回上一頁
        window.history.back();
    }
    
    /**
     * 處理全域鍵盤事件
     */
    handleGlobalKeydown(event) {
        switch (event.key) {
            case 'Escape':
                this.handleEscapeKey(event);
                break;
            case 'F5':
                // 防止意外重新整理
                if (!event.ctrlKey) {
                    event.preventDefault();
                    this.showConfirmRefresh();
                }
                break;
        }
    }
    
    /**
     * 處理 ESC 鍵
     */
    handleEscapeKey(event) {
        // 由子類別覆寫處理特定邏輯
        console.log(`ESC key pressed on ${this.pageName}`);
    }
    
    /**
     * 處理頁面可見性變化
     */
    handleVisibilityChange() {
        if (document.hidden) {
            console.log(`📱 ${this.pageName} 頁面隱藏`);
            // 頁面隱藏時的處理邏輯
            this.onPageHidden();
        } else {
            console.log(`📱 ${this.pageName} 頁面顯示`);
            // 頁面顯示時的處理邏輯
            this.onPageVisible();
        }
    }
    
    /**
     * 頁面隱藏時的處理
     */
    onPageHidden() {
        // 由子類別覆寫
    }
    
    /**
     * 頁面顯示時的處理
     */
    onPageVisible() {
        // 由子類別覆寫
    }
    
    /**
     * 顯示載入狀態
     */
    showLoading(message = '載入中...') {
        if (this.elements.loading) {
            this.elements.loading.textContent = message;
            this.elements.loading.classList.remove('d-none');
        }
        
        this.eventBus.emit(EVENTS.UI.LOADING_START, {
            page: this.pageName,
            message
        });
    }
    
    /**
     * 隱藏載入狀態
     */
    hideLoading() {
        if (this.elements.loading) {
            this.elements.loading.classList.add('d-none');
        }
        
        this.eventBus.emit(EVENTS.UI.LOADING_END, {
            page: this.pageName
        });
    }
    
    /**
     * 顯示錯誤訊息
     */
    showError(message, duration = 5000) {
        if (this.elements.errorMessage) {
            this.elements.errorMessage.textContent = message;
            this.elements.errorMessage.classList.remove('d-none');
            
            // 自動隱藏
            if (duration > 0) {
                this.setTimeout(() => {
                    this.hideError();
                }, duration);
            }
        }
    }
    
    /**
     * 隱藏錯誤訊息
     */
    hideError() {
        if (this.elements.errorMessage) {
            this.elements.errorMessage.classList.add('d-none');
        }
    }
    
    /**
     * 顯示確認重新整理對話框
     */
    showConfirmRefresh() {
        if (confirm('確定要重新整理頁面嗎？未保存的資料可能會遺失。')) {
            window.location.reload();
        }
    }
    
    /**
     * 導向首頁
     */
    redirectToHome() {
        console.log(`🏠 從 ${this.pageName} 導向首頁`);
        window.location.href = 'index.html';
    }
    
    /**
     * 延遲執行 (統一管理計時器)
     */
    setTimeout(callback, delay) {
        const timerId = setTimeout(() => {
            callback();
            // 從計時器列表中移除
            const index = this.timers.indexOf(timerId);
            if (index > -1) {
                this.timers.splice(index, 1);
            }
        }, delay);
        
        this.timers.push(timerId);
        return timerId;
    }
    
    /**
     * 清除計時器
     */
    clearTimeout(timerId) {
        clearTimeout(timerId);
        const index = this.timers.indexOf(timerId);
        if (index > -1) {
            this.timers.splice(index, 1);
        }
    }
    
    /**
     * 創建效能監控器
     */
    createPerformanceMonitor() {
        return {
            start: (actionName) => {
                const startTime = performance.now();
                return {
                    end: () => {
                        const endTime = performance.now();
                        const duration = endTime - startTime;
                        
                        console.log(`⏱️ ${this.pageName}.${actionName}: ${duration.toFixed(2)}ms`);
                        
                        // 記錄效能數據
                        this.recordPerformanceMetric(actionName, duration);
                        
                        return duration;
                    }
                };
            }
        };
    }
    
    /**
     * 記錄效能指標
     */
    recordPerformanceMetric(action, duration) {
        try {
            const metrics = this.storageService.getItem('performanceMetrics') || [];
            
            metrics.push({
                page: this.pageName,
                action,
                duration,
                timestamp: new Date().toISOString()
            });
            
            // 只保留最近 100 筆記錄
            if (metrics.length > 100) {
                metrics.splice(0, metrics.length - 100);
            }
            
            this.storageService.setItem('performanceMetrics', metrics);
        } catch (error) {
            console.warn('記錄效能指標失敗:', error);
        }
    }
    
    /**
     * 銷毀頁面
     */
    destroy() {
        if (this.isDestroyed) {
            console.warn(`⚠️ ${this.pageName} 頁面已經銷毀`);
            return;
        }
        
        console.log(`🗑️ 銷毀 ${this.pageName} 頁面...`);
        
        // 移除所有事件監聽器
        this.eventListeners.forEach(({ element, event, handler, options }) => {
            element.removeEventListener(event, handler, options);
        });
        this.eventListeners = [];
        
        // 清除所有計時器
        this.timers.forEach(timerId => {
            clearTimeout(timerId);
        });
        this.timers = [];
        
        // 清理 DOM 參照
        this.elements = {};
        
        // 觸發銷毀事件
        this.eventBus.emit(EVENTS.PAGE.DESTROYED, {
            pageName: this.pageName,
            timestamp: Date.now()
        });
        
        this.isDestroyed = true;
        
        console.log(`✅ ${this.pageName} 頁面銷毀完成`);
    }
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BasePage };
} else {
    window.BasePage = BasePage;
}
