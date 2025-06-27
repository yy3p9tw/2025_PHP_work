<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 檢查是否已登入，並檢查權限
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// 處理篩選週期
$filter_period = $_GET['period'] ?? 'today'; // 預設為今日

$date_condition = '';
$display_period_text = '今日';

switch ($filter_period) {
    case 'today':
        $date_condition = 'DATE(order_time) = CURDATE()';
        $display_period_text = '今日';
        break;
    case 'week':
        $date_condition = 'YEARWEEK(order_time, 1) = YEARWEEK(CURDATE(), 1)'; // MySQL YEARWEEK, 1 = 週一為一週開始
        $display_period_text = '本週';
        break;
    case 'month':
        $date_condition = 'YEAR(order_time) = YEAR(CURDATE()) AND MONTH(order_time) = MONTH(CURDATE())';
        $display_period_text = '本月';
        break;
    default:
        $date_condition = 'DATE(order_time) = CURDATE()';
        $display_period_text = '今日';
        $filter_period = 'today';
        break;
}

// 獲取待處理訂單數量 (根據篩選週期)
$pending_orders_stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND " . $date_condition);
$pending_orders_count = $pending_orders_stmt->fetchColumn();

// 獲取總收入 (根據篩選週期)
$total_revenue_stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND " . $date_condition);
$total_revenue = $total_revenue_stmt->fetchColumn();
$total_revenue = $total_revenue ? number_format($total_revenue, 2) : '0.00';

// 獲取餐點總數
$total_menu_items_stmt = $conn->query("SELECT COUNT(*) FROM menu_items");
$total_menu_items_count = $total_menu_items_stmt->fetchColumn();

// 獲取最新訂單 (根據篩選週期)
$latest_orders_stmt = $conn->query("SELECT o.*, t.table_number FROM orders o JOIN tables t ON o.table_id = t.id WHERE " . $date_condition . " ORDER BY o.order_time DESC LIMIT 10");
$latest_orders = $latest_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理儀表板</title>
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
                            <a class="nav-link active" aria-current="page" href="dashboard.php">
                                儀表板
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
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
                    <h1 class="h2">儀表板 - <?php echo $display_period_text; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?period=today" class="btn btn-sm btn-outline-secondary <?php echo ($filter_period == 'today') ? 'active' : ''; ?>">今日</a>
                            <a href="?period=week" class="btn btn-sm btn-outline-secondary <?php echo ($filter_period == 'week') ? 'active' : ''; ?>">本週</a>
                            <a href="?period=month" class="btn btn-sm btn-outline-secondary <?php echo ($filter_period == 'month') ? 'active' : ''; ?>">本月</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">待處理訂單</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $pending_orders_count; ?></h5>
                                <p class="card-text">目前有待處理的訂單數量。</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">總收入</div>
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo $total_revenue; ?></h5>
                                <p class="card-text">已結帳訂單的總收入。</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-header">總餐點數</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_menu_items_count; ?></h5>
                                <p class="card-text">系統中已設定的餐點總數。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h3>最新訂單</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>桌號</th>
                                <th>下單時間</th>
                                <th>總金額</th>
                                <th>狀態</th>
                                <th>支付狀態</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($latest_orders):
                                foreach ($latest_orders as $order):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_time']); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(getOrderStatusText($order['status'])); ?></td>
                                    <td><?php echo htmlspecialchars(getPaymentStatusText($order['payment_status'])); ?></td>
                                </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="6">目前沒有訂單。</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Feather Icons (可選，用於圖標) -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>