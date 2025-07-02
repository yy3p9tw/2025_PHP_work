<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    exit;
}

require_once '../includes/db.php';

// Fetch some basic data for dashboard widgets
$pending_orders = 0;
$total_users = 0;

try {
    $pending_orders = $pdo->query("SELECT count(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $total_users = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
    // You can set an error message to display on the dashboard
    $dashboard_error = "Could not load dashboard data.";
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理儀表板</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active" href="dashboard.php">儀表板</a></li>
                        <li class="nav-item"><a class="nav-link" href="orders.php">訂單管理</a></li>
                        <li class="nav-item"><a class="nav-link" href="menu.php">餐點管理</a></li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="users.php">使用者管理</a></li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2"><li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li></ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">儀表板</h1>
                </div>

                <?php if (isset($dashboard_error)): ?>
                    <div class="alert alert-danger"><?php echo $dashboard_error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">待處理訂單</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $pending_orders; ?></h5>
                                <p class="card-text">目前等待處理的訂單數量。</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-header">總使用者數</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_users; ?></h5>
                                <p class="card-text">系統中註冊的使用者總數。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h2>快速操作</h2>
                <p>
                    <a href="menu.php" class="btn btn-primary">管理餐點</a>
                    <a href="orders.php" class="btn btn-secondary">查看所有訂單</a>
                </p>

            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>