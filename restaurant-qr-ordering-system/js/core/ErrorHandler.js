// 統一錯誤處理系統
// 實現全域錯誤管理、異步錯誤處理、用戶友善錯誤提示

// 錯誤處理事件常數定義
const ERROR_EVENTS = {
    PAGE: {
        ERROR: 'page:error',
        LOADED: 'page:loaded',
        UNLOAD: 'page:unload'
    },
    UI: {
        TOAST_SHOW: 'ui:toast:show',
        TOAST_HIDE: 'ui:toast:hide',
        NOTIFICATION_SHOW: 'ui:notification:show',
        NOTIFICATION_HIDE: 'ui:notification:hide'
    },
    ERROR: {
        HANDLED: 'error:handled',
        RECOVERY_ATTEMPTED: 'error:recovery:attempted',
        RECOVERY_SUCCESS: 'error:recovery:success',
        RECOVERY_FAILED: 'error:recovery:failed'
    }
};

// 自定義錯誤類別
class AppError extends Error {
    constructor(message, code = 'UNKNOWN_ERROR', context = {}) {
        super(message);
        this.name = 'AppError';
        this.code = code;
        this.context = context;
        this.timestamp = new Date().toISOString();
        
        // 保持堆疊追蹤
        if (Error.captureStackTrace) {
            Error.captureStackTrace(this, AppError);
        }
    }
}

// 錯誤代碼常數
const ERROR_CODES = {
    // 驗證錯誤
    VALIDATION: {
        EMPTY_CART: 'VALIDATION_EMPTY_CART',
        INVALID_TABLE: 'VALIDATION_INVALID_TABLE',
        INVALID_PAYMENT: 'VALIDATION_INVALID_PAYMENT',
        REQUIRED_FIELD: 'VALIDATION_REQUIRED_FIELD'
    },
    
    // 網路錯誤
    NETWORK: {
        CONNECTION_FAILED: 'NETWORK_CONNECTION_FAILED',
        TIMEOUT: 'NETWORK_TIMEOUT',
        SERVER_ERROR: 'NETWORK_SERVER_ERROR'
    },
    
    // 資料錯誤
    DATA: {
        NOT_FOUND: 'DATA_NOT_FOUND',
        PARSE_ERROR: 'DATA_PARSE_ERROR',
        STORAGE_ERROR: 'DATA_STORAGE_ERROR'
    },
    
    // 系統錯誤
    SYSTEM: {
        INITIALIZATION_FAILED: 'SYSTEM_INITIALIZATION_FAILED',
        DEPENDENCY_MISSING: 'SYSTEM_DEPENDENCY_MISSING',
        PERMISSION_DENIED: 'SYSTEM_PERMISSION_DENIED'
    }
};

// 錯誤處理管理器 - 單例模式
class ErrorHandler {
    constructor() {
        if (ErrorHandler.instance) {
            return ErrorHandler.instance;
        }
        
        this.errorLog = [];
        this.maxLogSize = 100;
        this.isProduction = window.location.hostname !== 'localhost';
        this.eventBus = null;
        
        ErrorHandler.instance = this;
        
        // 初始化
        this.init();
    }
    
    static getInstance() {
        if (!ErrorHandler.instance) {
            ErrorHandler.instance = new ErrorHandler();
        }
        return ErrorHandler.instance;
    }
    
    init() {
        // 設置全域錯誤監聽
        this.setupGlobalErrorHandlers();
        
        // 初始化事件匯流排
        if (typeof EventBus !== 'undefined') {
            this.eventBus = EventBus.getInstance();
        }
        
        console.log('🛡️ ErrorHandler 初始化完成');
    }
    
    /**
     * 處理同步錯誤
     * @param {Error} error - 錯誤物件
     * @param {object} context - 上下文資訊
     */
    handleError(error, context = {}) {
        const errorInfo = {
            message: error.message,
            code: error.code || 'UNKNOWN_ERROR',
            stack: error.stack,
            context: {
                ...context,
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            },
            timestamp: new Date().toISOString()
        };
        
        // 記錄錯誤
        this.logError(errorInfo);
        
        // 顯示用戶友善訊息
        this.displayUserFriendlyMessage(error);
          // 觸發錯誤事件
        if (this.eventBus) {
            this.eventBus.emit(ERROR_EVENTS.PAGE.ERROR, errorInfo);
        }
        
        // 開發環境下輸出詳細錯誤
        if (!this.isProduction) {
            console.error('🚨 ErrorHandler 捕獲錯誤:', errorInfo);
        }
    }
    
    /**
     * 處理異步錯誤
     * @param {Promise} promise - Promise 物件
     * @param {object} context - 上下文資訊
     */
    async handleAsyncError(promise, context = {}) {
        try {
            return await promise;
        } catch (error) {
            this.handleError(error, { ...context, type: 'async' });
            throw error; // 重新拋出讓調用者決定如何處理
        }
    }
    
    /**
     * 創建應用錯誤
     * @param {string} message - 錯誤訊息
     * @param {string} code - 錯誤代碼
     * @param {object} context - 上下文資訊
     */
    createError(message, code, context = {}) {
        return new AppError(message, code, context);
    }
    
    /**
     * 記錄錯誤到日誌
     * @param {object} errorInfo - 錯誤資訊
     */
    logError(errorInfo) {
        // 添加到記憶體日誌
        this.errorLog.push(errorInfo);
        
        // 限制日誌大小
        if (this.errorLog.length > this.maxLogSize) {
            this.errorLog.shift();
        }
        
        // 保存到 localStorage (開發環境)
        if (!this.isProduction) {
            try {
                const savedErrors = JSON.parse(localStorage.getItem('errorLog') || '[]');
                savedErrors.push(errorInfo);
                
                // 只保留最近 50 筆錯誤
                if (savedErrors.length > 50) {
                    savedErrors.splice(0, savedErrors.length - 50);
                }
                
                localStorage.setItem('errorLog', JSON.stringify(savedErrors));
            } catch (e) {
                console.warn('無法保存錯誤日誌到 localStorage:', e);
            }
        }
    }
    
    /**
     * 顯示用戶友善的錯誤訊息
     * @param {Error} error - 錯誤物件
     */
    displayUserFriendlyMessage(error) {
        let userMessage = '系統發生錯誤，請稍後再試';
        
        // 根據錯誤代碼提供友善訊息
        if (error.code) {
            switch (error.code) {
                case ERROR_CODES.VALIDATION.EMPTY_CART:
                    userMessage = '購物車是空的，請先選擇商品';
                    break;
                case ERROR_CODES.VALIDATION.INVALID_TABLE:
                    userMessage = '座號格式不正確，請重新輸入';
                    break;
                case ERROR_CODES.VALIDATION.INVALID_PAYMENT:
                    userMessage = '請選擇付款方式';
                    break;
                case ERROR_CODES.NETWORK.CONNECTION_FAILED:
                    userMessage = '網路連線失敗，請檢查網路設定';
                    break;
                case ERROR_CODES.DATA.STORAGE_ERROR:
                    userMessage = '資料儲存失敗，請重新整理頁面';
                    break;
                default:
                    userMessage = error.message || userMessage;
            }
        }
        
        // 顯示錯誤提示
        this.showErrorToast(userMessage);
    }
      /**
     * 顯示錯誤 Toast 訊息
     * @param {string} message - 錯誤訊息
     */
    showErrorToast(message) {
        // 如果有 Toast 系統，使用 Toast
        if (typeof window.showToast === 'function') {
            window.showToast(message, 'error');
        } else if (this.eventBus) {
            // 使用事件系統
            this.eventBus.emit(ERROR_EVENTS.UI.TOAST_SHOW, {
                message,
                type: 'error',
                duration: 5000
            });
        } else {
            // 備用方案：使用 alert
            alert(message);
        }
    }
    
    /**
     * 設置全域錯誤監聽器
     */
    setupGlobalErrorHandlers() {
        // 捕獲 JavaScript 錯誤
        window.addEventListener('error', (event) => {
            this.handleGlobalError(event.error, {
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                type: 'javascript'
            });
        });
        
        // 捕獲未處理的 Promise 拒絕
        window.addEventListener('unhandledrejection', (event) => {
            this.handleUnhandledRejection(event.reason, {
                type: 'unhandled_rejection'
            });
        });
        
        // 捕獲資源載入錯誤
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.handleResourceError(event.target, {
                    type: 'resource_load_error'
                });
            }
        }, true);
    }
    
    /**
     * 處理全域 JavaScript 錯誤
     * @param {Error} error - 錯誤物件
     * @param {object} context - 上下文資訊
     */
    handleGlobalError(error, context = {}) {
        this.handleError(error, {
            ...context,
            source: 'global_error_handler'
        });
    }
    
    /**
     * 處理未處理的 Promise 拒絕
     * @param {*} reason - 拒絕原因
     * @param {object} context - 上下文資訊
     */
    handleUnhandledRejection(reason, context = {}) {
        const error = reason instanceof Error ? reason : new Error(String(reason));
        this.handleError(error, {
            ...context,
            source: 'unhandled_rejection'
        });
    }
    
    /**
     * 處理資源載入錯誤
     * @param {Element} target - 錯誤目標元素
     * @param {object} context - 上下文資訊
     */
    handleResourceError(target, context = {}) {
        const error = new Error(`資源載入失敗: ${target.src || target.href || '未知'}`);
        this.handleError(error, {
            ...context,
            source: 'resource_load_error',
            tagName: target.tagName,
            src: target.src || target.href
        });
    }
    
    /**
     * 獲取錯誤統計資訊
     */
    getErrorStats() {
        const stats = {
            totalErrors: this.errorLog.length,
            errorsByCode: {},
            errorsByType: {},
            recentErrors: this.errorLog.slice(-10)
        };
        
        this.errorLog.forEach(error => {
            // 按錯誤代碼統計
            const code = error.code || 'UNKNOWN';
            stats.errorsByCode[code] = (stats.errorsByCode[code] || 0) + 1;
            
            // 按錯誤類型統計
            const type = error.context?.type || 'unknown';
            stats.errorsByType[type] = (stats.errorsByType[type] || 0) + 1;
        });
        
        return stats;
    }
    
    /**
     * 清空錯誤日誌
     */
    clearErrorLog() {
        const clearedCount = this.errorLog.length;
        this.errorLog = [];
        
        // 清空 localStorage 中的錯誤日誌
        try {
            localStorage.removeItem('errorLog');
        } catch (e) {
            console.warn('無法清空 localStorage 中的錯誤日誌:', e);
        }
        
        console.log(`🧹 ErrorHandler: 清空了 ${clearedCount} 筆錯誤日誌`);
        return clearedCount;
    }

    /**
     * 錯誤分類
     * @param {Error} error - 錯誤物件
     * @returns {string} 錯誤類別
     */
    categorizeError(error) {
        if (!error.code) return 'UNKNOWN';
        
        const code = error.code;
        
        if (code.startsWith('VALIDATION_')) return 'VALIDATION';
        if (code.startsWith('NETWORK_')) return 'NETWORK';
        if (code.startsWith('DATA_')) return 'DATA';
        if (code.startsWith('SYSTEM_')) return 'SYSTEM';
        
        return 'UNKNOWN';
    }

    /**
     * 獲取友善錯誤訊息
     * @param {Error} error - 錯誤物件
     * @returns {string} 友善錯誤訊息
     */
    getFriendlyMessage(error) {
        if (error.code) {
            switch (error.code) {
                case ERROR_CODES.VALIDATION.EMPTY_CART:
                    return '購物車是空的，請先選擇商品再進行結帳';
                case ERROR_CODES.VALIDATION.INVALID_TABLE:
                    return '座號格式不正確，請重新輸入正確的座號';
                case ERROR_CODES.VALIDATION.INVALID_PAYMENT:
                    return '請選擇一種付款方式';
                case ERROR_CODES.NETWORK.CONNECTION_FAILED:
                    return '網路連線失敗，請檢查網路設定後重試';
                case ERROR_CODES.NETWORK.TIMEOUT:
                    return '網路連線超時，請稍後再試';
                case ERROR_CODES.DATA.STORAGE_ERROR:
                    return '資料儲存失敗，請重新整理頁面後再試';
                default:
                    return error.message || '發生未預期的錯誤，請聯繫技術支援';
            }
        }
        
        return '發生未預期的錯誤，請稍後再試或聯繫技術支援';
    }

    /**
     * 獲取錯誤恢復建議
     * @param {Error} error - 錯誤物件
     * @returns {string[]} 恢復建議陣列
     */
    getRecoverySuggestions(error) {
        const suggestions = [];
        
        if (error.code) {
            switch (error.code) {
                case ERROR_CODES.VALIDATION.EMPTY_CART:
                    suggestions.push('返回菜單頁面選擇商品');
                    suggestions.push('檢查購物車是否有商品');
                    break;
                case ERROR_CODES.VALIDATION.INVALID_TABLE:
                    suggestions.push('重新輸入座號');
                    suggestions.push('確認座號格式正確（例如：A12, B01）');
                    break;
                case ERROR_CODES.NETWORK.CONNECTION_FAILED:
                    suggestions.push('檢查網路連線');
                    suggestions.push('重新整理頁面');
                    suggestions.push('稍後再試');
                    break;
                case ERROR_CODES.DATA.STORAGE_ERROR:
                    suggestions.push('清除瀏覽器快取');
                    suggestions.push('重新整理頁面');
                    break;
                default:
                    suggestions.push('重新整理頁面');
                    suggestions.push('清除瀏覽器快取');
                    suggestions.push('聯繫技術支援');
            }
        } else {
            suggestions.push('重新整理頁面');
            suggestions.push('稍後再試');
        }
        
        return suggestions;
    }

    /**
     * 嘗試自動恢復
     * @param {Error} error - 錯誤物件
     * @returns {boolean} 是否成功恢復
     */
    attemptRecovery(error) {
        try {
            if (error.code === ERROR_CODES.DATA.STORAGE_ERROR) {
                return this.clearOldData();
            }
            
            if (error.code === ERROR_CODES.VALIDATION.EMPTY_CART) {
                // 嘗試重新載入購物車
                return this.reloadCart();
            }
            
            return false;
        } catch (recoveryError) {
            console.warn('自動恢復失敗:', recoveryError);
            return false;
        }
    }

    /**
     * 清除舊資料
     * @returns {boolean} 是否成功
     */
    clearOldData() {
        try {
            // 清除舊的錯誤日誌
            const oldErrorLog = localStorage.getItem('errorLog');
            if (oldErrorLog) {
                localStorage.removeItem('errorLog');
            }
            
            // 清除過期的快取資料
            const keys = Object.keys(localStorage);
            const now = Date.now();
            
            keys.forEach(key => {
                if (key.includes('_timestamp')) {
                    const timestamp = parseInt(localStorage.getItem(key));
                    // 清除超過1天的資料
                    if (now - timestamp > 24 * 60 * 60 * 1000) {
                        const dataKey = key.replace('_timestamp', '');
                        localStorage.removeItem(dataKey);
                        localStorage.removeItem(key);
                    }
                }
            });
            
            return true;
        } catch (error) {
            console.warn('清除舊資料失敗:', error);
            return false;
        }
    }

    /**
     * 重新載入購物車
     * @returns {boolean} 是否成功
     */
    reloadCart() {
        try {
            if (window.location.pathname.includes('cart')) {
                window.location.reload();
                return true;
            }
            return false;
        } catch (error) {
            console.warn('重新載入購物車失敗:', error);
            return false;
        }
    }

    /**
     * 生成錯誤報告
     * @returns {string} 錯誤報告
     */
    generateErrorReport() {
        const stats = this.getErrorStats();
        
        let report = '=== 錯誤統計報告 ===\n';
        report += `總錯誤數: ${stats.totalErrors}\n`;
        report += `報告時間: ${new Date().toLocaleString()}\n\n`;
        
        report += '=== 錯誤代碼統計 ===\n';
        Object.entries(stats.errorsByCode).forEach(([code, count]) => {
            report += `${code}: ${count} 次\n`;
        });
        
        report += '\n=== 錯誤類型統計 ===\n';
        Object.entries(stats.errorsByType).forEach(([type, count]) => {
            report += `${type}: ${count} 次\n`;
        });
        
        report += '\n=== 最近錯誤 ===\n';
        stats.recentErrors.forEach((error, index) => {
            report += `${index + 1}. [${error.code || 'UNKNOWN'}] ${error.message}\n`;
            report += `   時間: ${error.timestamp}\n`;
            if (error.context) {
                report += `   上下文: ${JSON.stringify(error.context)}\n`;
            }
            report += '\n';
        });
        
        return report;
    }

    /**
     * 匯出錯誤日誌
     * @returns {string} JSON 格式的錯誤日誌
     */
    exportErrorLog() {
        return JSON.stringify({
            exportTime: new Date().toISOString(),
            totalErrors: this.errorLog.length,
            errors: this.errorLog
        }, null, 2);
    }

    /**
     * 通知用戶
     * @param {string} message - 通知訊息
     * @param {string} type - 通知類型
     */
    notifyUser(message, type = 'error') {
        // 批次處理通知，避免過多通知
        if (!this.notificationQueue) {
            this.notificationQueue = [];
        }
        
        this.notificationQueue.push({ message, type, timestamp: Date.now() });
        
        // 防抖處理
        if (this.notificationTimer) {
            clearTimeout(this.notificationTimer);
        }
        
        this.notificationTimer = setTimeout(() => {
            this.flushNotifications();
        }, 100);
    }

    /**
     * 刷新通知佇列
     */
    flushNotifications() {
        if (!this.notificationQueue || this.notificationQueue.length === 0) {
            return;
        }
          // 只顯示最新的通知
        const latestNotification = this.notificationQueue[this.notificationQueue.length - 1];
        
        if (this.eventBus) {
            this.eventBus.emit(ERROR_EVENTS.UI.NOTIFICATION_SHOW, latestNotification);
        }
        
        this.notificationQueue = [];
    }

    // ...existing code...
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ErrorHandler, AppError, ERROR_CODES, ERROR_EVENTS };
} else {
    window.ErrorHandler = ErrorHandler;
    window.AppError = AppError;
    window.ERROR_CODES = ERROR_CODES;
    window.ERROR_EVENTS = ERROR_EVENTS;
}
