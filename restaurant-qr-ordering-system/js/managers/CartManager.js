// 購物車管理器 - 組合模式
// 專門處理購物車相關的所有業務邏輯

// 購物車事件常數
const CART_EVENTS = {
    CART: {
        ITEM_ADDED: 'cart:item:added',
        ITEM_REMOVED: 'cart:item:removed',
        QUANTITY_UPDATED: 'cart:quantity:updated',
        CLEARED: 'cart:cleared',
        LOADED: 'cart:loaded'
    }
};

class CartManager {
    constructor(storageService = null) {
        // 確保 storageService 有效
        if (!storageService || typeof storageService.setItem !== 'function') {
            if (typeof LocalStorageService !== 'undefined') {
                storageService = new LocalStorageService();
            } else {
                // 作為後備，創建一個簡單的 localStorage 包裝器
                storageService = {
                    getItem: (key) => {
                        try {
                            const item = localStorage.getItem(key);
                            return item ? JSON.parse(item) : null;
                        } catch (e) {
                            return null;
                        }
                    },
                    setItem: (key, value) => {
                        try {
                            localStorage.setItem(key, JSON.stringify(value));
                            return true;
                        } catch (e) {
                            return false;
                        }
                    },
                    removeItem: (key) => {
                        try {
                            localStorage.removeItem(key);
                            return true;
                        } catch (e) {
                            return false;
                        }
                    },
                    clear: () => {
                        try {
                            localStorage.clear();
                            return true;
                        } catch (e) {
                            return false;
                        }
                    }
                };
            }
        }
        
        this.storageService = storageService;
        this.storage = storageService; // 別名，便於測試
        this.eventBus = EventBus.getInstance();
        this.errorHandler = ErrorHandler.getInstance();
        
        this.storageKey = 'cart';
        this.maxQuantity = 99;
        this.maxItems = 50;        // 初始化購物車數據
        this.items = this.getCart();
        
        console.log('🛒 CartManager 初始化完成');
    }
      /**
     * 獲取購物車
     * @returns {Array} 購物車項目陣列
     */    getCart() {
        try {
            const cart = this.storageService.getItem(this.storageKey) || [];
            this.items = Array.isArray(cart) ? cart : [];
            return this.items;
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'getCart'
            });
            this.items = [];
            return [];
        }
    }
    
    /**
     * 同步保存購物車
     * @param {Array} cart - 購物車數據
     */    saveCart(cart) {
        try {
            if (!this.storageService || typeof this.storageService.setItem !== 'function') {
                throw new Error('StorageService 不可用或 setItem 方法未定義');
            }
            
            this.items = cart;
            const success = this.storageService.setItem(this.storageKey, cart);
            if (!success) {
                throw new Error('購物車資料保存失敗');
            }
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'saveCart'
            });
            throw error; // 重新拋出錯誤以便上層處理
        }
    }
    
    /**
     * 添加商品到購物車
     * @param {Object} item - 商品資訊
     * @param {number} quantity - 數量
     */    addItem(item, quantity = 1) {
        try {
            // 驗證輸入
            this.validateItem(item);
            this.validateQuantity(quantity);
            
            // 使用當前的購物車狀態，而不是重新從 storage 讀取
            const cart = [...this.items]; // 創建副本以避免直接修改
            
            // 檢查購物車項目數量限制
            if (cart.length >= this.maxItems) {
                throw this.errorHandler.createError(
                    `購物車最多只能放 ${this.maxItems} 種商品`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { maxItems: this.maxItems }
                );
            }
            
            // 查找是否已存在相同商品
            const existingItemIndex = cart.findIndex(cartItem => cartItem.id === item.id);
            
            if (existingItemIndex > -1) {
                // 更新現有商品數量
                const newQuantity = cart[existingItemIndex].quantity + quantity;
                
                if (newQuantity > this.maxQuantity) {
                    throw this.errorHandler.createError(
                        `商品數量不能超過 ${this.maxQuantity}`,
                        ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                        { maxQuantity: this.maxQuantity }
                    );
                }
                
                cart[existingItemIndex].quantity = newQuantity;
                cart[existingItemIndex].updatedAt = new Date().toISOString();
            } else {
                // 添加新商品
                const cartItem = {
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    image: item.image,
                    category: item.category,
                    quantity: quantity,
                    addedAt: new Date().toISOString(),
                    updatedAt: new Date().toISOString()
                };
                
                cart.push(cartItem);
            }
              // 保存到儲存
            this.saveCart(cart);
            
            // 觸發事件
            this.eventBus.emit(CART_EVENTS.CART.ITEM_ADDED, {
                item,
                quantity,
                cartTotal: this.getCartTotal(cart),
                itemCount: this.getItemCount(cart)
            });
            
            console.log(`✅ 添加商品到購物車: ${item.name} x${quantity}`);
            
            return true;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'addItem',
                itemId: item?.id,
                quantity
            });
            throw error;
        }
    }
    
    /**
     * 移除購物車商品
     * @param {string} itemId - 商品ID
     */    removeItem(itemId) {
        try {
            // 使用當前的購物車狀態
            const cart = [...this.items];
            const itemIndex = cart.findIndex(item => item.id === itemId);
            
            if (itemIndex === -1) {
                throw this.errorHandler.createError(
                    '購物車中沒有找到該商品',
                    ERROR_CODES.DATA.NOT_FOUND,
                    { itemId }
                );
            }
            
            const removedItem = cart.splice(itemIndex, 1)[0];
              // 保存到儲存
            this.saveCart(cart);
            
            // 觸發事件
            this.eventBus.emit(CART_EVENTS.CART.ITEM_REMOVED, {
                item: removedItem,
                cartTotal: this.getCartTotal(cart),
                itemCount: this.getItemCount(cart)
            });
            
            console.log(`🗑️ 從購物車移除商品: ${removedItem.name}`);
            
            return true;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'removeItem',
                itemId
            });
            throw error;
        }
    }
    
    /**
     * 更新商品數量
     * @param {string} itemId - 商品ID
     * @param {number} quantity - 新數量
     */    updateQuantity(itemId, quantity) {
        try {
            this.validateQuantity(quantity);
            
            // 使用當前的購物車狀態
            const cart = [...this.items];
            const itemIndex = cart.findIndex(item => item.id === itemId);
            
            if (itemIndex === -1) {
                throw this.errorHandler.createError(
                    '購物車中沒有找到該商品',
                    ERROR_CODES.DATA.NOT_FOUND,
                    { itemId }
                );
            }
            
            const oldQuantity = cart[itemIndex].quantity;
            
            if (quantity <= 0) {
                // 數量為 0 或負數，移除商品
                return this.removeItem(itemId);
            }
            
            if (quantity > this.maxQuantity) {
                throw this.errorHandler.createError(
                    `商品數量不能超過 ${this.maxQuantity}`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { maxQuantity: this.maxQuantity }
                );
            }
            
            cart[itemIndex].quantity = quantity;
            cart[itemIndex].updatedAt = new Date().toISOString();
              // 保存到儲存
            this.saveCart(cart);
            
            // 觸發事件
            this.eventBus.emit(CART_EVENTS.CART.QUANTITY_UPDATED, {
                itemId,
                oldQuantity,
                newQuantity: quantity,
                item: cart[itemIndex],
                cartTotal: this.getCartTotal(cart),
                itemCount: this.getItemCount(cart)
            });
            
            console.log(`🔄 更新購物車商品數量: ${cart[itemIndex].name} ${oldQuantity} → ${quantity}`);
            
            return true;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'updateQuantity',
                itemId,
                quantity
            });
            throw error;
        }
    }
    
    /**
     * 清空購物車
     */    clearCart() {
        try {
            const cart = [...this.items];
            const clearedCount = cart.length;
            
            // 清空本地狀態
            this.items = [];
            
            // 清空儲存
            this.storageService.removeItem(this.storageKey);
            
            // 觸發事件
            this.eventBus.emit(CART_EVENTS.CART.CLEARED, {
                clearedCount,
                clearedItems: [...cart]
            });
            
            console.log(`🧹 清空購物車，移除了 ${clearedCount} 個商品`);
            
            return true;
            
        } catch (error) {
            this.errorHandler.handleError(error, {
                action: 'clearCart'
            });
            throw error;
        }
    }
    
    /**
     * 獲取購物車商品總數
     * @param {Array} cart - 購物車陣列 (可選)
     */
    getItemCount(cart = null) {
        const cartItems = cart || this.getCart();
        return cartItems.reduce((total, item) => total + item.quantity, 0);
    }
    
    /**
     * 獲取購物車總金額
     * @param {Array} cart - 購物車陣列 (可選)
     */
    getCartTotal(cart = null) {
        const cartItems = cart || this.getCart();
        return cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    /**
     * 獲取購物車摘要
     */
    getCartSummary() {
        const cart = this.getCart();
        const itemCount = this.getItemCount(cart);
        const total = this.getCartTotal(cart);
        
        return {
            items: cart,
            itemCount,
            total,
            isEmpty: cart.length === 0,
            createdAt: new Date().toISOString()
        };
    }
    
    /**
     * 檢查購物車是否為空
     */
    isEmpty() {
        return this.getCart().length === 0;
    }
    
    /**
     * 驗證商品資訊
     * @param {Object} item - 商品資訊  
     */
    validateItem(item) {
        if (!item || typeof item !== 'object') {
            throw this.errorHandler.createError(
                '商品資訊不正確',
                ERROR_CODES.VALIDATION.REQUIRED_FIELD
            );
        }
          const requiredFields = ['id', 'name', 'price'];
        
        for (const field of requiredFields) {
            if (item[field] === undefined || item[field] === null || item[field] === '') {
                throw this.errorHandler.createError(
                    `商品缺少必要資訊: ${field}`,
                    ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                    { field, item }
                );
            }
        }
        
        if (typeof item.price !== 'number' || item.price < 0) {
            throw this.errorHandler.createError(
                '商品價格不正確',
                ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                { price: item.price }
            );
        }
    }
    
    /**
     * 驗證數量
     * @param {number} quantity - 數量
     */
    validateQuantity(quantity) {
        if (typeof quantity !== 'number' || quantity < 0) {
            throw this.errorHandler.createError(
                '商品數量必須是正數',
                ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                { quantity }
            );
        }
        
        if (quantity > this.maxQuantity) {
            throw this.errorHandler.createError(
                `商品數量不能超過 ${this.maxQuantity}`,
                ERROR_CODES.VALIDATION.REQUIRED_FIELD,
                { quantity, maxQuantity: this.maxQuantity }
            );        }
    }
    
    /**
     * 獲取商品在購物車中的數量
     * @param {string} itemId - 商品ID
     */
    getItemQuantity(itemId) {
        const cart = this.getCart();
        const item = cart.find(cartItem => cartItem.id === itemId);
        return item ? item.quantity : 0;
    }
    
    /**
     * 檢查商品是否在購物車中
     * @param {string} itemId - 商品ID
     */
    hasItem(itemId) {
        const cart = this.getCart();
        return cart.some(item => item.id === itemId);
    }
    
    /**
     * 獲取購物車統計資訊
     */
    getStats() {
        const cart = this.getCart();
        
        const stats = {
            totalItems: cart.length,
            totalQuantity: this.getItemCount(cart),
            totalAmount: this.getCartTotal(cart),
            categories: {},
            averagePrice: 0,
            mostExpensiveItem: null,
            cheapestItem: null
        };
        
        if (cart.length > 0) {
            // 按分類統計
            cart.forEach(item => {
                const category = item.category || 'other';
                if (!stats.categories[category]) {
                    stats.categories[category] = {
                        count: 0,
                        quantity: 0,
                        amount: 0
                    };
                }
                
                stats.categories[category].count++;
                stats.categories[category].quantity += item.quantity;
                stats.categories[category].amount += item.price * item.quantity;
            });
            
            // 平均價格
            stats.averagePrice = stats.totalAmount / stats.totalQuantity;
            
            // 最貴和最便宜的商品
            stats.mostExpensiveItem = cart.reduce((max, item) =>
                item.price > max.price ? item : max
            );
            
            stats.cheapestItem = cart.reduce((min, item) =>
                item.price < min.price ? item : min
            );
        }
        
        return stats;
    }
}

// 匯出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CartManager, CART_EVENTS };
} else {
    window.CartManager = CartManager;
    window.CART_EVENTS = CART_EVENTS;
}
