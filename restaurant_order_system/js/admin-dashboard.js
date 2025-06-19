// 管理主控台
class AdminDashboard {
    constructor() {
        this.menuItems = [];
        this.orders = [];
        this.initializeData();
        this.updateStats();
        this.initializeEventListeners();
        this.startAutoRefresh();
    }

    // 初始化資料
    initializeData() {
        // 載入菜單資料
        const menuData = localStorage.getItem('restaurantMenuItems');
        if (menuData) {
            this.menuItems = JSON.parse(menuData);
        }

        // 載入訂單資料（模擬）
        const orderData = localStorage.getItem('restaurantOrders');
        if (orderData) {
            this.orders = JSON.parse(orderData);
        } else {
            // 創建一些示範訂單資料
            this.orders = this.generateSampleOrders();
            localStorage.setItem('restaurantOrders', JSON.stringify(this.orders));
        }
    }

    // 生成示範訂單資料
    generateSampleOrders() {
        const today = new Date().toISOString().split('T')[0];
        const sampleOrders = [
            {
                id: 1,
                tableNumber: 3,
                items: [
                    { id: 1, name: '牛肉麵', price: 180, quantity: 1 },
                    { id: 9, name: '珍珠奶茶', price: 55, quantity: 2 }
                ],
                totalAmount: 290,
                status: 'completed',
                createdAt: `${today}T10:30:00`,
                completedAt: `${today}T10:45:00`
            },
            {
                id: 2,
                tableNumber: 7,
                items: [
                    { id: 2, name: '炸雞排', price: 120, quantity: 2 },
                    { id: 5, name: '玉米濃湯', price: 60, quantity: 1 }
                ],
                totalAmount: 300,
                status: 'preparing',
                createdAt: `${today}T11:15:00`
            },
            {
                id: 3,
                tableNumber: 12,
                items: [
                    { id: 4, name: '小籠包', price: 80, quantity: 1 },
                    { id: 10, name: '新鮮果汁', price: 65, quantity: 1 }
                ],
                totalAmount: 145,
                status: 'pending',
                createdAt: `${today}T11:45:00`
            }
        ];
        return sampleOrders;
    }

    // 更新統計資料
    updateStats() {
        // 菜品統計
        const totalMenuItems = this.menuItems.length;
        const availableItems = this.menuItems.filter(item => item.status === 'available').length;
        
        // 訂單統計
        const today = new Date().toISOString().split('T')[0];
        const todayOrders = this.orders.filter(order => 
            order.createdAt.startsWith(today)
        );
        
        const todayRevenue = todayOrders
            .filter(order => order.status === 'completed')
            .reduce((sum, order) => sum + order.totalAmount, 0);

        // 更新顯示
        document.getElementById('totalMenuItems').textContent = totalMenuItems;
        document.getElementById('availableItems').textContent = availableItems;
        document.getElementById('todayOrders').textContent = todayOrders.length;
        document.getElementById('todayRevenue').textContent = `$${todayRevenue}`;

        // 更新系統狀態
        this.updateSystemStatus();
    }

    // 更新系統狀態
    updateSystemStatus() {
        const now = new Date();
        document.getElementById('lastUpdate').textContent = now.toLocaleTimeString();
        
        // 檢查儲存空間使用量
        const storageUsed = this.getStorageUsage();
        const storageElement = document.getElementById('storageStatus');
        
        if (storageUsed < 50) {
            storageElement.textContent = '🟢 充足';
            storageElement.className = 'status-value status-good';
        } else if (storageUsed < 80) {
            storageElement.textContent = '🟡 普通';
            storageElement.className = 'status-value status-warning';
        } else {
            storageElement.textContent = '🔴 不足';
            storageElement.className = 'status-value status-danger';
        }
    }

    // 計算儲存空間使用量
    getStorageUsage() {
        let totalSize = 0;
        for (let key in localStorage) {
            if (localStorage.hasOwnProperty(key)) {
                totalSize += localStorage[key].length;
            }
        }
        // 返回使用百分比（假設 localStorage 限制為 5MB）
        return (totalSize / (5 * 1024 * 1024)) * 100;
    }

    // 初始化事件監聽器
    initializeEventListeners() {
        // 監聽 localStorage 變化
        window.addEventListener('storage', (e) => {
            if (e.key === 'restaurantMenuItems' || e.key === 'restaurantOrders') {
                this.initializeData();
                this.updateStats();
                this.addActivity('資料已更新');
            }
        });

        // 監聽頁面可見性變化
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.initializeData();
                this.updateStats();
            }
        });
    }

    // 添加活動記錄
    addActivity(message) {
        const activityList = document.getElementById('activityList');
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-time">${timeString}</div>
            <div class="activity-content">${message}</div>
        `;
        
        activityList.insertBefore(activityItem, activityList.firstChild);
        
        // 限制活動記錄數量
        const items = activityList.querySelectorAll('.activity-item');
        if (items.length > 10) {
            activityList.removeChild(items[items.length - 1]);
        }
    }

    // 開始自動刷新
    startAutoRefresh() {
        setInterval(() => {
            this.updateStats();
        }, 30000); // 每30秒更新一次
    }

    // 清除所有資料
    clearAllData() {
        if (confirm('確定要清除所有資料嗎？這個操作無法復原！')) {
            localStorage.removeItem('restaurantMenuItems');
            localStorage.removeItem('customerMenuItems');
            localStorage.removeItem('restaurantOrders');
            
            this.menuItems = [];
            this.orders = [];
            this.updateStats();
            this.addActivity('所有資料已清除');
            
            alert('資料已清除');
        }
    }

    // 重置示範資料
    resetDemoData() {
        if (confirm('確定要重置為示範資料嗎？')) {
            // 重置菜單資料
            const defaultMenu = [
                { id: 1, name: '牛肉麵', price: 180, category: 'main', image: '', status: 'available' },
                { id: 2, name: '炸雞排', price: 120, category: 'main', image: '', status: 'available' },
                { id: 3, name: '蔥抓餅', price: 40, category: 'appetizer', image: '', status: 'available' },
                { id: 4, name: '小籠包', price: 80, category: 'appetizer', image: '', status: 'available' },
                { id: 5, name: '玉米濃湯', price: 60, category: 'soup', image: '', status: 'available' },
                { id: 6, name: '紫菜蛋花湯', price: 50, category: 'soup', image: '', status: 'available' },
                { id: 7, name: '紅豆湯', price: 45, category: 'dessert', image: '', status: 'available' },
                { id: 8, name: '芒果冰', price: 90, category: 'dessert', image: '', status: 'available' },
                { id: 9, name: '珍珠奶茶', price: 55, category: 'drink', image: '', status: 'available' },
                { id: 10, name: '新鮮果汁', price: 65, category: 'drink', image: '', status: 'available' }
            ];
            
            localStorage.setItem('restaurantMenuItems', JSON.stringify(defaultMenu));
            
            // 同步到顧客端
            const customerData = defaultMenu.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                category: item.category,
                image: item.image
            }));
            localStorage.setItem('customerMenuItems', JSON.stringify(customerData));
            
            // 重置訂單資料
            const sampleOrders = this.generateSampleOrders();
            localStorage.setItem('restaurantOrders', JSON.stringify(sampleOrders));
            
            this.initializeData();
            this.updateStats();
            this.addActivity('示範資料已重置');
            
            alert('示範資料已重置');
        }
    }

    // 匯出資料
    exportData() {
        const data = {
            menuItems: this.menuItems,
            orders: this.orders,
            exportDate: new Date().toISOString()
        };
        
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `restaurant-data-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.addActivity('資料已匯出');
    }
}

// 全域函數
function navigateToPage(page) {
    window.location.href = page;
}

// 初始化主控台
const dashboard = new AdminDashboard();

// 全域工具函數
window.adminDashboard = {
    clearAllData: () => dashboard.clearAllData(),
    resetDemoData: () => dashboard.resetDemoData(),
    exportData: () => dashboard.exportData()
};
