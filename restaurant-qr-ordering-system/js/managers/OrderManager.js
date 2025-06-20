// 訂單管理器 - 組合模式
// 專門處理訂單相關的所有業務邏輯

class OrderManager {
    constructor(storageService = new LocalStorageService()) {
        this.storageService = storageService;
        this.eventBus = EventBus.getInstance();
        this.errorHandler = ErrorHandler.getInstance();
        
        this.currentOrderKey = 'currentOrder';
        this.submittedOrdersKey = 'submittedOrders';
        this.maxOrderHistory = 50;
        
        // 預估製作時間 (分鐘)
        this.estimatedTimes = {
            'appetizer': 5,
            'main': 15,
            'drink': 3,
            'dessert': 8,
            'default': 12
        };
        
        console.log('📋 OrderManager 初始化完成');
    }
    
    /**
     * 從購物車創建訂單
     * @param {Array} cartItems - 購物車項目
     * @param {string} tableNumber - 座號
     * @param {Object} options - 其他選項
     */
    createOrder(cartItems, tableNumber, options = {}) {
        try {
            // 驗證輸入
            this.validateCartItems(cartItems);
            this.validateTableNumber(tableNumber);
            
            const orderNumber = this.generateOrderNumber();
            const estimatedTime = this.calculateEstimatedTime(cartItems);
            
            const order = {
                orderNumber,
                tableNumber,
                items: cartItems.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity,
                    image: item.image,
                    category: item.category,
                    subtotal: item.price * item.quantity
                })),
                subtotal: this.calculateSubtotal(cartItems),
                total: this.calculateTotal(cartItems),
                estimatedTime,
                status: 'pending',
                paymentStatus: 'pending',
                paymentMethod: null,
                customerNote: options.customerNote || '',
                createdAt: new Date().toISOString(),
                updatedAt: new Date().toISOString()
            };
            
            // 保存當前訂單
            this.saveCurrentOrder(order);
            
            // 觸發事件
            this.eventBus.emit(EVENTS.ORDER.CREATED, {
                order,
                itemCount: cartItems.length,
                totalAmount: order.total
            });
            
            console.log(`✅ 創建訂單: ${orderNumber}`);
            
            return order;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'createOrder',
                tableNumber,
                itemCount: cartItems?.length
            });
            throw error;
        }
    }
    
    /**
     * 提交訂單
     * @param {Object} order - 訂單物件
     * @param {string} paymentMethod - 付款方式
     * @param {string} customerNote - 顧客備註
     */
    async submitOrder(order, paymentMethod, customerNote = '') {
        try {
            // 驗證訂單
            this.validateOrder(order);
            this.validatePaymentMethod(paymentMethod);
            
            // 更新訂單資訊
            const submittedOrder = {
                ...order,
                paymentMethod,
                customerNote: customerNote.trim(),
                status: 'submitted',
                paymentStatus: this.getPaymentStatus(paymentMethod),
                submittedAt: new Date().toISOString(),
                updatedAt: new Date().toISOString()
            };
            
            // 保存到已提交訂單
            this.addToOrderHistory(submittedOrder);
            
            // 清除當前訂單
            this.clearCurrentOrder();
            
            // 模擬提交延遲
            await this.simulateSubmissionDelay();
            
            // 觸發事件
            this.eventBus.emit(EVENTS.ORDER.SUBMITTED, {
                order: submittedOrder,
                paymentMethod,
                estimatedTime: submittedOrder.estimatedTime
            });
            
            console.log(`🚀 提交訂單: ${submittedOrder.orderNumber}`);
            
            return submittedOrder;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'submitOrder',
                orderNumber: order?.orderNumber,
                paymentMethod
            });
            throw error;
        }
    }
    
    /**
     * 獲取當前訂單
     */
    getCurrentOrder() {
        try {
            return this.storageService.getItem(this.currentOrderKey);
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'getCurrentOrder'
            });
            return null;
        }
    }
    
    /**
     * 獲取訂單歷史
     */
    getOrderHistory() {
        try {
            const orders = this.storageService.getItem(this.submittedOrdersKey) || [];
            return Array.isArray(orders) ? orders : [];
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'getOrderHistory'
            });
            return [];
        }
    }
    
    /**
     * 更新訂單狀態
     * @param {string} orderNumber - 訂單編號
     * @param {string} status - 新狀態
     */
    updateOrderStatus(orderNumber, status) {
        try {
            const orders = this.getOrderHistory();
            const orderIndex = orders.findIndex(order => order.orderNumber === orderNumber);
            
            if (orderIndex === -1) {
                throw this.errorHandler.createError(
                    '找不到指定的訂單',
                    ERROR_CODES.DATA.NOT_FOUND,
                    { orderNumber }
                );
            }
            
            const oldStatus = orders[orderIndex].status;
            orders[orderIndex].status = status;
            orders[orderIndex].updatedAt = new Date().toISOString();
            
            // 保存更新
            this.storageService.setItem(this.submittedOrdersKey, orders);
            
            // 觸發事件
            this.eventBus.emit(EVENTS.ORDER.STATUS_UPDATED, {
                orderNumber,
                oldStatus,
                newStatus: status,
                order: orders[orderIndex]
            });
            
            console.log(`📊 更新訂單狀態: ${orderNumber} ${oldStatus} → ${status}`);
            
            return orders[orderIndex];
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'updateOrderStatus',
                orderNumber,
                status
            });
            throw error;
        }
    }
    
    /**
     * 生成訂單編號
     */
    generateOrderNumber() {
        const now = new Date();
        const datePart = now.toISOString().replace(/[-:T]/g, '').slice(0, 14);
        const randomPart = Math.random().toString(36).substr(2, 4).toUpperCase();
        
        return `ORD${datePart}${randomPart}`;
    }
    
    /**
     * 計算預估製作時間
     * @param {Array} items - 訂單項目
     */
    calculateEstimatedTime(items) {
        let totalTime = 0;
        let maxCategoryTime = 0;
        
        items.forEach(item => {
            const categoryTime = this.estimatedTimes[item.category] || this.estimatedTimes.default;
            const itemTime = categoryTime * Math.min(item.quantity, 3); // 同商品最多計算3份時間
            
            totalTime += itemTime;
            maxCategoryTime = Math.max(maxCategoryTime, categoryTime);
        });
        
        // 考慮並行處理，時間不會簡單相加
        const estimatedMinutes = Math.max(
            Math.ceil(totalTime * 0.6), // 並行處理係數
            maxCategoryTime,
            5 // 最少5分鐘
        );
        
        const minTime = Math.max(estimatedMinutes - 3, 5);
        const maxTime = estimatedMinutes + 5;
        
        return `${minTime}-${maxTime}分鐘`;
    }
    
    /**
     * 計算小計
     * @param {Array} items - 訂單項目
     */
    calculateSubtotal(items) {
        return items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    /**
     * 計算總計
     * @param {Array} items - 訂單項目
     */
    calculateTotal(items) {
        const subtotal = this.calculateSubtotal(items);
        // 目前沒有額外費用，總計等於小計
        return subtotal;
    }
    
    /**
     * 根據付款方式獲取付款狀態
     * @param {string} paymentMethod - 付款方式
     */
    getPaymentStatus(paymentMethod) {
        switch (paymentMethod) {
            case 'cash':
            case 'credit_card':
                return 'awaiting_payment'; // 等待櫃檯付款
            case 'mobile_payment':
                return 'processing'; // 線上付款處理中
            default:
                return 'pending';
        }
    }
    
    /**
     * 保存當前訂單
     * @param {Object} order - 訂單物件
     */
    saveCurrentOrder(order) {
        const success = this.storageService.setItem(this.currentOrderKey, order);
        
        if (!success) {
            throw this.errorHandler.createError(
                '訂單資料保存失敗',
                ERROR_CODES.DATA.STORAGE_ERROR
            );
        }
    }
    
    /**
     * 清除當前訂單
     */
    clearCurrentOrder() {
        this.storageService.removeItem(this.currentOrderKey);
    }
    
    /**
     * 添加到訂單歷史
     * @param {Object} order - 訂單物件
     */
    addToOrderHistory(order) {
        const orders = this.getOrderHistory();
        
        // 添加到陣列開頭 (最新的在前面)
        orders.unshift(order);
        
        // 限制歷史數量
        if (orders.length > this.maxOrderHistory) {
            orders.splice(this.maxOrderHistory);
        }
        
        const success = this.storageService.setItem(this.submittedOrdersKey, orders);
        
        if (!success) {
            throw this.errorHandler.createError(
                '訂單歷史保存失敗',
                ERROR_CODES.DATA.STORAGE_ERROR
            );
        }
    }
    
    /**
     * 模擬提交延遲
     */
    async simulateSubmissionDelay() {
        const delay = Math.random() * 1000 + 500; // 0.5-1.5秒
        return new Promise(resolve => setTimeout(resolve, delay));
    }
    
    /**
     * 驗證購物車項目
     * @param {Array} items - 購物車項目
     */
    validateCartItems(items) {
        if (!Array.isArray(items) || items.length === 0) {
            throw this.errorHandler.createError(
                '購物車是空的',
                ERROR_CODES.VALIDATION.EMPTY_CART
            );
        }
        
        items.forEach((item, index) => {
            if (!item || typeof item !== 'object') {
                throw this.errorHandler.createError(
                    `商品 ${index + 1} 資訊不正確`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { index, item }
                );
            }
            
            const requiredFields = ['id', 'name', 'price', 'quantity'];
            
            for (const field of requiredFields) {
                if (item[field] === undefined || item[field] === null) {
                    throw this.errorHandler.createError(
                        `商品 ${item.name || index + 1} 缺少必要資訊: ${field}`,
                        ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                        { field, item }
                    );
                }
            }
            
            if (typeof item.price !== 'number' || item.price < 0) {
                throw this.errorHandler.createError(
                    `商品 ${item.name} 價格不正確`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { price: item.price, item }
                );
            }
            
            if (typeof item.quantity !== 'number' || item.quantity <= 0) {
                throw this.errorHandler.createError(
                    `商品 ${item.name} 數量不正確`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { quantity: item.quantity, item }
                );
            }
        });
    }
    
    /**
     * 驗證座號
     * @param {string} tableNumber - 座號
     */
    validateTableNumber(tableNumber) {
        if (!tableNumber || typeof tableNumber !== 'string') {
            throw this.errorHandler.createError(
                '座號不正確',
                ERROR_CODES.VALIDATION.INVALID_TABLE,
                { tableNumber }
            );
        }
    }
    
    /**
     * 驗證訂單
     * @param {Object} order - 訂單物件
     */
    validateOrder(order) {
        if (!order || typeof order !== 'object') {
            throw this.errorHandler.createError(
                '訂單資訊不正確',
                ERROR_CODES.VALIDATION.REQUIRED_FIELD
            );
        }
        
        const requiredFields = ['orderNumber', 'tableNumber', 'items', 'total'];
        
        for (const field of requiredFields) {
            if (!order[field]) {
                throw this.errorHandler.createError(
                    `訂單缺少必要資訊: ${field}`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { field, order }
                );
            }
        }
    }
    
    /**
     * 驗證付款方式
     * @param {string} paymentMethod - 付款方式
     */
    validatePaymentMethod(paymentMethod) {
        const validMethods = ['cash', 'credit_card', 'mobile_payment'];
        
        if (!validMethods.includes(paymentMethod)) {
            throw this.errorHandler.createError(
                '付款方式不正確',
                ERROR_CODES.VALIDATION.INVALID_PAYMENT,
                { paymentMethod, validMethods }
            );
        }
    }
    
    /**
     * 獲取訂單統計資訊
     */
    getOrderStats() {
        const orders = this.getOrderHistory();
        
        const stats = {
            totalOrders: orders.length,
            totalAmount: 0,
            averageAmount: 0,
            statusCounts: {},
            paymentMethodCounts: {},
            recentOrders: orders.slice(0, 5),
            ordersByDate: {}
        };
        
        orders.forEach(order => {
            // 總金額
            stats.totalAmount += order.total;
            
            // 狀態統計
            stats.statusCounts[order.status] = (stats.statusCounts[order.status] || 0) + 1;
            
            // 付款方式統計
            if (order.paymentMethod) {
                stats.paymentMethodCounts[order.paymentMethod] = 
                    (stats.paymentMethodCounts[order.paymentMethod] || 0) + 1;
            }
            
            // 按日期統計
            const orderDate = order.createdAt.split('T')[0];
            if (!stats.ordersByDate[orderDate]) {
                stats.ordersByDate[orderDate] = {
                    count: 0,
                    amount: 0
                };
            }
            stats.ordersByDate[orderDate].count++;
            stats.ordersByDate[orderDate].amount += order.total;
        });
        
        // 平均金額
        if (orders.length > 0) {
            stats.averageAmount = stats.totalAmount / orders.length;
        }
        
        return stats;
    }
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { OrderManager };
} else {
    window.OrderManager = OrderManager;
}
