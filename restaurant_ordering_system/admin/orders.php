<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php'; // Ensure functions.php is included

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    exit;
}

// Fetch all orders for display
$orders = [];
try {
    $stmt = $pdo->query("SELECT o.*, t.table_number FROM orders o JOIN tables t ON o.table_id = t.id ORDER BY o.order_time DESC");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error_message = "無法載入訂單資料。";
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>訂單管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">儀表板</a></li>
                        <li class="nav-item"><a class="nav-link active" href="orders.php">訂單管理</a></li>
                        <li class="nav-item"><a class="nav-link" href="menu.php">餐點管理</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="users.php">使用者管理</a></li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2"><li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li></ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">訂單管理</h1>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-sm" id="ordersTable">
                        <thead>
                            <tr>
                                <th>訂單ID</th>
                                <th>桌號</th>
                                <th>下單時間</th>
                                <th>總金額</th>
                                <th>狀態</th>
                                <th>支付狀態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="7" class="text-center">目前沒有任何訂單。</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_time']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                        <td><?php echo htmlspecialchars(getOrderStatusText($order['status'])); ?></td>
                                        <td><?php echo htmlspecialchars(getPaymentStatusText($order['payment_status'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm view-details-btn" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="<?php echo $order['id']; ?>">詳情</button>
                                            <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                                                <button type="button" class="btn btn-success btn-sm update-status-btn" data-order-id="<?php echo $order['id']; ?>" data-new-status="completed">完成</button>
                                                <button type="button" class="btn btn-danger btn-sm update-status-btn" data-order-id="<?php echo $order['id']; ?>" data-new-status="cancelled">取消</button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-danger btn-sm delete-order-btn" data-order-id="<?php echo $order['id']; ?>">刪除</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">訂單詳情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    加載中...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderDetailsModal = document.getElementById('orderDetailsModal');
            const orderDetailsContent = document.getElementById('orderDetailsContent');

            // View Details Button Click
            document.querySelectorAll('.view-details-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    orderDetailsContent.innerHTML = '加載中...'; // Reset modal content

                    fetch(`api_get_order_details.php?order_id=${orderId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                orderDetailsContent.innerHTML = `<p class="text-danger">錯誤: ${data.error}</p>`;
                                return;
                            }
                            let html = '<table class="table table-bordered table-sm"><thead><tr><th>餐點</th><th>數量</th><th>單價</th><th>小計</th></tr></thead><tbody>';
                            data.forEach(item => {
                                html += `<tr>
                                    <td>${item.item_name}</td>
                                    <td>${item.quantity}</td>
                                    <td>${parseFloat(item.price).toFixed(2)}</td>
                                    <td>${(parseFloat(item.quantity) * parseFloat(item.price)).toFixed(2)}</td>
                                </tr>`;
                            });
                            html += '</tbody></table>';
                            orderDetailsContent.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error fetching order details:', error);
                            orderDetailsContent.innerHTML = '<p class="text-danger">無法載入訂單詳情。</p>';
                        });
                });
            });

            // Update Status Button Click
            document.querySelectorAll('.update-status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    const newStatus = this.dataset.newStatus;

                    if (confirm(`確定要將訂單 ${orderId} 的狀態更新為 ${newStatus === 'completed' ? '已完成' : '已取消'} 嗎？`)) {
                        fetch('api_update_order_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ order_id: orderId, status: newStatus })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload(); // Reload page to show updated status
                            } else {
                                alert(`更新失敗: ${data.message}`);
                            }
                        })
                        .catch(error => {
                            console.error('Error updating order status:', error);
                            alert('更新訂單狀態時發生錯誤。');
                        });
                    }
                });
            });

            // Delete Order Button Click
            document.querySelectorAll('.delete-order-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    if (confirm(`確定要刪除訂單 ${orderId} 嗎？此操作無法復原。`)) {
                        fetch('api_delete_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ order_id: orderId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                location.reload(); // Reload page to show updated list
                            } else {
                                alert(`刪除失敗: ${data.message}`);
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting order:', error);
                            alert('刪除訂單時發生錯誤。');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>