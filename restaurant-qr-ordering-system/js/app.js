// 餐廳QR點餐系統 - 主要應用程式邏輯

class RestaurantOrderingApp {
    constructor() {
        this.currentTable = null;
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.isOnline = navigator.onLine;
        
        this.init();
    }    init() {
        console.log('🍽️ 餐廳點餐系統初始化中...');
        
        // 監聽網路狀態
        this.setupNetworkListener();
        
        // 設定事件監聽
        this.setupEventListeners();
        
        // 檢查是否已經有座號
        this.checkExistingTable();
        
        console.log('✅ 系統初始化完成 - 歡迎掃碼用戶！');
    }

    setupNetworkListener() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            console.log('📶 網路已連線');
            this.syncOfflineData();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            console.log('📵 網路已中斷');
            this.showMessage('網路連線中斷，部分功能可能受限', 'warning');
        });
    }    setupEventListeners() {
        // 座號表單提交
        const tableForm = document.getElementById('tableForm');
        if (tableForm) {
            tableForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleTableSubmit();
            });
        }

        // 座號輸入框即時驗證
        const tableNumberInput = document.getElementById('tableNumber');
        if (tableNumberInput) {
            tableNumberInput.addEventListener('input', (e) => {
                this.validateTableNumber(e.target);
            });
            
            // 頁面載入時自動聚焦到座號輸入框
            setTimeout(() => {
                tableNumberInput.focus();
            }, 500);
        }

        // 設定模態框關閉事件
        this.setupModalEvents();
    }

    setupModalEvents() {
        // 確定按鈕
        const modalConfirmBtn = document.getElementById('modalConfirmBtn');
        if (modalConfirmBtn) {
            modalConfirmBtn.addEventListener('click', () => {
                this.closeErrorModal();
            });
        }

        // X關閉按鈕
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', () => {
                this.closeErrorModal();
            });
        }

        // 點擊背景關閉
        const errorModal = document.getElementById('errorModal');
        if (errorModal) {
            errorModal.addEventListener('click', (e) => {
                if (e.target === errorModal) {
                    this.closeErrorModal();
                }
            });
        }

        // ESC鍵關閉
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('errorModal');
                if (modal && !modal.classList.contains('d-none')) {
                    this.closeErrorModal();
                }
            }
        });
    }

    checkExistingTable() {
        const savedTable = localStorage.getItem('currentTable');
        if (savedTable) {
            const tableData = JSON.parse(savedTable);
            const now = new Date().getTime();
            const sessionTime = 4 * 60 * 60 * 1000; // 4小時會話
            
            if (now - tableData.timestamp < sessionTime) {
                this.currentTable = tableData.number;
                console.log(`🪑 恢復座號：${this.currentTable}`);
                // 可以選擇直接跳轉到菜單頁面
                // this.redirectToMenu();
            } else {
                localStorage.removeItem('currentTable');
            }
        }
    }

    validateTableNumber(input) {
        const value = input.value.trim().toUpperCase();
        const isValid = this.isValidTableNumber(value);
        
        if (value && !isValid) {
            input.classList.add('error');
            this.showFieldError(input, '請輸入有效的座號格式');
        } else {
            input.classList.remove('error');
            this.clearFieldError(input);
        }
        
        return isValid;
    }

    isValidTableNumber(tableNumber) {
        // 支援格式：1-99, A01-Z99, A1-Z99
        const patterns = [
            /^[1-9][0-9]?$/, // 1-99
            /^[A-Z][0-9]{1,2}$/, // A1-Z99
            /^[A-Z][0-9]{2}$/ // A01-Z99
        ];
        
        return patterns.some(pattern => pattern.test(tableNumber));
    }

    async handleTableSubmit() {
        const tableNumberInput = document.getElementById('tableNumber');
        const tableNumber = tableNumberInput.value.trim().toUpperCase();
        
        if (!tableNumber) {
            this.showError('請輸入座號');
            return;
        }

        if (!this.isValidTableNumber(tableNumber)) {
            this.showError('請輸入有效的座號格式（例：1, 12, A01, B5）');
            return;
        }

        this.showLoading('驗證座號中...');

        try {
            // 檢查座號是否可用
            const isAvailable = await this.checkTableAvailability(tableNumber);
            
            if (!isAvailable) {
                this.hideLoading();
                this.showError('此座號目前不可使用，請確認座號是否正確');
                return;
            }

            // 儲存座號資訊
            this.currentTable = tableNumber;
            const tableData = {
                number: tableNumber,
                timestamp: new Date().getTime()
            };
            localStorage.setItem('currentTable', JSON.stringify(tableData));

            console.log(`✅ 座號設定成功：${tableNumber}`);
            
            // 跳轉到菜單頁面
            this.redirectToMenu();
            
        } catch (error) {
            console.error('座號驗證錯誤:', error);
            this.hideLoading();
            this.showError('系統繁忙，請稍後再試');
        }
    }    async checkTableAvailability(tableNumber) {
        // 模擬API檢查（實際需要連接Firebase）
        if (!this.isOnline) {
            // 離線模式：允許所有座號
            return true;
        }

        // 這裡應該連接Firebase檢查座號狀態
        // 目前先模擬檢查邏輯
        await this.delay(1000); // 模擬網路延遲
        
        // 簡單的座號檢查邏輯
        const tableNum = parseInt(tableNumber.replace(/[A-Z]/g, ''));
        return tableNum >= 1 && tableNum <= 50; // 假設餐廳有50桌
    }

    redirectToMenu() {
        this.showLoading('正在進入菜單...');
        
        // 模擬跳轉延遲
        setTimeout(() => {
            window.location.href = 'menu.html';
        }, 1500);
    }

    // 工具方法
    showLoading(message = '載入中...') {
        const overlay = document.getElementById('loadingOverlay');
        const messageEl = overlay.querySelector('p');
        
        if (messageEl) {
            messageEl.textContent = message;
        }
        
        overlay.classList.remove('d-none');
    }

    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('d-none');
    }

    showError(message) {
        this.showMessage(message, 'error');
    }    showMessage(message, type = 'info') {
        const modal = document.getElementById('errorModal');
        const messageEl = document.getElementById('errorMessage');
        
        if (messageEl) {
            messageEl.textContent = message;
        }
        
        if (modal) {
            modal.classList.remove('d-none');
            
            // 根據類型設定樣式
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.className = `modal-content ${type}`;
            }
        }
    }

    closeErrorModal() {
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.classList.add('d-none');
            console.log('✅ 提示框已關閉');
        }
    }

    showFieldError(input, message) {
        // 移除現有錯誤訊息
        this.clearFieldError(input);
        
        // 建立錯誤元素
        const errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        errorEl.textContent = message;
        
        // 插入錯誤訊息
        input.parentNode.appendChild(errorEl);
    }

    clearFieldError(input) {
        const errorEl = input.parentNode.querySelector('.form-error');
        if (errorEl) {
            errorEl.remove();
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async syncOfflineData() {
        // 同步離線期間的資料
        console.log('🔄 同步離線資料...');
        // TODO: 實作離線資料同步
    }
}

// 全域函數 - 向後相容
function closeErrorModal() {
    if (window.app && typeof window.app.closeErrorModal === 'function') {
        window.app.closeErrorModal();
    } else {
        // 備用方案
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.classList.add('d-none');
        }
    }
}

// 當頁面載入完成時初始化應用程式
document.addEventListener('DOMContentLoaded', () => {
    window.app = new RestaurantOrderingApp();
    console.log('🍽️ 應用程式已初始化，提示框功能已就緒');
});

// 錯誤處理
window.addEventListener('error', (event) => {
    console.error('全域錯誤:', event.error);
    // 可以在這裡實作錯誤回報功能
});

// 防止表單重複提交
window.addEventListener('beforeunload', (event) => {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    });
});
