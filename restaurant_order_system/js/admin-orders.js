// 訂單管理系統
class OrderManager {
    constructor() {
        this.orders = [];
        this.filteredOrders = [];
        this.currentOrderId = null;
        this.initializeData();
        this.initializeEventListeners();
        this.loadOrders();
        this.startAutoRefresh();
    }

    // 初始化資料
    initializeData() {
        const orderData = localStorage.getItem('restaurantOrders');
        if (orderData) {
            this.orders = JSON.parse(orderData);
        } else {
            this.orders = this.generateSampleOrders();
            this.saveOrders();
        }
    }

    // 生成示範訂單資料
    generateSampleOrders() {
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        
        const formatDate = (date) => date.toISOString();
        
        return [
            {
                id: Date.now() + 1,
                tableNumber: 3,
                items: [
                    { id: 1, name: '牛肉麵', price: 180, quantity: 1 },
                    { id: 9, name: '珍珠奶茶', price: 55, quantity: 2 }
                ],
                totalAmount: 290,
                status: 'pending',
                createdAt: formatDate(new Date(today.getTime() - 1000 * 60 * 30)), // 30分鐘前
                note: ''
            },
            {
                id: Date.now() + 2,
                tableNumber: 7,
                items: [
                    { id: 2, name: '炸雞排', price: 120, quantity: 2 },
                    { id: 5, name: '玉米濃湯', price: 60, quantity: 1 }
                ],
                totalAmount: 300,
                status: 'preparing',
                createdAt: formatDate(new Date(today.getTime() - 1000 * 60 * 45)), // 45分鐘前
                note: '不要太辣'
            },
            {
                id: Date.now() + 3,
                tableNumber: 12,
                items: [
                    { id: 4, name: '小籠包', price: 80, quantity: 1 },
                    { id: 10, name: '新鮮果汁', price: 65, quantity: 1 }
                ],
                totalAmount: 145,
                status: 'ready',
                createdAt: formatDate(new Date(today.getTime() - 1000 * 60 * 15)), // 15分鐘前
                completedAt: formatDate(new Date(today.getTime() - 1000 * 60 * 5)), // 5分鐘前
                note: ''
            },
            {
                id: Date.now() + 4,
                tableNumber: 5,
                items: [
                    { id: 3, name: '蔥抓餅', price: 40, quantity: 3 },
                    { id: 6, name: '紫菜蛋花湯', price: 50, quantity: 2 }
                ],
                totalAmount: 220,
                status: 'completed',
                createdAt: formatDate(yesterday),
                completedAt: formatDate(new Date(yesterday.getTime() + 1000 * 60 * 25)),
                note: ''
            }
        ];
    }

    // 儲存訂單資料
    saveOrders() {
        localStorage.setItem('restaurantOrders', JSON.stringify(this.orders));
    }

    // 初始化事件監聽器
    initializeEventListeners() {
        // 篩選器
        document.getElementById('statusFilter').addEventListener('change', () => {
            this.filterOrders();
        });

        document.getElementById('dateFilter').addEventListener('change', () => {
            this.filterOrders();
        });

        document.getElementById('tableFilter').addEventListener('input', () => {
            this.filterOrders();
        });

        // 工具列按鈕
        document.getElementById('refreshOrders').addEventListener('click', () => {
            this.refreshOrders();
        });

        document.getElementById('addTestOrder').addEventListener('click', () => {
            this.addTestOrder();
        });

        // 設定今天的日期為預設值
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateFilter').value = today;
    }

    // 載入訂單
    loadOrders() {
        this.filteredOrders = [...this.orders];
        this.filterOrders();
        this.updateStats();
    }

    // 篩選訂單
    filterOrders() {
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        const tableFilter = document.getElementById('tableFilter').value;

        this.filteredOrders = this.orders.filter(order => {
            const statusMatch = !statusFilter || order.status === statusFilter;
            const dateMatch = !dateFilter || order.createdAt.startsWith(dateFilter);
            const tableMatch = !tableFilter || order.tableNumber.toString() === tableFilter;
            
            return statusMatch && dateMatch && tableMatch;
        });

        this.renderOrders();
        this.updateStats();
    }

    // 渲染訂單列表
    renderOrders() {
        const orderCards = document.getElementById('orderCards');
        const emptyState = document.getElementById('emptyState');

        if (this.filteredOrders.length === 0) {
            orderCards.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        
        // 按狀態和時間排序
        const sortedOrders = this.filteredOrders.sort((a, b) => {
            const statusOrder = { 'pending': 1, 'preparing': 2, 'ready': 3, 'completed': 4 };
            if (statusOrder[a.status] !== statusOrder[b.status]) {
                return statusOrder[a.status] - statusOrder[b.status];
            }
            return new Date(b.createdAt) - new Date(a.createdAt);
        });

        orderCards.innerHTML = sortedOrders.map(order => this.createOrderCard(order)).join('');
    }

    // 創建訂單卡片
    createOrderCard(order) {
        const createdTime = new Date(order.createdAt).toLocaleString();
        const statusClass = `status-${order.status}`;
        const statusText = this.getStatusText(order.status);
        
        return `
            <div class="order-card ${statusClass}" onclick="orderManager.showOrderDetail(${order.id})">
                <div class="order-header">
                    <div class="order-id">#${order.id}</div>
                    <div class="order-table">桌號 ${order.tableNumber}</div>
                    <div class="order-status ${statusClass}">${statusText}</div>
                </div>
                
                <div class="order-time">${createdTime}</div>
                
                <div class="order-items-summary">
                    ${order.items.map(item => 
                        `<span class="item-summary">${item.name} x${item.quantity}</span>`
                    ).join(', ')}
                </div>
                
                <div class="order-footer">
                    <div class="order-total">NT$ ${order.totalAmount}</div>
                    <div class="order-actions">
                        ${this.getOrderActions(order)}
                    </div>
                </div>
                
                ${order.note ? `<div class="order-note">備註: ${order.note}</div>` : ''}
            </div>
        `;
    }

    // 獲取狀態文字
    getStatusText(status) {
        const statusMap = {
            'pending': '待處理',
            'preparing': '製作中',
            'ready': '待取餐',
            'completed': '已完成'
        };
        return statusMap[status] || status;
    }

    // 獲取訂單操作按鈕
    getOrderActions(order) {
        const actions = [];
        
        switch (order.status) {
            case 'pending':
                actions.push(`<button class="btn-action btn-start" onclick="event.stopPropagation(); orderManager.updateOrderStatus(${order.id}, 'preparing')">開始製作</button>`);
                break;
            case 'preparing':
                actions.push(`<button class="btn-action btn-ready" onclick="event.stopPropagation(); orderManager.updateOrderStatus(${order.id}, 'ready')">製作完成</button>`);
                break;
            case 'ready':
                actions.push(`<button class="btn-action btn-complete" onclick="event.stopPropagation(); orderManager.updateOrderStatus(${order.id}, 'completed')">已取餐</button>`);
                break;
        }
        
        return actions.join('');
    }

    // 更新訂單狀態
    updateOrderStatus(orderId, newStatus) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;

        order.status = newStatus;
        
        if (newStatus === 'completed') {
            order.completedAt = new Date().toISOString();
        }

        this.saveOrders();
        this.filterOrders();
        this.showNotification(`訂單 #${orderId} 狀態已更新為 ${this.getStatusText(newStatus)}`);
    }

    // 更新統計
    updateStats() {
        const stats = {
            pending: 0,
            preparing: 0,
            ready: 0,
            completed: 0
        };

        // 只統計今天的訂單
        const today = new Date().toISOString().split('T')[0];
        const todayOrders = this.orders.filter(order => order.createdAt.startsWith(today));
        
        todayOrders.forEach(order => {
            if (stats.hasOwnProperty(order.status)) {
                stats[order.status]++;
            }
        });

        document.getElementById('pendingOrders').textContent = stats.pending;
        document.getElementById('preparingOrders').textContent = stats.preparing;
        document.getElementById('readyOrders').textContent = stats.ready;
        document.getElementById('completedOrders').textContent = stats.completed;
    }

    // 顯示訂單詳情
    showOrderDetail(orderId) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;

        this.currentOrderId = orderId;
        
        // 填充訂單資訊
        document.getElementById('detailOrderId').textContent = order.id;
        document.getElementById('detailTableNumber').textContent = order.tableNumber;
        document.getElementById('detailCreatedAt').textContent = new Date(order.createdAt).toLocaleString();
        document.getElementById('detailStatus').textContent = this.getStatusText(order.status);
        document.getElementById('detailTotalAmount').textContent = `NT$ ${order.totalAmount}`;

        // 填充訂單項目
        const itemsContainer = document.getElementById('detailOrderItems');
        itemsContainer.innerHTML = order.items.map(item => `
            <div class="order-item-detail">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">x ${item.quantity}</span>
                <span class="item-price">NT$ ${item.price * item.quantity}</span>
            </div>
        `).join('');

        // 填充狀態操作按鈕
        const actionsContainer = document.getElementById('statusActions');
        actionsContainer.innerHTML = this.getDetailActions(order);

        // 顯示對話框
        document.getElementById('orderDetailModal').style.display = 'block';
    }

    // 獲取詳情頁面的操作按鈕
    getDetailActions(order) {
        const actions = [];
        
        switch (order.status) {
            case 'pending':
                actions.push(`<button class="btn-primary" onclick="orderManager.updateOrderStatusFromDetail('preparing')">開始製作</button>`);
                actions.push(`<button class="btn-danger" onclick="orderManager.cancelOrder()">取消訂單</button>`);
                break;
            case 'preparing':
                actions.push(`<button class="btn-success" onclick="orderManager.updateOrderStatusFromDetail('ready')">製作完成</button>`);
                break;
            case 'ready':
                actions.push(`<button class="btn-success" onclick="orderManager.updateOrderStatusFromDetail('completed')">已取餐</button>`);
                break;
            case 'completed':
                actions.push(`<button class="btn-secondary" onclick="orderManager.printReceipt()">列印收據</button>`);
                break;
        }
        
        actions.push(`<button class="btn-secondary" onclick="orderManager.hideOrderDetail()">關閉</button>`);
        
        return actions.join('');
    }

    // 從詳情頁面更新訂單狀態
    updateOrderStatusFromDetail(newStatus) {
        if (this.currentOrderId) {
            this.updateOrderStatus(this.currentOrderId, newStatus);
            this.hideOrderDetail();
        }
    }

    // 隱藏訂單詳情
    hideOrderDetail() {
        document.getElementById('orderDetailModal').style.display = 'none';
        this.currentOrderId = null;
    }

    // 取消訂單
    cancelOrder() {
        if (confirm('確定要取消這個訂單嗎？')) {
            this.orders = this.orders.filter(o => o.id !== this.currentOrderId);
            this.saveOrders();
            this.filterOrders();
            this.hideOrderDetail();
            this.showNotification('訂單已取消');
        }
    }

    // 列印收據（模擬）
    printReceipt() {
        const order = this.orders.find(o => o.id === this.currentOrderId);
        if (!order) return;

        const receiptContent = `
            餐廳收據
            ==================
            訂單編號: #${order.id}
            桌號: ${order.tableNumber}
            時間: ${new Date(order.createdAt).toLocaleString()}
            
            訂單項目:
            ${order.items.map(item => 
                `${item.name} x${item.quantity} - NT$ ${item.price * item.quantity}`
            ).join('\n            ')}
            
            ==================
            總計: NT$ ${order.totalAmount}
            
            謝謝光臨！
        `;

        // 在新視窗中顯示收據內容
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head><title>收據 #${order.id}</title></head>
                <body style="font-family: monospace; white-space: pre;">${receiptContent}</body>
            </html>
        `);
        printWindow.print();
        printWindow.close();
        
        this.showNotification('收據已列印');
    }

    // 刷新訂單
    refreshOrders() {
        this.initializeData();
        this.loadOrders();
        this.showNotification('訂單已刷新');
    }

    // 新增測試訂單
    addTestOrder() {
        const menuData = localStorage.getItem('restaurantMenuItems');
        if (!menuData) {
            alert('請先設定菜單項目');
            return;
        }

        const menuItems = JSON.parse(menuData).filter(item => item.status === 'available');
        if (menuItems.length === 0) {
            alert('沒有可供應的菜品');
            return;
        }

        // 隨機生成測試訂單
        const randomTable = Math.floor(Math.random() * 20) + 1;
        const randomItemCount = Math.floor(Math.random() * 3) + 1;
        const selectedItems = [];
        
        for (let i = 0; i < randomItemCount; i++) {
            const randomItem = menuItems[Math.floor(Math.random() * menuItems.length)];
            const quantity = Math.floor(Math.random() * 3) + 1;
            
            selectedItems.push({
                id: randomItem.id,
                name: randomItem.name,
                price: randomItem.price,
                quantity: quantity
            });
        }

        const totalAmount = selectedItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        const newOrder = {
            id: Date.now(),
            tableNumber: randomTable,
            items: selectedItems,
            totalAmount: totalAmount,
            status: 'pending',
            createdAt: new Date().toISOString(),
            note: Math.random() > 0.7 ? '測試訂單備註' : ''
        };

        this.orders.unshift(newOrder);
        this.saveOrders();
        this.filterOrders();
        this.showNotification(`新增測試訂單 #${newOrder.id}`);
    }

    // 開始自動刷新
    startAutoRefresh() {
        setInterval(() => {
            this.updateStats();
        }, 30000); // 每30秒更新一次統計
    }

    // 顯示通知
    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        `;

        document.body.appendChild(notification);

        setTimeout(() => notification.style.opacity = '1', 100);
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// 初始化訂單管理器
const orderManager = new OrderManager();
