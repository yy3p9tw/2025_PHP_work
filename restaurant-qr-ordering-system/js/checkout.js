// 結帳頁面 JavaScript - 餐廳點餐系統

class CheckoutPage {
    constructor() {
        this.currentTable = null;
        this.currentOrder = null;
        this.paymentMethod = 'cash';
        
        this.init();
    }

    init() {
        console.log('💳 結帳頁面初始化中...');
        
        // 檢查座號
        this.checkTableNumber();
        
        // 載入訂單資料
        this.loadOrderData();
        
        // 設定事件監聽
        this.setupEventListeners();
        
        // 初始化按鈕文字
        this.updateSubmitButtonText();
        
        console.log('✅ 結帳頁面初始化完成');
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
    }

    async loadOrderData() {
        try {
            console.log('📦 載入訂單資料...');
            
            // 顯示載入狀態
            const loadingState = document.getElementById('checkoutLoading');
            const checkoutContent = document.getElementById('checkoutContent');
            const emptyCart = document.getElementById('emptyCart');
            
            if (loadingState) {
                loadingState.classList.remove('d-none');
            }

            // 從本地儲存載入訂單資料
            const orderDataStr = localStorage.getItem('currentOrder');
            
            // 模擬載入延遲
            await this.delay(1000);
            
            if (!orderDataStr) {
                // 沒有訂單資料，顯示空購物車狀態
                if (loadingState) loadingState.classList.add('d-none');
                if (emptyCart) emptyCart.classList.remove('d-none');
                if (checkoutContent) checkoutContent.classList.add('d-none');
                
                console.warn('⚠️ 沒有找到訂單資料');
                return;
            }

            this.currentOrder = JSON.parse(orderDataStr);
            
            // 驗證訂單資料
            if (!this.currentOrder || !this.currentOrder.items || this.currentOrder.items.length === 0) {
                // 訂單為空，顯示空購物車狀態
                if (loadingState) loadingState.classList.add('d-none');
                if (emptyCart) emptyCart.classList.remove('d-none');
                if (checkoutContent) checkoutContent.classList.add('d-none');
                
                console.warn('⚠️ 訂單資料為空');
                return;
            }

            // 隱藏載入狀態，顯示結帳內容
            if (loadingState) loadingState.classList.add('d-none');
            if (emptyCart) emptyCart.classList.add('d-none');
            if (checkoutContent) checkoutContent.classList.remove('d-none');

            // 渲染訂單摘要
            this.renderOrderSummary();
            
            console.log(`✅ 載入訂單成功: ${this.currentOrder.items.length} 個項目`);

        } catch (error) {
            console.error('❌ 訂單載入失敗:', error);
            this.showToast('訂單載入失敗，請重新整理頁面', 'error');
        }
    }

    renderOrderSummary() {
        if (!this.currentOrder) return;

        const orderItems = document.getElementById('orderItems');
        const orderSubtotal = document.getElementById('orderSubtotal');
        const orderTotal = document.getElementById('orderTotal');

        if (!orderItems) return;

        // 清空容器
        orderItems.innerHTML = '';

        // 渲染每個訂單項目
        this.currentOrder.items.forEach(item => {
            const itemElement = this.createOrderItemElement(item);
            orderItems.appendChild(itemElement);
        });

        // 更新金額顯示
        if (orderSubtotal) {
            orderSubtotal.textContent = `$${this.currentOrder.subtotal}`;
        }
        if (orderTotal) {
            orderTotal.textContent = `$${this.currentOrder.total}`;
        }
    }

    createOrderItemElement(item) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'order-item';

        itemDiv.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="item-image" loading="lazy">
            <div class="item-details">
                <div class="item-name">${item.name}</div>
                <div class="item-price-qty">$${item.price} × ${item.quantity}</div>
            </div>
            <div class="item-total">$${item.price * item.quantity}</div>
        `;

        return itemDiv;
    }    setupEventListeners() {
        // 付款方式選擇
        const paymentMethodInputs = document.querySelectorAll('input[name="paymentMethod"]');
        paymentMethodInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.paymentMethod = e.target.value;
                console.log(`💳 選擇付款方式: ${this.paymentMethod}`);
                
                // 更新付款方式樣式
                this.updatePaymentMethodStyles();
                
                // 更新提交按鈕文字
                this.updateSubmitButtonText();
            });
        });

        // 提交訂單按鈕
        const submitOrderBtn = document.getElementById('submitOrderBtn');
        if (submitOrderBtn) {
            submitOrderBtn.addEventListener('click', () => {
                this.submitOrder();
            });
        }

        // 備註輸入框字數限制提示
        const customerNote = document.getElementById('customerNote');
        if (customerNote) {
            customerNote.addEventListener('input', (e) => {
                const remaining = 200 - e.target.value.length;
                const hint = e.target.parentNode.querySelector('small');
                if (hint) {
                    hint.textContent = `還可輸入 ${remaining} 個字`;
                    if (remaining < 20) {
                        hint.style.color = 'var(--warning)';
                    } else {
                        hint.style.color = 'var(--text-light)';
                    }
                }
            });
        }
    }

    updatePaymentMethodStyles() {
        const paymentOptions = document.querySelectorAll('.payment-option');
        paymentOptions.forEach(option => {
            const input = option.querySelector('input[type="radio"]');
            if (input.checked) {
                option.style.borderColor = 'var(--primary-color)';
                option.style.backgroundColor = 'rgba(255, 107, 53, 0.1)';
            } else {
                option.style.borderColor = '#e0e0e0';
                option.style.backgroundColor = 'transparent';
            }
        });
    }

    updateSubmitButtonText() {
        const submitBtn = document.getElementById('submitOrderBtn');
        if (!submitBtn) return;

        switch (this.paymentMethod) {
            case 'cash':
                submitBtn.textContent = '確認訂單 (現金櫃檯付款)';
                break;
            case 'card':
                submitBtn.textContent = '確認訂單 (信用卡櫃檯付款)';
                break;
            case 'mobile':
                submitBtn.textContent = '確認訂單並線上付款';
                break;
            default:
                submitBtn.textContent = '確認訂單';
        }
    }    async submitOrder() {
        try {
            if (!this.currentOrder) {
                this.showToast('訂單資料錯誤，請重新整理頁面', 'error');
                return;
            }

            // 禁用提交按鈕
            const submitBtn = document.getElementById('submitOrderBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '提交中... <div class="loading-small"></div>';
            }

            console.log('📤 提交訂單中...');

            // 準備最終訂單資料
            const finalOrder = {
                ...this.currentOrder,
                paymentMethod: this.paymentMethod,
                customerNote: document.getElementById('customerNote')?.value || '',
                orderNumber: this.generateOrderNumber(),
                status: this.getOrderStatus(),
                submittedAt: new Date().toISOString(),
                estimatedTime: this.calculateEstimatedTime(),
                paymentStatus: this.getPaymentStatus()
            };

            // 模擬提交延遲
            await this.delay(2000);

            // 這裡將來會接入真實的 API 或 Firebase
            // await this.submitToServer(finalOrder);
            
            // 目前先儲存到本地儲存作為模擬
            const orders = JSON.parse(localStorage.getItem('submittedOrders') || '[]');
            orders.push(finalOrder);
            localStorage.setItem('submittedOrders', JSON.stringify(orders));

            // 清除當前訂單和購物車
            localStorage.removeItem('currentOrder');
            localStorage.removeItem('cart');

            console.log('✅ 訂單提交成功:', finalOrder);

            // 根據付款方式顯示不同的成功訊息
            this.showSuccessModal(finalOrder);

        } catch (error) {
            console.error('❌ 訂單提交失敗:', error);
            this.showToast('訂單提交失敗，請稍後再試', 'error');
            
            // 恢復提交按鈕
            const submitBtn = document.getElementById('submitOrderBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                this.updateSubmitButtonText();
            }
        }
    }

    getOrderStatus() {
        // 根據付款方式決定訂單狀態
        switch (this.paymentMethod) {
            case 'cash':
            case 'card':
                return 'awaiting_payment'; // 等待櫃檯付款
            case 'mobile':
                return 'paid'; // 線上付款完成
            default:
                return 'pending';
        }
    }    getPaymentStatus() {
        // 根據付款方式決定付款狀態
        switch (this.paymentMethod) {
            case 'cash':
            case 'card':
                return 'pending'; // 等待櫃檯付款
            case 'mobile':
                return 'completed'; // 線上付款完成
            default:
                return 'pending';
        }
    }

    generateOrderNumber() {
        const now = new Date();
        const dateStr = now.toISOString().slice(0, 10).replace(/-/g, '');
        const timeStr = now.toISOString().slice(11, 19).replace(/:/g, '');
        const randomStr = Math.random().toString(36).substr(2, 4).toUpperCase();
        
        return `ORD${dateStr}${timeStr}${randomStr}`;
    }

    calculateEstimatedTime() {
        if (!this.currentOrder || !this.currentOrder.items) return '15-20分鐘';
        
        // 根據項目數量計算預估時間
        const itemCount = this.currentOrder.items.reduce((sum, item) => sum + item.quantity, 0);
        
        if (itemCount <= 3) {
            return '10-15分鐘';
        } else if (itemCount <= 6) {
            return '15-20分鐘';
        } else if (itemCount <= 10) {
            return '20-25分鐘';
        } else {
            return '25-30分鐘';
        }
    }    showSuccessModal(order) {
        const modal = document.getElementById('successModal');
        const orderNumber = document.getElementById('orderNumber');
        const estimatedTime = document.getElementById('estimatedTime');

        if (modal) {
            // 更新訂單編號和預估時間
            if (orderNumber) {
                orderNumber.textContent = `#${order.orderNumber}`;
            }
            if (estimatedTime) {
                estimatedTime.textContent = order.estimatedTime;
            }

            // 根據付款方式更新成功訊息
            const successMessage = modal.querySelector('.success-message');
            if (successMessage) {
                switch (order.paymentMethod) {
                    case 'cash':
                        successMessage.innerHTML = `
                            <h3 class="mb-3">訂單提交成功！</h3>
                            <p class="payment-instruction">請攜帶此訂單編號至櫃檯以<strong>現金付款</strong></p>
                        `;
                        break;
                    case 'card':
                        successMessage.innerHTML = `
                            <h3 class="mb-3">訂單提交成功！</h3>
                            <p class="payment-instruction">請攜帶此訂單編號至櫃檯以<strong>信用卡付款</strong></p>
                        `;
                        break;
                    case 'mobile':
                        successMessage.innerHTML = `
                            <h3 class="mb-3">訂單提交成功！付款完成</h3>
                            <p class="payment-instruction">您的<strong>行動支付</strong>已完成，請等待餐點製作</p>
                        `;
                        break;
                    default:
                        successMessage.innerHTML = `
                            <h3 class="mb-3">訂單提交成功！</h3>
                            <p class="payment-instruction">請至櫃檯完成付款</p>
                        `;
                }
            }            // 顯示模態框
            modal.classList.remove('d-none');
            
            // 禁用背景滾動
            document.body.style.overflow = 'hidden';
            
            // 5秒自動返回首頁的倒數計時
            let autoRedirectTimer = null;
            let countdownTimer = null;
            let countdownSeconds = 5;
              const startAutoRedirect = () => {
                // 更新按鈕文字和提示文字顯示倒數
                const homeButton = modal.querySelector('.btn-outline');
                const redirectNotice = modal.querySelector('.auto-redirect-notice small');
                
                const updateCountdown = () => {
                    if (countdownSeconds > 0) {
                        if (homeButton) {
                            homeButton.textContent = `返回首頁 (${countdownSeconds}s)`;
                        }
                        if (redirectNotice) {
                            redirectNotice.textContent = `${countdownSeconds}秒後將自動返回首頁`;
                        }
                        countdownSeconds--;
                        countdownTimer = setTimeout(updateCountdown, 1000);
                    } else {
                        // 時間到，自動返回首頁
                        closeModal();
                        goToHome();
                    }
                };
                updateCountdown();
                
                // 5秒後自動返回首頁
                autoRedirectTimer = setTimeout(() => {
                    closeModal();
                    goToHome();
                }, 5000);
            };
              // 清除自動返回計時器的函數
            const clearAutoRedirect = () => {
                if (autoRedirectTimer) {
                    clearTimeout(autoRedirectTimer);
                    autoRedirectTimer = null;
                }
                if (countdownTimer) {
                    clearTimeout(countdownTimer);
                    countdownTimer = null;
                }
                // 恢復按鈕原始文字和提示文字
                const homeButton = modal.querySelector('.btn-outline');
                const redirectNotice = modal.querySelector('.auto-redirect-notice small');
                if (homeButton) {
                    homeButton.textContent = '返回首頁';
                }
                if (redirectNotice) {
                    redirectNotice.textContent = '已取消自動返回';
                }
            };
            
            // 添加點擊背景關閉的功能
            const closeModal = () => {
                clearAutoRedirect();
                modal.classList.add('d-none');
                document.body.style.overflow = 'auto';
            };            // 點擊背景關閉
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // ESC 鍵關閉
            const handleEscKey = (e) => {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', handleEscKey);
                }
            };
            document.addEventListener('keydown', handleEscKey);

            // 為按鈕添加事件監聽，點擊時取消自動返回
            const menuButton = modal.querySelector('.btn-primary');
            const homeButton = modal.querySelector('.btn-outline');
            
            if (menuButton) {
                menuButton.addEventListener('click', () => {
                    clearAutoRedirect();
                });
            }
            
            if (homeButton) {
                homeButton.addEventListener('click', () => {
                    clearAutoRedirect();
                });
            }

            // 存儲關閉函數供全域使用
            window.closeSuccessModal = closeModal;
            window.clearAutoRedirect = clearAutoRedirect;
            
            // 啟動自動返回倒數計時
            startAutoRedirect();
        }
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

// 全域函數 - 供 HTML 使用
function goToMenu() {
    window.location.href = 'menu.html';
}

function goToCart() {
    window.location.href = 'cart.html';
}

function goToHome() {
    window.location.href = 'index.html';
}

// 當頁面載入完成時初始化
document.addEventListener('DOMContentLoaded', () => {
    window.checkoutPage = new CheckoutPage();
});

// 錯誤處理
window.addEventListener('error', (event) => {
    console.error('結帳頁面錯誤:', event.error);
});

// 在頁面卸載時清理資源
window.addEventListener('beforeunload', () => {
    console.log('📤 結帳頁面卸載');
});
