// 營業報表管理
class ReportsManager {
    constructor() {
        this.orders = [];
        this.menuItems = [];
        this.startDate = null;
        this.endDate = null;
        this.initializeData();
        this.initializeEventListeners();
        this.setDefaultDateRange();
        this.generateReport();
    }

    // 初始化資料
    initializeData() {
        // 載入訂單資料
        const orderData = localStorage.getItem('restaurantOrders');
        if (orderData) {
            this.orders = JSON.parse(orderData);
        }

        // 載入菜單資料
        const menuData = localStorage.getItem('restaurantMenuItems');
        if (menuData) {
            this.menuItems = JSON.parse(menuData);
        }
    }

    // 初始化事件監聽器
    initializeEventListeners() {
        document.getElementById('generateReport').addEventListener('click', () => {
            this.generateReport();
        });

        document.getElementById('exportPDF').addEventListener('click', () => {
            this.exportPDF();
        });

        document.getElementById('exportExcel').addEventListener('click', () => {
            this.exportExcel();
        });

        // 監聽日期變化
        document.getElementById('startDate').addEventListener('change', () => {
            this.updateDateRange();
        });

        document.getElementById('endDate').addEventListener('change', () => {
            this.updateDateRange();
        });
    }

    // 設定預設日期範圍（最近7天）
    setDefaultDateRange() {
        const today = new Date();
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 7);

        const todayStr = today.toISOString().split('T')[0];
        const weekAgoStr = weekAgo.toISOString().split('T')[0];

        document.getElementById('endDate').value = todayStr;
        document.getElementById('startDate').value = weekAgoStr;

        this.updateDateRange();
    }

    // 更新日期範圍
    updateDateRange() {
        this.startDate = document.getElementById('startDate').value;
        this.endDate = document.getElementById('endDate').value;
    }

    // 生成報表
    generateReport() {
        this.updateDateRange();
        
        if (!this.startDate || !this.endDate) {
            alert('請選擇開始和結束日期');
            return;
        }

        if (new Date(this.startDate) > new Date(this.endDate)) {
            alert('開始日期不能晚於結束日期');
            return;
        }

        // 篩選日期範圍內的訂單
        const filteredOrders = this.getOrdersInRange();
        
        // 生成各種報表
        this.generateRevenueOverview(filteredOrders);
        this.generateProductRanking(filteredOrders);
        this.generateTimeAnalysis(filteredOrders);
        this.generateTableUsage(filteredOrders);
        this.generateSalesChart(filteredOrders);

        this.showNotification('報表已生成');
    }

    // 獲取日期範圍內的訂單
    getOrdersInRange() {
        const startDateTime = new Date(this.startDate + 'T00:00:00');
        const endDateTime = new Date(this.endDate + 'T23:59:59');

        return this.orders.filter(order => {
            const orderDate = new Date(order.createdAt);
            return orderDate >= startDateTime && orderDate <= endDateTime && order.status === 'completed';
        });
    }

    // 生成營收概覽
    generateRevenueOverview(orders) {
        const totalRevenue = orders.reduce((sum, order) => sum + order.totalAmount, 0);
        const totalOrders = orders.length;
        const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;

        // 計算熱銷商品
        const itemSales = {};
        orders.forEach(order => {
            order.items.forEach(item => {
                if (!itemSales[item.name]) {
                    itemSales[item.name] = 0;
                }
                itemSales[item.name] += item.quantity;
            });
        });

        const topSellingItem = Object.keys(itemSales).length > 0 
            ? Object.keys(itemSales).reduce((a, b) => itemSales[a] > itemSales[b] ? a : b)
            : '-';

        // 更新顯示
        document.getElementById('totalRevenue').textContent = `$${totalRevenue.toLocaleString()}`;
        document.getElementById('totalOrders').textContent = totalOrders.toLocaleString();
        document.getElementById('avgOrderValue').textContent = `$${Math.round(avgOrderValue)}`;
        document.getElementById('topSellingItem').textContent = topSellingItem;
    }

    // 生成商品銷售排行
    generateProductRanking(orders) {
        const itemSales = {};
        const itemRevenue = {};

        // 統計銷售數據
        orders.forEach(order => {
            order.items.forEach(item => {
                if (!itemSales[item.name]) {
                    itemSales[item.name] = 0;
                    itemRevenue[item.name] = 0;
                }
                itemSales[item.name] += item.quantity;
                itemRevenue[item.name] += item.price * item.quantity;
            });
        });

        // 計算總營收用於占比計算
        const totalRevenue = Object.values(itemRevenue).reduce((sum, revenue) => sum + revenue, 0);

        // 轉換為陣列並排序
        const rankingData = Object.keys(itemSales).map(itemName => ({
            name: itemName,
            quantity: itemSales[itemName],
            revenue: itemRevenue[itemName],
            percentage: totalRevenue > 0 ? (itemRevenue[itemName] / totalRevenue * 100) : 0
        })).sort((a, b) => b.revenue - a.revenue);

        // 更新表格
        const tbody = document.getElementById('productRankingBody');
        tbody.innerHTML = rankingData.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>$${item.revenue.toLocaleString()}</td>
                <td>${item.percentage.toFixed(1)}%</td>
            </tr>
        `).join('');
    }

    // 生成時段分析
    generateTimeAnalysis(orders) {
        const periods = {
            morning: { start: 6, end: 11, orders: 0, revenue: 0 },
            lunch: { start: 11, end: 14, orders: 0, revenue: 0 },
            afternoon: { start: 14, end: 17, orders: 0, revenue: 0 },
            dinner: { start: 17, end: 22, orders: 0, revenue: 0 }
        };

        orders.forEach(order => {
            const hour = new Date(order.createdAt).getHours();
            
            for (const [periodName, period] of Object.entries(periods)) {
                if (hour >= period.start && hour < period.end) {
                    period.orders++;
                    period.revenue += order.totalAmount;
                    break;
                }
            }
        });

        // 更新顯示
        document.getElementById('morningOrders').textContent = `${periods.morning.orders} 筆訂單`;
        document.getElementById('morningRevenue').textContent = `$${periods.morning.revenue}`;
        
        document.getElementById('lunchOrders').textContent = `${periods.lunch.orders} 筆訂單`;
        document.getElementById('lunchRevenue').textContent = `$${periods.lunch.revenue}`;
        
        document.getElementById('afternoonOrders').textContent = `${periods.afternoon.orders} 筆訂單`;
        document.getElementById('afternoonRevenue').textContent = `$${periods.afternoon.revenue}`;
        
        document.getElementById('dinnerOrders').textContent = `${periods.dinner.orders} 筆訂單`;
        document.getElementById('dinnerRevenue').textContent = `$${periods.dinner.revenue}`;
    }

    // 生成桌位使用分析
    generateTableUsage(orders) {
        const tableUsage = {};
        const maxTable = 20; // 假設最多20桌

        // 初始化桌位使用統計
        for (let i = 1; i <= maxTable; i++) {
            tableUsage[i] = { orders: 0, revenue: 0 };
        }

        // 統計桌位使用
        orders.forEach(order => {
            const tableNumber = order.tableNumber;
            if (tableUsage[tableNumber]) {
                tableUsage[tableNumber].orders++;
                tableUsage[tableNumber].revenue += order.totalAmount;
            }
        });

        // 找出使用率最高的桌位
        const maxOrders = Math.max(...Object.values(tableUsage).map(table => table.orders));

        // 生成桌位網格
        const tableGrid = document.getElementById('tableUsageGrid');
        tableGrid.innerHTML = '';

        for (let i = 1; i <= maxTable; i++) {
            const table = tableUsage[i];
            const usagePercent = maxOrders > 0 ? (table.orders / maxOrders * 100) : 0;
            
            const tableElement = document.createElement('div');
            tableElement.className = 'table-usage-item';
            tableElement.innerHTML = `
                <div class="table-number">桌 ${i}</div>
                <div class="table-stats">
                    <div class="table-orders">${table.orders} 筆</div>
                    <div class="table-revenue">$${table.revenue}</div>
                </div>
                <div class="usage-bar">
                    <div class="usage-fill" style="width: ${usagePercent}%"></div>
                </div>
            `;
            
            tableGrid.appendChild(tableElement);
        }
    }

    // 生成銷售圖表（簡單的文字圖表）
    generateSalesChart(orders) {
        const canvas = document.getElementById('salesChart');
        const ctx = canvas.getContext('2d');
        
        // 清除畫布
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (orders.length === 0) {
            ctx.fillStyle = '#666';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('暫無數據', canvas.width / 2, canvas.height / 2);
            return;
        }

        // 按日期分組統計
        const dailySales = {};
        orders.forEach(order => {
            const date = order.createdAt.split('T')[0];
            if (!dailySales[date]) {
                dailySales[date] = 0;
            }
            dailySales[date] += order.totalAmount;
        });

        // 繪製簡單的柱狀圖
        const dates = Object.keys(dailySales).sort();
        const maxRevenue = Math.max(...Object.values(dailySales));
        
        if (dates.length === 0) return;

        const barWidth = canvas.width / dates.length * 0.8;
        const barSpacing = canvas.width / dates.length * 0.2;
        
        dates.forEach((date, index) => {
            const revenue = dailySales[date];
            const barHeight = (revenue / maxRevenue) * (canvas.height - 60);
            const x = index * (barWidth + barSpacing) + barSpacing / 2;
            const y = canvas.height - barHeight - 30;
            
            // 繪製柱子
            ctx.fillStyle = '#4a90e2';
            ctx.fillRect(x, y, barWidth, barHeight);
            
            // 繪製日期標籤
            ctx.fillStyle = '#666';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(date.split('-')[2], x + barWidth / 2, canvas.height - 10);
            
            // 繪製金額
            ctx.fillStyle = '#333';
            ctx.font = '12px Arial';
            ctx.fillText(`$${revenue}`, x + barWidth / 2, y - 5);
        });
    }

    // 匯出 PDF（模擬）
    exportPDF() {
        const reportContent = this.generateReportContent();
        const blob = new Blob([reportContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `營業報表_${this.startDate}_${this.endDate}.txt`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showNotification('報表已匯出（文字格式）');
    }

    // 匯出 Excel（模擬）
    exportExcel() {
        const orders = this.getOrdersInRange();
        const csvContent = this.generateCSVContent(orders);
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `訂單明細_${this.startDate}_${this.endDate}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showNotification('訂單明細已匯出（CSV 格式）');
    }

    // 生成報表內容
    generateReportContent() {
        const orders = this.getOrdersInRange();
        const totalRevenue = orders.reduce((sum, order) => sum + order.totalAmount, 0);
        
        return `
營業報表
日期範圍: ${this.startDate} 至 ${this.endDate}
生成時間: ${new Date().toLocaleString()}

====================

營收概覽:
- 總訂單數: ${orders.length} 筆
- 總營收: $${totalRevenue.toLocaleString()}
- 平均客單價: $${orders.length > 0 ? Math.round(totalRevenue / orders.length) : 0}

====================

詳細資料請參考系統中的報表頁面。
        `;
    }

    // 生成 CSV 內容
    generateCSVContent(orders) {
        const headers = ['訂單編號', '桌號', '訂單時間', '商品名稱', '數量', '單價', '小計', '訂單總額'];
        const rows = [headers];
        
        orders.forEach(order => {
            order.items.forEach((item, index) => {
                const row = [
                    index === 0 ? order.id : '',
                    index === 0 ? order.tableNumber : '',
                    index === 0 ? new Date(order.createdAt).toLocaleString() : '',
                    item.name,
                    item.quantity,
                    item.price,
                    item.price * item.quantity,
                    index === 0 ? order.totalAmount : ''
                ];
                rows.push(row);
            });
        });
        
        return rows.map(row => row.join(',')).join('\n');
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

// 初始化報表管理器
const reportsManager = new ReportsManager();
