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

// 獲取搜尋和篩選參數
$search = $_GET['search'] ?? '';
$transaction_type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 構建查詢條件
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = '(u.username LIKE ? OR s.name LIKE ? OR s.code LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($transaction_type)) {
    $where_conditions[] = 't.type = ?';
    $params[] = $transaction_type;
}

if (!empty($date_from)) {
    $where_conditions[] = 'DATE(t.created_at) >= ?';
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = 'DATE(t.created_at) <= ?';
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 獲取交易記錄
$transactions = $db->fetchAll("
    SELECT t.*, u.username, s.name as stock_name, s.code as stock_code
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN stocks s ON t.stock_code = s.code
    {$where_clause}
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

// 獲取總數
$total_result = $db->fetchOne("
    SELECT COUNT(*) as count 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN stocks s ON t.stock_code = s.code
    {$where_clause}
", $params);
$total = $total_result['count'];
$total_pages = ceil($total / $limit);

// 獲取統計資料
$stats = [
    'total_buy' => $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE type = 'buy'")['count'],
    'total_sell' => $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE type = 'sell'")['count'],
    'total_amount' => $db->fetchOne("SELECT SUM(total_amount) as total FROM transactions")['total'] ?? 0,
    'total_volume' => $db->fetchOne("SELECT SUM(quantity) as total FROM transactions")['total'] ?? 0
];

$page_title = '交易記錄管理';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>交易記錄管理</h1>
            </div>

            <!-- 統計卡片 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_buy']); ?></div>
                                    <div>買入交易</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-up fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo number_format($stats['total_sell']); ?></div>
                                    <div>賣出交易</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-down fa-2x"></i>
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
                                    <div class="h4">$<?php echo number_format($stats['total_amount'], 0); ?></div>
                                    <div>總交易金額</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
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
                                    <div class="h4"><?php echo number_format($stats['total_volume']); ?></div>
                                    <div>總交易量</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-bar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 搜尋和篩選 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="搜尋用戶或股票" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="type">
                                <option value="">所有類型</option>
                                <option value="buy" <?php echo $transaction_type === 'buy' ? 'selected' : ''; ?>>買入</option>
                                <option value="sell" <?php echo $transaction_type === 'sell' ? 'selected' : ''; ?>>賣出</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" 
                                   placeholder="開始日期" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" 
                                   placeholder="結束日期" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">搜尋</button>
                        </div>
                        <div class="col-md-1">
                            <a href="?" class="btn btn-outline-secondary w-100">清除</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 交易記錄列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用戶</th>
                                    <th>股票</th>
                                    <th>類型</th>
                                    <th>數量</th>
                                    <th>價格</th>
                                    <th>總額</th>
                                    <th>交易時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($transaction['stock_code']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($transaction['stock_name']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $transaction['type'] === 'buy' ? 'success' : 'danger'; ?>">
                                            <?php echo $transaction['type'] === 'buy' ? '買入' : '賣出'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($transaction['quantity']); ?></td>
                                    <td>$<?php echo number_format($transaction['price'], 2); ?></td>
                                    <td>$<?php echo number_format($transaction['quantity'] * $transaction['price'], 2); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="交易記錄分頁">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($transaction_type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">上一頁</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($transaction_type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($transaction_type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">下一頁</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
