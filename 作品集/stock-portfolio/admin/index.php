<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/auth.php';

// 檢查是否已登入且為管理員
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();

// 獲取系統統計資料
$stats = [
    'total_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users')['count'],
    'total_stocks' => $db->fetchOne('SELECT COUNT(*) as count FROM stocks WHERE status = ?', ['active'])['count'],
    'total_transactions' => $db->fetchOne('SELECT COUNT(*) as count FROM transactions')['count'],
    'total_news' => $db->fetchOne('SELECT COUNT(*) as count FROM news WHERE status = ?', ['active'])['count'],
    'active_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users WHERE is_active = 1')['count'],
    'total_portfolios' => $db->fetchOne('SELECT COUNT(*) as count FROM portfolios WHERE quantity > 0')['count']
];

// 獲取最新用戶
$latest_users = $db->fetchAll('
    SELECT username, email, created_at, role 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
');

// 獲取最新交易
$latest_transactions = $db->fetchAll('
    SELECT t.*, u.username, s.name as stock_name
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN stocks s ON t.stock_code = s.code
    ORDER BY t.created_at DESC
    LIMIT 10
');

// 獲取系統設定
$settings = $db->fetchAll('SELECT * FROM settings ORDER BY setting_key');

$page_title = '管理後台';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
 

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-tachometer-alt me-2"></i>管理後台</h1>
                <div class="text-muted">
                    歡迎，<?php echo htmlspecialchars($_SESSION['username']); ?>！
                </div>
            </div>

            <!-- 系統統計卡片 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_users']); ?></div>
                                    <div>總用戶數</div>
                                    <small>活躍: <?php echo number_format($stats['active_users']); ?></small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="users.php" class="text-white text-decoration-none">
                                管理用戶 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_stocks']); ?></div>
                                    <div>股票數量</div>
                                    <small>投資組合: <?php echo number_format($stats['total_portfolios']); ?></small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="stocks.php" class="text-white text-decoration-none">
                                管理股票 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_news']); ?></div>
                                    <div>新聞數量</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-newspaper fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="news.php" class="text-white text-decoration-none">
                                管理新聞 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_transactions']); ?></div>
                                    <div>交易記錄</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exchange-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="transactions.php" class="text-white text-decoration-none">
                                查看交易 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 快速管理工具 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">快速管理</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="users.php" class="btn btn-outline-primary">
                                    <i class="fas fa-users me-2"></i>用戶管理
                                </a>
                                <a href="stocks.php" class="btn btn-outline-success">
                                    <i class="fas fa-chart-line me-2"></i>股票管理
                                </a>
                                <a href="news.php" class="btn btn-outline-info">
                                    <i class="fas fa-newspaper me-2"></i>新聞管理
                                </a>
                                <a href="settings.php" class="btn btn-outline-warning">
                                    <i class="fas fa-cog me-2"></i>系統設定
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">系統狀態</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h5 text-success"><?php echo number_format($stats['active_users']); ?></div>
                                    <div class="text-muted">活躍用戶</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h5 text-info"><?php echo number_format($stats['total_portfolios']); ?></div>
                                    <div class="text-muted">投資組合</div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100) : 0; ?>%">
                                            用戶活躍度: <?php echo $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100) : 0; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- 最新用戶 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0">最新註冊用戶</h5>
                            <a href="users.php" class="btn btn-sm btn-outline-primary">查看全部</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($latest_users)): ?>
                                <p class="text-muted">暫無用戶</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($latest_users as $user): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo $user['role'] === 'admin' ? '管理員' : '一般用戶'; ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 最新交易 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0">最新交易記錄</h5>
                            <a href="transactions.php" class="btn btn-sm btn-outline-primary">查看全部</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($latest_transactions)): ?>
                                <p class="text-muted">暫無交易記錄</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($latest_transactions, 0, 5) as $transaction): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo htmlspecialchars($transaction['username']); ?></strong>
                                                <span class="badge bg-<?php echo $transaction['type'] === 'buy' ? 'success' : 'danger'; ?>">
                                                    <?php echo $transaction['type'] === 'buy' ? '買入' : '賣出'; ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($transaction['stock_code']); ?> - 
                                                    <?php echo htmlspecialchars($transaction['stock_name']); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div>$<?php echo number_format($transaction['total_amount'], 2); ?></div>
                                                <small class="text-muted">
                                                    <?php echo date('m-d H:i', strtotime($transaction['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 系統設定預覽 -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">系統設定</h5>
                    <a href="settings.php" class="btn btn-sm btn-outline-primary">管理設定</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($settings, 0, 6) as $setting): ?>
                        <div class="col-md-4 mb-3">
                            <div class="border-start border-3 border-primary ps-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($setting['setting_key']); ?></h6>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($setting['setting_value']); ?></p>
                                <?php if ($setting['description']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
