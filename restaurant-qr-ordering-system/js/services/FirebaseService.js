/**
 * 🔥 Firebase 服務類別
 * 提供 Firestore 資料庫操作的封裝
 */

// Firebase 模組將通過 HTML script 標籤動態載入
// 此檔案依賴於 window.firebaseApp 和 window.firebaseDb

class FirebaseService {
    constructor() {
        this.db = null;
        this.initialized = false;
    }

    /**
     * 初始化 Firebase 服務
     */
    async init() {
        if (this.initialized) return;
        
        try {
            // 等待 Firebase 載入完成
            await this.waitForFirebase();
            this.db = window.firebaseDb;
            this.initialized = true;
            console.log('🔥 Firebase 服務初始化成功');
        } catch (error) {
            console.error('❌ Firebase 服務初始化失敗:', error);
            throw error;
        }
    }

    /**
     * 等待 Firebase 載入完成
     */
    waitForFirebase() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50; // 5秒超時
            
            const checkFirebase = () => {
                if (window.firebaseDb) {
                    resolve();
                } else if (attempts >= maxAttempts) {
                    reject(new Error('Firebase 載入超時'));
                } else {
                    attempts++;
                    setTimeout(checkFirebase, 100);
                }
            };
            
            checkFirebase();
        });
    }

    /**
     * 檢查是否已初始化
     */
    ensureInitialized() {
        if (!this.initialized) {
            throw new Error('FirebaseService 尚未初始化，請先呼叫 init()');
        }
    }

    /**
     * 儲存訂單到 Firestore
     */
    async saveOrder(orderData) {
        this.ensureInitialized();
        
        try {
            // 準備訂單資料
            const orderToSave = {
                ...orderData,
                createdAt: window.firebase.firestore.FieldValue.serverTimestamp(),
                updatedAt: window.firebase.firestore.FieldValue.serverTimestamp(),
                status: 'pending'
            };

            // 儲存到 Firestore
            const docRef = await window.firebase.firestore()
                .collection('orders')
                .add(orderToSave);

            console.log('✅ 訂單儲存成功，ID:', docRef.id);
            return {
                success: true,
                orderId: docRef.id,
                data: orderToSave
            };
        } catch (error) {
            console.error('❌ 儲存訂單失敗:', error);
            throw new Error(`儲存訂單失敗: ${error.message}`);
        }
    }

    /**
     * 讀取所有訂單
     */
    async getOrders(filters = {}) {
        this.ensureInitialized();
        
        try {
            let query = window.firebase.firestore().collection('orders');
            
            // 套用篩選條件
            if (filters.status) {
                query = query.where('status', '==', filters.status);
            }
            
            if (filters.tableNumber) {
                query = query.where('tableNumber', '==', filters.tableNumber);
            }
            
            // 按時間排序
            query = query.orderBy('createdAt', 'desc');
            
            const querySnapshot = await query.get();
            const orders = [];
            
            querySnapshot.forEach((doc) => {
                orders.push({
                    id: doc.id,
                    ...doc.data()
                });
            });

            console.log(`✅ 成功讀取 ${orders.length} 筆訂單`);
            return orders;
        } catch (error) {
            console.error('❌ 讀取訂單失敗:', error);
            throw new Error(`讀取訂單失敗: ${error.message}`);
        }
    }

    /**
     * 更新訂單狀態
     */
    async updateOrderStatus(orderId, status) {
        this.ensureInitialized();
        
        try {
            await window.firebase.firestore()
                .collection('orders')
                .doc(orderId)
                .update({
                    status: status,
                    updatedAt: window.firebase.firestore.FieldValue.serverTimestamp()
                });

            console.log('✅ 訂單狀態更新成功:', orderId, '→', status);
            return { success: true };
        } catch (error) {
            console.error('❌ 更新訂單狀態失敗:', error);
            throw new Error(`更新訂單狀態失敗: ${error.message}`);
        }
    }

    /**
     * 即時監聽訂單變化
     */
    listenToOrders(callback, filters = {}) {
        this.ensureInitialized();
        
        try {
            let query = window.firebase.firestore().collection('orders');
            
            // 套用篩選條件
            if (filters.status) {
                query = query.where('status', '==', filters.status);
            }
            
            // 按時間排序
            query = query.orderBy('createdAt', 'desc');
            
            // 建立即時監聽器
            const unsubscribe = query.onSnapshot((querySnapshot) => {
                const orders = [];
                querySnapshot.forEach((doc) => {
                    orders.push({
                        id: doc.id,
                        ...doc.data()
                    });
                });
                
                console.log(`🔄 即時更新：收到 ${orders.length} 筆訂單`);
                callback(orders);
            }, (error) => {
                console.error('❌ 訂單監聽失敗:', error);
                callback(null, error);
            });

            return unsubscribe; // 返回取消監聽的函數
        } catch (error) {
            console.error('❌ 建立訂單監聽失敗:', error);
            throw new Error(`建立訂單監聽失敗: ${error.message}`);
        }
    }

    /**
     * 儲存菜單項目
     */
    async saveMenuItem(menuItem) {
        this.ensureInitialized();
        
        try {
            const docRef = await window.firebase.firestore()
                .collection('menu')
                .add({
                    ...menuItem,
                    createdAt: window.firebase.firestore.FieldValue.serverTimestamp(),
                    updatedAt: window.firebase.firestore.FieldValue.serverTimestamp()
                });

            console.log('✅ 菜單項目儲存成功，ID:', docRef.id);
            return docRef.id;
        } catch (error) {
            console.error('❌ 儲存菜單項目失敗:', error);
            throw new Error(`儲存菜單項目失敗: ${error.message}`);
        }
    }

    /**
     * 讀取菜單
     */
    async getMenu() {
        this.ensureInitialized();
        
        try {
            const querySnapshot = await window.firebase.firestore()
                .collection('menu')
                .orderBy('category')
                .orderBy('name')
                .get();
            
            const menu = [];
            querySnapshot.forEach((doc) => {
                menu.push({
                    id: doc.id,
                    ...doc.data()
                });
            });

            console.log(`✅ 成功讀取 ${menu.length} 個菜單項目`);
            return menu;
        } catch (error) {
            console.error('❌ 讀取菜單失敗:', error);
            throw new Error(`讀取菜單失敗: ${error.message}`);
        }
    }
}

// 建立全域實例
window.FirebaseService = FirebaseService;
