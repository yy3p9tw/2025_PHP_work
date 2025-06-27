<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php'; // 新增這行

// 檢查是否已登入，並檢查權限
if (!isLoggedIn() || !isStaff()) { // 員工和管理員都可以查看訂單
    header('Location: index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// 處理訂單狀態更新
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare('UPDATE orders SET status = :new_status WHERE id = :order_id');
    $stmt->bindParam(':new_status', $new_status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    header('Location: orders.php?status_updated=true');
    exit();
}

// 處理支付狀態更新
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment_status'])) {
    $order_id = $_POST['order_id'];
    $new_payment_status = $_POST['new_payment_status'];

    $stmt = $conn->prepare('UPDATE orders SET payment_status = :new_payment_status WHERE id = :order_id');
    $stmt->bindParam(':new_payment_status', $new_payment_status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    header('Location: orders.php?payment_status_updated=true');
    exit();
}

// 獲取所有訂單，並按時間倒序排列
$orders_stmt = $conn->query("SELECT o.*, t.table_number FROM orders o JOIN tables t ON o.table_id = t.id ORDER BY o.order_time DESC");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 側邊導航欄 -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                儀表板
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="orders.php">
                                訂單管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="menu.php">
                                餐點管理
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                使用者管理
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                登出
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- 主要內容區域 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">訂單管理</h1>
                </div>

                <?php if (isset($_GET['status_updated'])): ?>
                    <div class="alert alert-success" role="alert">
                        訂單狀態已更新！
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['payment_status_updated'])): ?>
                    <div class="alert alert-success" role="alert">
                        支付狀態已更新！
                    </div>
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
                            <?php if ($orders): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_time']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                        <td><?php echo htmlspecialchars(getOrderStatusText($order['status'])); ?>
                                            <form action="orders.php" method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>待處理</option>
                                                    <option value="preparing" <?php echo ($order['status'] == 'preparing') ? 'selected' : ''; ?>>準備中</option>
                                                    <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>已完成</option>
                                                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>已取消</option>
                                                </select>
                                                <input type="hidden" name="update_order_status" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars(getPaymentStatusText($order['payment_status'])); ?>
                                            <form action="orders.php" method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="new_payment_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                    <option value="unpaid" <?php echo ($order['payment_status'] == 'unpaid') ? 'selected' : ''; ?>>未支付</option>
                                                    <option value="paid" <?php echo ($order['payment_status'] == 'paid') ? 'selected' : ''; ?>>已支付</option>
                                                </select>
                                                <input type="hidden" name="update_payment_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="<?php echo $order['id']; ?>">詳情</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                orderTableBody.innerHTML = '<tr><td colspan="7" class="text-center">目前沒有任何訂單。</td></tr>';
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- 訂單詳情 Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">訂單詳情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- 訂單詳情將通過 AJAX 加載到這裡 -->
                    加載中...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
</body>
</html>