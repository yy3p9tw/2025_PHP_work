<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// 獲取統計資料
$stats = [];

// 用戶統計
$stmt = $conn->prepare("SELECT COUNT(*) as total_users, COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_users FROM users");
$stmt->execute();
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC);

// 股票統計
$stmt = $conn->prepare("SELECT COUNT(*) as total_stocks, COUNT(CASE WHEN status = 'active' THEN 1 END) as active_stocks FROM stocks");
$stmt->execute();
$stats['stocks'] = $stmt->fetch(PDO::FETCH_ASSOC);

// 交易統計
$stmt = $conn->prepare("SELECT COUNT(*) as total_transactions, SUM(total_amount) as total_volume FROM transactions");
$stmt->execute();
$stats['transactions'] = $stmt->fetch(PDO::FETCH_ASSOC);

// 新聞統計
$stmt = $conn->prepare("SELECT COUNT(*) as total_news, COUNT(CASE WHEN status = 'active' THEN 1 END) as active_news FROM news");
$stmt->execute();
$stats['news'] = $stmt->fetch(PDO::FETCH_ASSOC);

// 最近活動
$stmt = $conn->prepare("
    SELECT 'transaction' as type, CONCAT(u.username, ' 進行了 ', t.type, ' ', t.stock_code, ' 交易') as message, t.created_at as timestamp
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理後台 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog"></i> 管理後台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">儀表板</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">用戶管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stocks.php">股票管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">新聞管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">系統設定</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home"></i> 前台
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="fas fa-tachometer-alt"></i> 系統儀表板</h1>
                <p class="text-muted">歡迎使用股票投資組合管理系統後台</p>
            </div>
        </div>

        <!-- 統計卡片 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>用戶總數</h5>
                                <h3><?php echo number_format($stats['users']['total_users']); ?></h3>
                                <small>活躍用戶: <?php echo number_format($stats['users']['active_users']); ?></small>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>股票總數</h5>
                                <h3><?php echo number_format($stats['stocks']['total_stocks']); ?></h3>
                                <small>啟用中: <?php echo number_format($stats['stocks']['active_stocks']); ?></small>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>交易總數</h5>
                                <h3><?php echo number_format($stats['transactions']['total_transactions']); ?></h3>
                                <small>總交易額: $<?php echo number_format($stats['transactions']['total_volume'], 2); ?></small>
                            </div>
                            <div>
                                <i class="fas fa-exchange-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>新聞總數</h5>
                                <h3><?php echo number_format($stats['news']['total_news']); ?></h3>
                                <small>啟用中: <?php echo number_format($stats['news']['active_news']); ?></small>
                            </div>
                            <div>
                                <i class="fas fa-newspaper fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 最近活動 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> 最近活動</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span><?php echo htmlspecialchars($activity['message']); ?></span>
                                    <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($activity['timestamp'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 系統狀態 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-server"></i> 系統狀態</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="fas fa-database fa-2x text-success"></i>
                                    <p class="mt-2">資料庫<br><small class="text-success">正常</small></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="fas fa-wifi fa-2x text-success"></i>
                                    <p class="mt-2">API<br><small class="text-success">正常</small></p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="refreshData()">
                                <i class="fas fa-sync-alt"></i> 重新整理資料
                            </button>
                            <button class="btn btn-secondary" onclick="clearCache()">
                                <i class="fas fa-trash"></i> 清除快取
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function refreshData() {
            alert('資料重新整理中...');
            location.reload();
        }

        function clearCache() {
            if (confirm('確定要清除快取嗎？')) {
                alert('快取已清除');
            }
        }
    </script>
</body>
</html>
