<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// 分頁設定
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 篩選條件
$stock_code = isset($_GET['stock']) ? trim($_GET['stock']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

$where_conditions = ['user_id = ?'];
$params = [$user_id];

if (!empty($stock_code)) {
    $where_conditions[] = 'stock_code = ?';
    $params[] = $stock_code;
}

if (!empty($type)) {
    $where_conditions[] = 'type = ?';
    $params[] = $type;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// 獲取交易記錄總數
$total_result = $db->fetchOne("
    SELECT COUNT(*) as count FROM transactions 
    $where_clause
", $params);
$total_transactions = $total_result['count'];

$total_pages = ceil($total_transactions / $per_page);

// 獲取交易記錄
$transactions = $db->fetchAll("
    SELECT t.*, s.name as stock_name
    FROM transactions t
    JOIN stocks s ON t.stock_code = s.code
    $where_clause
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$per_page, $offset]));

// 獲取用戶持有的股票清單
$user_stocks = $db->fetchAll('
    SELECT DISTINCT p.stock_code, s.name as stock_name
    FROM portfolios p
    JOIN stocks s ON p.stock_code = s.code
    WHERE p.user_id = ?
    ORDER BY s.name
', [$user_id]);

$page_title = '交易記錄';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">交易記錄</h1>
            
            <!-- 篩選條件 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <select class="form-select" name="stock">
                                <option value="">所有股票</option>
                                <?php foreach ($user_stocks as $stock): ?>
                                    <option value="<?php echo htmlspecialchars($stock['stock_code']); ?>" 
                                            <?php echo $stock_code === $stock['stock_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($stock['stock_code'] . ' - ' . $stock['stock_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="type">
                                <option value="">所有類型</option>
                                <option value="buy" <?php echo $type === 'buy' ? 'selected' : ''; ?>>買入</option>
                                <option value="sell" <?php echo $type === 'sell' ? 'selected' : ''; ?>>賣出</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">篩選</button>
                            <a href="transactions.php" class="btn btn-outline-secondary">清除</a>
                        </div>
                        <div class="col-md-2">
                            <a href="portfolio.php" class="btn btn-outline-primary w-100">返回投資組合</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 交易記錄列表 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">共 <?php echo $total_transactions; ?> 筆交易記錄</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">還沒有交易記錄</p>
                            <a href="stocks.php" class="btn btn-primary">開始交易</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>時間</th>
                                        <th>股票</th>
                                        <th>類型</th>
                                        <th>數量</th>
                                        <th>價格</th>
                                        <th>總金額</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($transaction['stock_code']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($transaction['stock_name']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($transaction['type'] === 'buy'): ?>
                                                <span class="badge bg-success">買入</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">賣出</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($transaction['quantity']); ?></td>
                                        <td>$<?php echo number_format($transaction['price'], 2); ?></td>
                                        <td>$<?php echo number_format($transaction['total_amount'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- 分頁導航 -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="交易記錄分頁">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&stock=<?php echo $stock_code; ?>&type=<?php echo $type; ?>">上一頁</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&stock=<?php echo $stock_code; ?>&type=<?php echo $type; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&stock=<?php echo $stock_code; ?>&type=<?php echo $type; ?>">下一頁</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
