// admin_script.js

document.addEventListener('DOMContentLoaded', function() {
    // 訂單詳情 Modal 邏輯 (已存在)
    var orderDetailsModal = document.getElementById('orderDetailsModal');
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // 觸發模態框的按鈕
            var orderId = button.getAttribute('data-order-id'); // 從按鈕獲取訂單ID

            var modalBody = orderDetailsModal.querySelector('#orderDetailsContent');
            modalBody.innerHTML = '加載中...'; // 顯示加載提示

            // 使用 AJAX 加載訂單詳情
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    modalBody.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error loading order details:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger">無法加載訂單詳情。</div>';
                });
        });
    }

    // 訂單列表自動更新邏輯
    const orderTableBody = document.querySelector('#ordersTable tbody');
    if (orderTableBody) {
        function fetchOrders() {
            fetch('fetch_pending_orders.php')
                .then(response => response.json())
                .then(data => {
                    orderTableBody.innerHTML = ''; // 清空現有內容
                    if (data.length > 0) {
                        data.forEach(order => {
                            const row = `
                                <tr>
                                    <td>${order.id}</td>
                                    <td>${order.table_number}</td>
                                    <td>${order.order_time}</td>
                                    <td>$${order.total_amount_formatted}</td>
                                    <td>
                                        <form action="orders.php" method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="${order.id}">
                                            <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>待處理</option>
                                                <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>準備中</option>
                                                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>已完成</option>
                                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>已取消</option>
                                            </select>
                                            <input type="hidden" name="update_order_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form action="orders.php" method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="${order.id}">
                                            <select name="new_payment_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                <option value="unpaid" ${order.payment_status === 'unpaid' ? 'selected' : ''}>未支付</option>
                                                <option value="paid" ${order.payment_status === 'paid' ? 'selected' : ''}>已支付</option>
                                            </select>
                                            <input type="hidden" name="update_payment_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="${order.id}">詳情</button>
                                    </td>
                                </tr>
                            `;
                            orderTableBody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        orderTableBody.innerHTML = '<tr><td colspan="7" class="text-center">目前沒有任何待處理訂單。</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching orders:', error);
                    orderTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">加載訂單失敗。</td></tr>';
                });
        }

        // 每 5 秒更新一次訂單列表
        setInterval(fetchOrders, 5000);

        // 首次載入時也獲取一次
        fetchOrders();
    }
});