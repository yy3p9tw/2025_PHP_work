// OOP 架構核心載入器
// 統一載入和初始化所有核心模組

(function() {
    'use strict';
    
    console.log('🚀 開始載入 OOP 架構核心模組...');
    
    // 檢查必要的全域對象
    const requiredGlobals = [
        'EventBus', 'EVENTS',
        'ErrorHandler', 'AppError', 'ERROR_CODES',
        'IStorageService', 'LocalStorageService', 'MockStorageService',
        'BasePage',
        'CartManager',
        'OrderManager',
        'ModalManager',
        'DIContainer', 'PerformanceMonitor', 'container'
    ];
    
    const missingGlobals = requiredGlobals.filter(global => typeof window[global] === 'undefined');
    
    if (missingGlobals.length > 0) {
        console.error('❌ 缺少必要的核心模組:', missingGlobals);
        throw new Error(`缺少必要的核心模組: ${missingGlobals.join(', ')}`);
    }
    
    console.log('✅ 所有核心模組載入完成');
    
    // 初始化應用程式
    class Application {
        constructor() {
            this.isInitialized = false;
            this.container = window.container;
            this.eventBus = null;
            this.errorHandler = null;
            this.performanceMonitor = null;
            
            console.log('📱 Application 建立中...');
        }
        
        /**
         * 初始化應用程式
         */
        async init() {
            if (this.isInitialized) {
                console.warn('⚠️ 應用程式已經初始化');
                return;
            }
            
            try {
                console.log('🔧 初始化應用程式...');
                
                // 獲取核心服務
                this.eventBus = this.container.get('eventBus');
                this.errorHandler = this.container.get('errorHandler');
                this.performanceMonitor = this.container.get('performanceMonitor');
                
                // 設置全域事件監聽
                this.setupGlobalEventListeners();
                
                // 設置效能監控
                this.setupPerformanceMonitoring();
                
                // 設置錯誤追蹤
                this.setupErrorTracking();
                
                // 初始化完成
                this.isInitialized = true;
                
                // 觸發應用程式準備事件
                this.eventBus.emit('app:ready', {
                    timestamp: Date.now(),
                    version: '3.0',
                    modules: requiredGlobals
                });
                
                console.log('✅ 應用程式初始化完成');
                
            } catch (error) {
                console.error('❌ 應用程式初始化失敗:', error);
                throw error;
            }
        }
        
        /**
         * 設置全域事件監聽
         */
        setupGlobalEventListeners() {
            // 監聽所有頁面事件
            this.eventBus.on(EVENTS.PAGE.INITIALIZED, (data) => {
                console.log(`📄 頁面初始化: ${data.pageName}`);
            });
            
            this.eventBus.on(EVENTS.PAGE.DESTROYED, (data) => {
                console.log(`🗑️ 頁面銷毀: ${data.pageName}`);
            });
            
            this.eventBus.on(EVENTS.PAGE.ERROR, (data) => {
                console.error(`🚨 頁面錯誤: ${data.context?.page || 'unknown'}`, data);
            });
            
            // 監聽購物車事件
            this.eventBus.on(EVENTS.CART.ITEM_ADDED, (data) => {
                console.log(`🛒 商品加入購物車: ${data.item?.name || 'unknown'} x${data.quantity}`);
                this.updateCartBadge(data.itemCount);
            });
            
            this.eventBus.on(EVENTS.CART.ITEM_REMOVED, (data) => {
                console.log(`🗑️ 商品從購物車移除: ${data.item?.name || 'unknown'}`);
                this.updateCartBadge(data.itemCount);
            });
            
            this.eventBus.on(EVENTS.CART.QUANTITY_UPDATED, (data) => {
                console.log(`🔄 購物車數量更新: ${data.itemId} ${data.oldQuantity} → ${data.newQuantity}`);
                this.updateCartBadge(data.itemCount);
            });
            
            this.eventBus.on(EVENTS.CART.CLEARED, (data) => {
                console.log(`🧹 購物車已清空，移除了 ${data.clearedCount} 個商品`);
                this.updateCartBadge(0);
            });
            
            // 監聽訂單事件
            this.eventBus.on(EVENTS.ORDER.CREATED, (data) => {
                console.log(`📋 訂單創建: ${data.order?.orderNumber || 'unknown'}`);
            });
            
            this.eventBus.on(EVENTS.ORDER.SUBMITTED, (data) => {
                console.log(`🚀 訂單提交: ${data.order?.orderNumber || 'unknown'}`);
            });
            
            // 監聽 UI 事件
            this.eventBus.on(EVENTS.UI.LOADING_START, (data) => {
                console.log(`⏳ 載入開始: ${data.page || 'unknown'}.${data.action || 'unknown'}`);
            });
            
            this.eventBus.on(EVENTS.UI.LOADING_END, (data) => {
                console.log(`✅ 載入結束: ${data.page || 'unknown'}.${data.action || 'unknown'}`);
            });
            
            this.eventBus.on(EVENTS.UI.TOAST_SHOW, (data) => {
                this.showToast(data.message, data.type, data.duration);
            });
        }
        
        /**
         * 設置效能監控
         */
        setupPerformanceMonitoring() {
            // 監控頁面載入時間
            window.addEventListener('load', () => {
                const loadTime = performance.now();
                this.performanceMonitor.recordMetric({
                    name: 'page_load',
                    duration: loadTime,
                    timestamp: new Date().toISOString(),
                    success: true
                });
                
                console.log(`⏱️ 頁面載入時間: ${loadTime.toFixed(2)}ms`);
            });
            
            // 監控記憶體使用
            if (performance.memory) {
                setInterval(() => {
                    const usage = performance.memory;
                    const usagePercent = (usage.usedJSHeapSize / usage.jsHeapSizeLimit) * 100;
                    
                    if (usagePercent > 80) {
                        console.warn(`⚠️ 記憶體使用率高: ${usagePercent.toFixed(1)}%`);
                    }
                }, 30000); // 每 30 秒檢查一次
            }
        }
        
        /**
         * 設置錯誤追蹤
         */
        setupErrorTracking() {
            // 追蹤未捕獲的 Promise 拒絕
            window.addEventListener('unhandledrejection', (event) => {
                console.error('🚨 未處理的 Promise 拒絕:', event.reason);
                
                this.eventBus.emit(EVENTS.PAGE.ERROR, {
                    type: 'unhandled_promise_rejection',
                    error: event.reason,
                    timestamp: Date.now()
                });
            });
            
            // 追蹤資源載入錯誤
            window.addEventListener('error', (event) => {
                if (event.target !== window) {
                    console.error('🚨 資源載入錯誤:', event.target.src || event.target.href);
                    
                    this.eventBus.emit(EVENTS.PAGE.ERROR, {
                        type: 'resource_load_error',
                        resource: event.target.src || event.target.href,
                        tagName: event.target.tagName,
                        timestamp: Date.now()
                    });
                }
            }, true);
        }
        
        /**
         * 更新購物車徽章
         */
        updateCartBadge(count) {
            const badges = document.querySelectorAll('.cart-badge, .badge-cart');
            
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count.toString();
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            });
        }
        
        /**
         * 顯示 Toast 訊息
         */
        showToast(message, type = 'info', duration = 3000) {
            // 如果存在外部 Toast 函數，使用它
            if (typeof window.showToast === 'function') {
                window.showToast(message, type, duration);
                return;
            }
            
            // 簡單的 Toast 實現
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${this.getToastColor(type)};
                color: white;
                padding: 12px 16px;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                z-index: 9999;
                max-width: 300px;
                font-size: 14px;
                opacity: 0;
                transform: translateY(-10px);
                transition: all 0.3s ease;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // 顯示動畫
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 10);
            
            // 自動移除
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, duration);
        }
        
        /**
         * 獲取 Toast 顏色
         */
        getToastColor(type) {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };
            
            return colors[type] || colors.info;
        }
        
        /**
         * 獲取應用程式狀態
         */
        getStatus() {
            return {
                initialized: this.isInitialized,
                eventBusStats: this.eventBus?.getStats(),
                errorStats: this.errorHandler?.getErrorStats(),
                performanceStats: this.performanceMonitor?.getStats(),
                containerStats: this.container?.getStats()
            };
        }
        
        /**
         * 除錯模式
         */
        debug() {
            console.group('🔍 應用程式除錯資訊');
            console.log('狀態:', this.getStatus());
            
            if (this.container) {
                this.container.debugDependencyGraph();
            }
            
            console.groupEnd();
        }
    }
    
    // 創建全域應用程式實例
    window.app = new Application();
    
    // 當 DOM 載入完成時自動初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.app.init().catch(error => {
                console.error('❌ 應用程式自動初始化失敗:', error);
            });
        });
    } else {
        // DOM 已經載入完成
        window.app.init().catch(error => {
            console.error('❌ 應用程式自動初始化失敗:', error);
        });
    }
    
    console.log('🎉 OOP 架構核心載入完成！');
    
})();
