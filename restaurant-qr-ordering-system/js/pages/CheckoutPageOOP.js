// 重構後的結帳頁面 - 基於 OOP 架構
// 繼承 BasePage，使用 CartManager 和 OrderManager

class CheckoutPage extends BasePage {
    constructor(dependencies = {}) {
        super('checkout', dependencies);
        
        // 依賴注入 - 提升可測試性
        this.cartManager = dependencies.cartManager || new CartManager(this.storageService);
        this.orderManager = dependencies.orderManager || new OrderManager(this.storageService);
        this.modalManager = dependencies.modalManager || new ModalManager();
        
        // 頁面狀態
        this.currentOrder = null;
        this.paymentMethod = 'cash';
        this.isSubmitting = false;
        this.autoReturnTimer = null;
        this.autoReturnCountdown = 5;
        
        console.log('💳 CheckoutPage 建立完成');
    }
    
    /**
     * 獲取頁面專用 DOM 元素
     */
    getPageSpecificElements() {
        return {
            orderSummary: document.getElementById('orderSummary'),
            orderItems: document.getElementById('orderItems'),
            subtotalAmount: document.getElementById('subtotalAmount'),
            totalAmount: document.getElementById('totalAmount'),
            paymentMethods: document.getElementById('payment-methods'),
            customerNote: document.getElementById('customerNote'),
            noteCounter: document.getElementById('noteCounter'),
            submitButton: document.getElementById('submitOrderBtn'),
            submitButtonText: document.querySelector('#submitOrderBtn .btn-text'),
            submitButtonSpinner: document.querySelector('#submitOrderBtn .spinner-border'),
            successModal: document.getElementById('successModal'),
            successMessage: document.getElementById('successMessage'),
            autoReturnMessage: document.getElementById('autoReturnMessage'),
            continueOrderingBtn: document.getElementById('continueOrderingBtn'),
            returnHomeBtn: document.getElementById('returnHomeBtn')
        };
    }
    
    /**
     * 載入訂單資料
     */
    async loadData() {
        try {
            console.log('📦 載入訂單資料...');
            
            // 檢查購物車
            if (this.cartManager.isEmpty()) {
                throw this.errorHandler.createError(
                    '購物車是空的，請先選擇商品',
                    ERROR_CODES.VALIDATION.EMPTY_CART
                );
            }
            
            // 創建訂單
            const cartItems = this.cartManager.getCart();
            this.currentOrder = this.orderManager.createOrder(
                cartItems,
                this.currentTable
            );
            
            console.log(`✅ 訂單創建完成: ${this.currentOrder.orderNumber}`);
            
        } catch (error) {
            console.error('❌ 載入訂單資料失敗:', error);
            
            if (error.code === ERROR_CODES.VALIDATION.EMPTY_CART) {
                // 購物車為空，導向菜單頁
                window.location.href = 'menu.html';
            }
            
            throw error;
        }
    }
    
    /**
     * 渲染頁面
     */
    async render() {
        if (!this.currentOrder) {
            throw this.errorHandler.createError(
                '沒有訂單資料可以顯示',
                ERROR_CODES.DATA.NOT_FOUND
            );
        }
        
        // 渲染訂單摘要
        this.renderOrderSummary();
        
        // 更新提交按鈕文字
        this.updateSubmitButtonText();
        
        console.log('🎨 結帳頁面渲染完成');
    }
    
    /**
     * 設置頁面專用事件監聽器
     */
    setupPageSpecificEventListeners() {
        // 付款方式選擇
        if (this.elements.paymentMethods) {
            this.addEventListener(
                this.elements.paymentMethods,
                'change',
                this.eventBus.debounce(this.handlePaymentMethodChange.bind(this))
            );
        }
        
        // 顾客備註輸入
        if (this.elements.customerNote) {
            this.addEventListener(
                this.elements.customerNote,
                'input',
                this.eventBus.throttle(this.handleNoteInput.bind(this), 200)
            );
        }
        
        // 提交訂單按鈕
        if (this.elements.submitButton) {
            this.addEventListener(
                this.elements.submitButton,
                'click',
                this.handleOrderSubmit.bind(this)
            );
        }
        
        // 成功模態框按鈕
        if (this.elements.continueOrderingBtn) {
            this.addEventListener(
                this.elements.continueOrderingBtn,
                'click',
                this.handleContinueOrdering.bind(this)
            );
        }
        
        if (this.elements.returnHomeBtn) {
            this.addEventListener(
                this.elements.returnHomeBtn,
                'click',
                this.handleReturnHome.bind(this)
            );
        }
        
        // 監聽購物車變化事件
        this.eventBus.on(EVENTS.CART.ITEM_ADDED, this.handleCartChanged.bind(this));
        this.eventBus.on(EVENTS.CART.ITEM_REMOVED, this.handleCartChanged.bind(this));
        this.eventBus.on(EVENTS.CART.QUANTITY_UPDATED, this.handleCartChanged.bind(this));
    }
    
    /**
     * 處理 ESC 鍵 - 關閉模態框
     */
    handleEscapeKey(event) {
        if (this.modalManager.isModalOpen()) {
            this.closeSuccessModal();
        }
    }
    
    /**
     * 渲染訂單摘要
     */
    renderOrderSummary() {
        if (!this.elements.orderItems || !this.currentOrder) return;
        
        const { items, subtotal, total } = this.currentOrder;
        
        // 渲染訂單項目
        this.elements.orderItems.innerHTML = items.map(item => `
            <div class="order-item">
                <div class="item-info">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='images/default-food.png'">
                    </div>
                    <div class="item-details">
                        <h6 class="item-name">${item.name}</h6>
                        <p class="item-price">$${item.price}</p>
                    </div>
                </div>
                <div class="item-quantity">
                    <span class="quantity">×${item.quantity}</span>
                    <span class="subtotal">$${item.subtotal}</span>
                </div>
            </div>
        `).join('');
        
        // 更新金額
        if (this.elements.subtotalAmount) {
            this.elements.subtotalAmount.textContent = `$${subtotal}`;
        }
        
        if (this.elements.totalAmount) {
            this.elements.totalAmount.textContent = `$${total}`;
        }
    }
    
    /**
     * 處理付款方式變更
     */
    handlePaymentMethodChange(event) {
        this.paymentMethod = event.target.value;
        this.updateSubmitButtonText();
        
        console.log(`💳 付款方式變更: ${this.paymentMethod}`);
    }
    
    /**
     * 處理備註輸入
     */
    handleNoteInput(event) {
        const note = event.target.value;
        const maxLength = 200;
        
        // 更新字數計數器
        if (this.elements.noteCounter) {
            this.elements.noteCounter.textContent = `${note.length}/${maxLength}`;
            
            // 接近限制時變紅
            if (note.length > maxLength * 0.9) {
                this.elements.noteCounter.classList.add('text-warning');
            } else {
                this.elements.noteCounter.classList.remove('text-warning');
            }
        }
        
        // 限制字數
        if (note.length > maxLength) {
            event.target.value = note.substring(0, maxLength);
        }
    }
    
    /**
     * 處理訂單提交
     */
    async handleOrderSubmit() {
        if (this.isSubmitting) return;
        
        try {
            this.isSubmitting = true;
            
            // 顯示載入狀態
            this.showSubmitLoading();
            
            // 獲取顧客備註
            const customerNote = this.elements.customerNote?.value?.trim() || '';
            
            // 提交訂單
            const submittedOrder = await this.errorHandler.handleAsyncError(
                this.orderManager.submitOrder(this.currentOrder, this.paymentMethod, customerNote),
                { 
                    action: 'submit_order', 
                    orderId: this.currentOrder.orderNumber,
                    paymentMethod: this.paymentMethod
                }
            );
            
            // 清空購物車
            this.cartManager.clearCart();
            
            // 顯示成功提示
            this.showSuccessModal(submittedOrder);
            
            console.log(`🎉 訂單提交成功: ${submittedOrder.orderNumber}`);
            
        } catch (error) {
            console.error('❌ 訂單提交失敗:', error);
            this.showErrorModal(error.message);
        } finally {
            this.isSubmitting = false;
            this.hideSubmitLoading();
        }
    }
    
    /**
     * 處理購物車變化
     */
    handleCartChanged(data) {
        console.log('🛒 購物車發生變化，重新載入訂單');
        
        // 如果購物車為空，導向菜單頁
        if (this.cartManager.isEmpty()) {
            window.location.href = 'menu.html';
            return;
        }
        
        // 重新創建訂單
        try {
            const cartItems = this.cartManager.getCart();
            this.currentOrder = this.orderManager.createOrder(
                cartItems,
                this.currentTable
            );
            
            // 重新渲染
            this.renderOrderSummary();
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'handle_cart_changed'
            });
        }
    }
    
    /**
     * 更新提交按鈕文字
     */
    updateSubmitButtonText() {
        if (!this.elements.submitButtonText) return;
        
        const buttonTexts = {
            'cash': '確認訂單 (現金付款)',
            'credit_card': '確認訂單 (信用卡付款)',
            'mobile_payment': '確認訂單 (行動支付)'
        };
        
        this.elements.submitButtonText.textContent = buttonTexts[this.paymentMethod] || '確認訂單';
    }
    
    /**
     * 顯示提交載入狀態
     */
    showSubmitLoading() {
        if (this.elements.submitButton) {
            this.elements.submitButton.disabled = true;
        }
        
        if (this.elements.submitButtonText) {
            this.elements.submitButtonText.textContent = '處理中...';
        }
        
        if (this.elements.submitButtonSpinner) {
            this.elements.submitButtonSpinner.classList.remove('d-none');
        }
        
        this.eventBus.emit(EVENTS.UI.LOADING_START, {
            page: this.pageName,
            action: 'submit_order'
        });
    }
    
    /**
     * 隱藏提交載入狀態
     */
    hideSubmitLoading() {
        if (this.elements.submitButton) {
            this.elements.submitButton.disabled = false;
        }
        
        this.updateSubmitButtonText();
        
        if (this.elements.submitButtonSpinner) {
            this.elements.submitButtonSpinner.classList.add('d-none');
        }
        
        this.eventBus.emit(EVENTS.UI.LOADING_END, {
            page: this.pageName,
            action: 'submit_order'
        });
    }
    
    /**
     * 顯示成功模態框
     */
    showSuccessModal(order) {
        if (!this.elements.successModal) return;
        
        // 設置成功訊息
        this.setSuccessMessage(order);
        
        // 顯示模態框
        this.modalManager.openModal(this.elements.successModal);
        
        // 開始自動返回倒數
        this.startAutoReturn();
        
        this.eventBus.emit(EVENTS.UI.MODAL_OPENED, {
            modal: 'success',
            order: order.orderNumber
        });
    }
    
    /**
     * 設定成功訊息
     */
    setSuccessMessage(order) {
        if (!this.elements.successMessage) return;
        
        const paymentMessages = {
            'cash': '請至櫃檯完成現金付款',
            'credit_card': '請至櫃檯完成信用卡付款',
            'mobile_payment': '請使用行動支付完成付款'
        };
        
        const paymentMessage = paymentMessages[order.paymentMethod] || '請完成付款';
        
        this.elements.successMessage.innerHTML = `
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4>訂單提交成功！</h4>
                <div class="order-info">
                    <p><strong>訂單編號：</strong>${order.orderNumber}</p>
                    <p><strong>座號：</strong>${order.tableNumber}</p>
                    <p><strong>預估製作時間：</strong>${order.estimatedTime}</p>
                </div>
                <div class="payment-instruction">
                    <p class="payment-message">${paymentMessage}</p>
                </div>
            </div>
        `;
    }
    
    /**
     * 開始自動返回倒數
     */
    startAutoReturn() {
        this.autoReturnCountdown = 5;
        this.updateAutoReturnMessage();
        
        this.autoReturnTimer = setInterval(() => {
            this.autoReturnCountdown--;
            
            if (this.autoReturnCountdown <= 0) {
                this.handleReturnHome();
            } else {
                this.updateAutoReturnMessage();
            }
        }, 1000);
    }
    
    /**
     * 更新自動返回訊息
     */
    updateAutoReturnMessage() {
        if (this.elements.autoReturnMessage) {
            this.elements.autoReturnMessage.innerHTML = `
                <small class="text-muted">
                    <i class="fas fa-clock"></i>
                    ${this.autoReturnCountdown} 秒後自動返回首頁
                </small>
            `;
        }
        
        // 更新返回首頁按鈕文字
        if (this.elements.returnHomeBtn) {
            this.elements.returnHomeBtn.innerHTML = `
                返回首頁 (${this.autoReturnCountdown})
            `;
        }
    }
    
    /**
     * 停止自動返回
     */
    stopAutoReturn() {
        if (this.autoReturnTimer) {
            clearInterval(this.autoReturnTimer);
            this.autoReturnTimer = null;
        }
        
        // 重置按鈕文字
        if (this.elements.returnHomeBtn) {
            this.elements.returnHomeBtn.textContent = '返回首頁';
        }
        
        // 清空倒數訊息
        if (this.elements.autoReturnMessage) {
            this.elements.autoReturnMessage.innerHTML = '';
        }
    }
    
    /**
     * 處理繼續點餐
     */
    handleContinueOrdering() {
        this.stopAutoReturn();
        this.closeSuccessModal();
        
        this.eventBus.emit(EVENTS.NAVIGATION.ROUTE_CHANGED, {
            from: 'checkout',
            to: 'menu',
            action: 'continue_ordering'
        });
        
        window.location.href = 'menu.html';
    }
    
    /**
     * 處理返回首頁
     */
    handleReturnHome() {
        this.stopAutoReturn();
        this.closeSuccessModal();
        
        this.eventBus.emit(EVENTS.NAVIGATION.ROUTE_CHANGED, {
            from: 'checkout',
            to: 'home',
            action: 'return_home'
        });
        
        window.location.href = 'index.html';
    }
    
    /**
     * 關閉成功模態框
     */
    closeSuccessModal() {
        if (this.elements.successModal) {
            this.modalManager.closeModal(this.elements.successModal);
        }
        
        this.stopAutoReturn();
        
        this.eventBus.emit(EVENTS.UI.MODAL_CLOSED, {
            modal: 'success'
        });
    }
    
    /**
     * 顯示錯誤模態框
     */
    showErrorModal(message) {
        // 簡單的錯誤提示
        alert(`訂單提交失敗：${message}`);
    }
    
    /**
     * 頁面隱藏時停止自動返回
     */
    onPageHidden() {
        this.stopAutoReturn();
    }
    
    /**
     * 頁面顯示時恢復自動返回 (如果模態框開啟)
     */
    onPageVisible() {
        if (this.modalManager.isModalOpen() && !this.autoReturnTimer) {
            this.startAutoReturn();
        }
    }
    
    /**
     * 銷毀頁面
     */
    destroy() {
        // 停止自動返回計時器
        this.stopAutoReturn();
        
        // 關閉模態框
        this.closeSuccessModal();
        
        // 移除事件監聽器
        this.eventBus.off(EVENTS.CART.ITEM_ADDED, this.handleCartChanged);
        this.eventBus.off(EVENTS.CART.ITEM_REMOVED, this.handleCartChanged);
        this.eventBus.off(EVENTS.CART.QUANTITY_UPDATED, this.handleCartChanged);
        
        // 調用父類銷毀
        super.destroy();
    }
}

// 頁面載入完成後初始化
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const checkoutPage = new CheckoutPage();
        await checkoutPage.init();
        
        // 將實例掛載到全域供除錯使用
        window.checkoutPage = checkoutPage;
        
    } catch (error) {
        console.error('❌ 結帳頁面初始化失敗:', error);
        
        // 顯示錯誤訊息
        const errorContainer = document.getElementById('errorMessage');
        if (errorContainer) {
            errorContainer.textContent = error.message;
            errorContainer.classList.remove('d-none');
        }
        
        // 如果是關鍵錯誤，導向相應頁面
        if (error.code === ERROR_CODES.VALIDATION.EMPTY_CART) {
            setTimeout(() => {
                window.location.href = 'menu.html';
            }, 2000);
        } else if (error.code === ERROR_CODES.VALIDATION.INVALID_TABLE) {
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        }
    }
});

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CheckoutPage };
} else {
    window.CheckoutPage = CheckoutPage;
}
