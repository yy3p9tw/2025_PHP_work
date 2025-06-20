// 購物車頁面 JavaScript - 餐廳點餐系統

class CartPage {    constructor() {
        this.currentTable = null;
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        this.init();
    }

    init() {
        console.log('🛒 購物車頁面初始化中...');
        
        // 檢查座號
        this.checkTableNumber();
        
        // 設定事件監聽
        this.setupEventListeners();
        
        // 載入購物車內容
        this.loadCartItems();
        
        console.log('✅ 購物車頁面初始化完成');
    }

    checkTableNumber() {
        const savedTable = localStorage.getItem('currentTable');
        if (savedTable) {
            const tableData = JSON.parse(savedTable);
            this.currentTable = tableData.number;
            
            // 更新頁面顯示
            const tableDisplay = document.getElementById('currentTableNumber');
            if (tableDisplay) {
                tableDisplay.textContent = `座號：${this.currentTable}`;
            }
        } else {
            // 如果沒有座號，返回首頁
            console.warn('⚠️ 沒有找到座號資訊，返回首頁');
            window.location.href = 'index.html';
        }
    }    setupEventListeners() {
        // 清空購物車按鈕
        const clearCartBtn = document.getElementById('clearCartBtn');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => {
                this.clearCart();
            });
        }

        // 前往結帳按鈕
        const proceedBtn = document.getElementById('proceedBtn');
        if (proceedBtn) {
            proceedBtn.addEventListener('click', () => {
                this.proceedToCheckout();
            });
        }    }

    async loadCartItems() {
        try {
            console.log('📦 載入購物車項目...');
            
            // 顯示載入狀態
            const loadingState = document.getElementById('cartLoading');
            if (loadingState) {
                loadingState.classList.remove('d-none');
            }

            // 模擬載入延遲
            await this.delay(800);

            // 渲染購物車項目
            this.renderCartItems();
            
            // 更新訂單摘要
            this.updateOrderSummary();

            console.log(`✅ 載入了 ${this.cart.length} 個購物車項目`);

        } catch (error) {
            console.error('❌ 購物車載入失敗:', error);
            this.showToast('購物車載入失敗，請重新整理頁面', 'error');
        }
    }

    renderCartItems() {
        const cartItems = document.getElementById('cartItems');
        const loadingState = document.getElementById('cartLoading');
        const emptyCart = document.getElementById('emptyCart');
        const orderSummary = document.getElementById('orderSummary');

        if (!cartItems) return;

        // 隱藏載入狀態
        if (loadingState) {
            loadingState.classList.add('d-none');
        }

        // 清空容器
        cartItems.innerHTML = '';

        if (this.cart.length === 0) {
            // 顯示空購物車狀態
            if (emptyCart) {
                emptyCart.classList.remove('d-none');
            }
            if (orderSummary) {
                orderSummary.classList.add('d-none');
            }
            return;
        }

        // 隱藏空狀態，顯示訂單摘要
        if (emptyCart) {
            emptyCart.classList.add('d-none');
        }
        if (orderSummary) {
            orderSummary.classList.remove('d-none');
        }

        // 渲染購物車項目
        this.cart.forEach((item, index) => {
            const itemElement = this.createCartItemElement(item, index);
            cartItems.appendChild(itemElement);
        });
    }

    createCartItemElement(item, index) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item';
        itemDiv.setAttribute('data-item-index', index);

        itemDiv.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="cart-item-image" loading="lazy">
            <div class="cart-item-details">
                <h3 class="cart-item-name">${item.name}</h3>
                <p class="cart-item-price">$${item.price}</p>
            </div>
            <div class="cart-item-controls">
                <div class="qty-controls">
                    <button class="qty-btn qty-minus" data-index="${index}">-</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button class="qty-btn qty-plus" data-index="${index}">+</button>
                </div>
                <button class="remove-btn" data-index="${index}" title="移除項目">
                    🗑️
                </button>
            </div>
        `;

        // 添加事件監聽
        this.setupItemEvents(itemDiv, index);

        return itemDiv;
    }

    setupItemEvents(itemElement, index) {
        // 數量減少按鈕
        const minusBtn = itemElement.querySelector('.qty-minus');
        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                this.updateQuantity(index, -1);
            });
        }

        // 數量增加按鈕
        const plusBtn = itemElement.querySelector('.qty-plus');
        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                this.updateQuantity(index, 1);
            });
        }

        // 移除按鈕
        const removeBtn = itemElement.querySelector('.remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                this.removeItem(index);
            });
        }
    }

    updateQuantity(index, change) {
        if (index < 0 || index >= this.cart.length) return;

        const item = this.cart[index];
        const newQuantity = Math.max(1, Math.min(99, item.quantity + change));
        
        if (newQuantity !== item.quantity) {
            item.quantity = newQuantity;
            
            // 儲存到本地儲存
            localStorage.setItem('cart', JSON.stringify(this.cart));
            
            // 更新顯示
            const qtyDisplay = document.querySelector(`[data-item-index="${index}"] .qty-display`);
            if (qtyDisplay) {
                qtyDisplay.textContent = newQuantity;
            }
            
            // 更新訂單摘要
            this.updateOrderSummary();
            
            console.log(`📝 更新數量: ${item.name} x ${newQuantity}`);
        }
    }

    removeItem(index) {
        if (index < 0 || index >= this.cart.length) return;

        const item = this.cart[index];
        const itemName = item.name;
        
        // 從購物車移除
        this.cart.splice(index, 1);
        
        // 儲存到本地儲存
        localStorage.setItem('cart', JSON.stringify(this.cart));
        
        // 重新渲染
        this.renderCartItems();
        this.updateOrderSummary();
        
        // 顯示提示
        this.showToast(`已移除 ${itemName}`);
        
        console.log(`🗑️ 移除項目: ${itemName}`);
    }    updateOrderSummary() {
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total');

        // 計算金額（不含服務費）
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const total = subtotal; // 總金額就是小計

        // 更新顯示
        if (subtotalEl) subtotalEl.textContent = `$${subtotal}`;
        if (totalEl) totalEl.textContent = `$${total}`;

        // 更新結帳按鈕狀態
        const proceedBtn = document.getElementById('proceedBtn');
        if (proceedBtn) {
            if (this.cart.length === 0) {
                proceedBtn.disabled = true;
                proceedBtn.textContent = '購物車是空的';
            } else {
                proceedBtn.disabled = false;
                proceedBtn.textContent = `前往結帳 ($${total})`;
            }
        }
    }

    clearCart() {
        if (this.cart.length === 0) {
            this.showToast('購物車已經是空的');
            return;
        }

        this.cart = [];
        localStorage.setItem('cart', JSON.stringify(this.cart));
        
        this.renderCartItems();
        this.updateOrderSummary();
        
        this.showToast('購物車已清空');
        
        console.log('🗑️ 購物車已清空');
    }    proceedToCheckout() {
        if (this.cart.length === 0) {
            this.showToast('購物車是空的，無法結帳', 'error');
            return;
        }

        // 計算訂單總金額（不含服務費）
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const total = subtotal; // 總金額就是小計

        // 準備訂單資料
        const orderData = {
            tableNumber: this.currentTable,
            items: this.cart,
            subtotal: subtotal,
            total: total,
            createdAt: new Date().toISOString()
        };

        // 儲存訂單資料到本地儲存
        localStorage.setItem('currentOrder', JSON.stringify(orderData));

        console.log('💳 前往結帳頁面', orderData);

        // 跳轉到結帳頁面
        window.location.href = 'checkout.html';
    }

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (toast && toastMessage) {
            toastMessage.textContent = message;
            
            // 設定樣式
            toast.className = `toast ${type}`;
            toast.classList.remove('d-none');
            
            // 3秒後自動隱藏
            setTimeout(() => {
                toast.classList.add('d-none');
            }, 3000);
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// 當頁面載入完成時初始化
document.addEventListener('DOMContentLoaded', () => {
    window.cartPage = new CartPage();
});

// 錯誤處理
window.addEventListener('error', (event) => {
    console.error('購物車頁面錯誤:', event.error);
});
