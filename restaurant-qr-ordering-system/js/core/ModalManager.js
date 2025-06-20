// 模態框管理器 - 統一管理所有模態框
// 提供開啟、關閉、事件處理等功能

class ModalManager {
    constructor() {
        this.activeModals = new Set();
        this.eventBus = EventBus.getInstance();
        this.originalBodyOverflow = null;
        
        // 設置全域事件監聽
        this.setupGlobalEventListeners();
        
        console.log('🗂️ ModalManager 初始化完成');
    }
    
    /**
     * 設置全域事件監聽器
     */
    setupGlobalEventListeners() {
        // ESC 鍵關閉模態框
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModals.size > 0) {
                this.closeTopModal();
            }
        });
        
        // 點擊背景關閉模態框
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal') && e.target.classList.contains('show')) {
                this.closeModal(e.target);
            }
        });
    }
    
    /**
     * 開啟模態框
     * @param {HTMLElement|string} modal - 模態框元素或ID
     * @param {Object} options - 選項
     */
    openModal(modal, options = {}) {
        const modalElement = this.getModalElement(modal);
        
        if (!modalElement) {
            console.error('找不到指定的模態框:', modal);
            return false;
        }
        
        if (this.activeModals.has(modalElement)) {
            console.warn('模態框已經開啟');
            return false;
        }
        
        // 防止背景滾動
        this.preventBackgroundScroll();
        
        // 添加到活動模態框列表
        this.activeModals.add(modalElement);
        
        // 顯示模態框
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        
        // 聚焦到模態框
        modalElement.focus();
        
        // 設置關閉按鈕事件
        this.setupCloseButtons(modalElement);
        
        // 觸發開啟事件
        this.eventBus.emit(EVENTS.UI.MODAL_OPENED, {
            modal: modalElement.id || 'unknown',
            element: modalElement,
            options
        });
        
        console.log(`📖 開啟模態框: ${modalElement.id || 'unknown'}`);
        
        return true;
    }
    
    /**
     * 關閉模態框
     * @param {HTMLElement|string} modal - 模態框元素或ID
     */
    closeModal(modal) {
        const modalElement = this.getModalElement(modal);
        
        if (!modalElement) {
            console.error('找不到指定的模態框:', modal);
            return false;
        }
        
        if (!this.activeModals.has(modalElement)) {
            console.warn('模態框沒有開啟');
            return false;
        }
        
        // 從活動模態框列表移除
        this.activeModals.delete(modalElement);
        
        // 隱藏模態框
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        
        // 延遲隱藏以配合動畫
        setTimeout(() => {
            if (!modalElement.classList.contains('show')) {
                modalElement.style.display = 'none';
            }
        }, 300);
        
        // 如果沒有其他模態框，恢復背景滾動
        if (this.activeModals.size === 0) {
            this.restoreBackgroundScroll();
        }
        
        // 觸發關閉事件
        this.eventBus.emit(EVENTS.UI.MODAL_CLOSED, {
            modal: modalElement.id || 'unknown',
            element: modalElement
        });
        
        console.log(`📕 關閉模態框: ${modalElement.id || 'unknown'}`);
        
        return true;
    }
    
    /**
     * 關閉最上層模態框
     */
    closeTopModal() {
        if (this.activeModals.size === 0) return false;
        
        // 獲取最後添加的模態框
        const modalsArray = Array.from(this.activeModals);
        const topModal = modalsArray[modalsArray.length - 1];
        
        return this.closeModal(topModal);
    }
    
    /**
     * 關閉所有模態框
     */
    closeAll() {
        const modalsToClose = Array.from(this.activeModals);
        
        modalsToClose.forEach(modal => {
            this.closeModal(modal);
        });
        
        console.log(`📚 關閉了 ${modalsToClose.length} 個模態框`);
        
        return modalsToClose.length;
    }
    
    /**
     * 檢查是否有模態框開啟
     */
    isModalOpen(modal = null) {
        if (modal) {
            const modalElement = this.getModalElement(modal);
            return modalElement ? this.activeModals.has(modalElement) : false;
        }
        
        return this.activeModals.size > 0;
    }
    
    /**
     * 獲取模態框元素
     * @param {HTMLElement|string} modal - 模態框元素或ID
     */
    getModalElement(modal) {
        if (typeof modal === 'string') {
            return document.getElementById(modal);
        } else if (modal instanceof HTMLElement) {
            return modal;
        }
        
        return null;
    }
    
    /**
     * 設置關閉按鈕事件
     * @param {HTMLElement} modalElement - 模態框元素
     */
    setupCloseButtons(modalElement) {
        // 查找所有關閉按鈕
        const closeButtons = modalElement.querySelectorAll('[data-dismiss="modal"], .modal-close, .btn-close');
        
        closeButtons.forEach(button => {
            // 移除舊的事件監聽器（如果存在）
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            // 添加新的事件監聽器
            newButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeModal(modalElement);
            });
        });
    }
    
    /**
     * 防止背景滾動
     */
    preventBackgroundScroll() {
        if (this.originalBodyOverflow === null) {
            this.originalBodyOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            document.body.classList.add('modal-open');
        }
    }
    
    /**
     * 恢復背景滾動
     */
    restoreBackgroundScroll() {
        if (this.originalBodyOverflow !== null) {
            document.body.style.overflow = this.originalBodyOverflow;
            document.body.classList.remove('modal-open');
            this.originalBodyOverflow = null;
        }
    }
    
    /**
     * 創建確認對話框
     * @param {string} message - 確認訊息
     * @param {Object} options - 選項
     */
    confirm(message, options = {}) {
        return new Promise((resolve) => {
            const {
                title = '確認',
                confirmText = '確定',
                cancelText = '取消',
                confirmClass = 'btn-primary',
                cancelClass = 'btn-secondary'
            } = options;
            
            // 創建模態框 HTML
            const modalHtml = `
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ${cancelClass}" id="confirmCancel">${cancelText}</button>
                                <button type="button" class="btn ${confirmClass}" id="confirmOk">${confirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // 添加到頁面
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer);
            
            const modal = modalContainer.querySelector('#confirmModal');
            const confirmBtn = modal.querySelector('#confirmOk');
            const cancelBtn = modal.querySelector('#confirmCancel');
            
            // 設置事件監聽器
            const handleConfirm = () => {
                this.closeModal(modal);
                setTimeout(() => {
                    document.body.removeChild(modalContainer);
                }, 300);
                resolve(true);
            };
            
            const handleCancel = () => {
                this.closeModal(modal);
                setTimeout(() => {
                    document.body.removeChild(modalContainer);
                }, 300);
                resolve(false);
            };
            
            confirmBtn.addEventListener('click', handleConfirm);
            cancelBtn.addEventListener('click', handleCancel);
            
            // ESC 鍵取消
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleEsc);
                    handleCancel();
                }
            };
            
            document.addEventListener('keydown', handleEsc);
            
            // 開啟模態框
            this.openModal(modal);
            
            // 聚焦確認按鈕
            setTimeout(() => {
                confirmBtn.focus();
            }, 100);
        });
    }
    
    /**
     * 創建警告對話框
     * @param {string} message - 警告訊息
     * @param {Object} options - 選項
     */
    alert(message, options = {}) {
        return new Promise((resolve) => {
            const {
                title = '提示',
                okText = '確定',
                okClass = 'btn-primary'
            } = options;
            
            // 創建模態框 HTML
            const modalHtml = `
                <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ${okClass}" id="alertOk">${okText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // 添加到頁面
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer);
            
            const modal = modalContainer.querySelector('#alertModal');
            const okBtn = modal.querySelector('#alertOk');
            
            // 設置事件監聽器
            const handleOk = () => {
                this.closeModal(modal);
                setTimeout(() => {
                    document.body.removeChild(modalContainer);
                }, 300);
                resolve();
            };
            
            okBtn.addEventListener('click', handleOk);
            
            // ESC 鍵關閉
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleEsc);
                    handleOk();
                }
            };
            
            document.addEventListener('keydown', handleEsc);
            
            // 開啟模態框
            this.openModal(modal);
            
            // 聚焦確定按鈕
            setTimeout(() => {
                okBtn.focus();
            }, 100);
        });
    }
    
    /**
     * 獲取活動模態框列表
     */
    getActiveModals() {
        return Array.from(this.activeModals);
    }
    
    /**
     * 獲取模態框統計資訊
     */
    getStats() {
        return {
            activeCount: this.activeModals.size,
            activeModals: this.getActiveModals().map(modal => ({
                id: modal.id || 'unknown',
                className: modal.className
            }))
        };
    }
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ModalManager };
} else {
    window.ModalManager = ModalManager;
}
