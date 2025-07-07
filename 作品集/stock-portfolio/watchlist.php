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

// 獲取關注清單
$watchlist = $db->fetchAll('
    SELECT w.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent, s.volume
    FROM watchlist w
    JOIN stocks s ON w.stock_code = s.code
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
', [$user_id]);

// 處理移除操作
if (isset($_GET['remove']) && $_GET['remove']) {
    $stock_code = $_GET['remove'];
    $db->query('DELETE FROM watchlist WHERE user_id = ? AND stock_code = ?', [$user_id, $stock_code]);
    header('Location: watchlist.php');
    exit();
}

$page_title = '關注清單';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">我的關注清單</h1>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">關注股票</h5>
                    <a href="stocks.php" class="btn btn-primary btn-sm">瀏覽股票</a>
                </div>
                <div class="card-body">
                    <?php if (empty($watchlist)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">您的關注清單是空的</p>
                            <a href="stocks.php" class="btn btn-primary">開始關注股票</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>現價</th>
                                        <th>漲跌</th>
                                        <th>漲跌幅</th>
                                        <th>成交量</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($watchlist as $stock): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stock['stock_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($stock['stock_name']); ?></td>
                                        <td>$<?php echo number_format($stock['current_price'], 2); ?></td>
                                        <td class="<?php echo $stock['price_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['price_change'] >= 0 ? '+' : '') . number_format($stock['price_change'], 2); ?>
                                        </td>
                                        <td class="<?php echo $stock['change_percent'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['change_percent'] >= 0 ? '+' : '') . number_format($stock['change_percent'], 2); ?>%
                                        </td>
                                        <td><?php echo number_format($stock['volume']); ?></td>
                                        <td>
                                            <a href="stock_detail.php?code=<?php echo $stock['stock_code']; ?>" 
                                               class="btn btn-sm btn-outline-primary">詳情</a>
                                            <a href="?remove=<?php echo $stock['stock_code']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('確定要移除此股票嗎？')">移除</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
