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

// 獲取用戶投資組合
$portfolios = $db->fetchAll('
    SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent
    FROM portfolios p
    JOIN stocks s ON p.stock_code = s.code
    WHERE p.user_id = ? AND p.quantity > 0
    ORDER BY p.updated_at DESC
', [$user_id]);

// 計算總值
$total_value = 0;
$total_cost = 0;
$total_gain_loss = 0;

foreach ($portfolios as $portfolio) {
    $current_value = $portfolio['current_price'] * $portfolio['quantity'];
    $cost = $portfolio['avg_price'] * $portfolio['quantity'];
    $gain_loss = $current_value - $cost;
    
    $total_value += $current_value;
    $total_cost += $cost;
    $total_gain_loss += $gain_loss;
}

$total_gain_loss_percent = $total_cost > 0 ? ($total_gain_loss / $total_cost) * 100 : 0;

$page_title = '投資組合';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">我的投資組合</h1>
            
            <!-- 投資組合總覽 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">總市值</h5>
                            <h4 class="text-primary">$<?php echo number_format($total_value, 2); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">總成本</h5>
                            <h4 class="text-secondary">$<?php echo number_format($total_cost, 2); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">損益</h5>
                            <h4 class="<?php echo $total_gain_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                $<?php echo number_format($total_gain_loss, 2); ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">報酬率</h5>
                            <h4 class="<?php echo $total_gain_loss_percent >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo number_format($total_gain_loss_percent, 2); ?>%
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 持股明細 -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">持股明細</h5>
                    <a href="transactions.php" class="btn btn-primary btn-sm">交易記錄</a>
                </div>
                <div class="card-body">
                    <?php if (empty($portfolios)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">您還沒有任何投資組合</p>
                            <a href="stocks.php" class="btn btn-primary">開始投資</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>持有股數</th>
                                        <th>平均成本</th>
                                        <th>現價</th>
                                        <th>市值</th>
                                        <th>損益</th>
                                        <th>報酬率</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($portfolios as $portfolio): 
                                        $current_value = $portfolio['current_price'] * $portfolio['quantity'];
                                        $cost = $portfolio['avg_price'] * $portfolio['quantity'];
                                        $gain_loss = $current_value - $cost;
                                        $gain_loss_percent = $cost > 0 ? ($gain_loss / $cost) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($portfolio['stock_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($portfolio['stock_name']); ?></td>
                                        <td><?php echo number_format($portfolio['quantity']); ?></td>
                                        <td>$<?php echo number_format($portfolio['avg_price'], 2); ?></td>
                                        <td>
                                            <div>
                                                $<?php echo number_format($portfolio['current_price'], 2); ?>
                                                <small class="<?php echo $portfolio['price_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    (<?php echo ($portfolio['price_change'] >= 0 ? '+' : '') . number_format($portfolio['price_change'], 2); ?>)
                                                </small>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($current_value, 2); ?></td>
                                        <td class="<?php echo $gain_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($gain_loss, 2); ?>
                                        </td>
                                        <td class="<?php echo $gain_loss_percent >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($gain_loss_percent, 2); ?>%
                                        </td>
                                        <td>
                                            <a href="stock_detail.php?code=<?php echo $portfolio['stock_code']; ?>" 
                                               class="btn btn-sm btn-outline-primary">詳情</a>
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
