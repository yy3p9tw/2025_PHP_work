<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 獲取股票代碼
$stock_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($stock_code)) {
    header('Location: stocks.php');
    exit();
}

// 獲取股票基本資料
$stock = $db->fetchOne('SELECT * FROM stocks WHERE code = ? AND status = ?', [$stock_code, 'active']);

if (empty($stock)) {
    header('Location: stocks.php');
    exit();
}

// 獲取股票價格歷史（最近30天）
$price_history = $db->fetchAll('
    SELECT * FROM stock_prices 
    WHERE stock_code = ? 
    ORDER BY date DESC 
    LIMIT 30
', [$stock_code]);

// 檢查是否在關注清單中
$is_watchlisted = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $watchlist_check = $db->fetchAll('
        SELECT id FROM watchlist 
        WHERE user_id = ? AND stock_code = ?
    ', [$user_id, $stock_code]);
    $is_watchlisted = !empty($watchlist_check);
    
    // 檢查是否持有此股票
    $portfolio_check = $db->fetchOne('
        SELECT * FROM portfolios 
        WHERE user_id = ? AND stock_code = ? AND quantity > 0
    ', [$user_id, $stock_code]);
    $is_owned = !empty($portfolio_check);
    $owned_quantity = $is_owned ? $portfolio_check['quantity'] : 0;
    $owned_avg_price = $is_owned ? $portfolio_check['avg_price'] : 0;
}

// 處理關注清單操作
if (isset($_POST['toggle_watchlist']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    if ($is_watchlisted) {
        // 移除關注
        $db->execute('DELETE FROM watchlist WHERE user_id = ? AND stock_code = ?', [$user_id, $stock_code]);
        $is_watchlisted = false;
    } else {
        // 加入關注
        $db->execute('INSERT INTO watchlist (user_id, stock_code) VALUES (?, ?)', [$user_id, $stock_code]);
        $is_watchlisted = true;
    }
    
    header('Location: stock_detail.php?code=' . $stock_code);
    exit();
}

$page_title = $stock['name'] . ' (' . $stock['code'] . ')';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- 股票基本資訊 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><?php echo htmlspecialchars($stock['name']); ?></h2>
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars($stock['code']); ?>
                            <?php if ($stock['industry']): ?>
                                · <?php echo htmlspecialchars($stock['industry']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <?php if (isLoggedIn()): ?>
                            <form method="post" class="d-inline">
                                <button type="submit" name="toggle_watchlist" 
                                        class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-star<?php echo $is_watchlisted ? '' : '-o'; ?>"></i>
                                    <?php echo $is_watchlisted ? '已關注' : '關注'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="mb-0">$<?php echo number_format($stock['current_price'], 2); ?></h3>
                                    <div class="<?php echo $stock['price_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($stock['price_change'] >= 0 ? '+' : '') . number_format($stock['price_change'], 2); ?>
                                        (<?php echo ($stock['change_percent'] >= 0 ? '+' : '') . number_format($stock['change_percent'], 2); ?>%)
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="row mb-2">
                                        <div class="col-6 text-muted">成交量</div>
                                        <div class="col-6"><?php echo number_format($stock['volume']); ?></div>
                                    </div>
                                    <?php if ($stock['market_cap'] > 0): ?>
                                    <div class="row mb-2">
                                        <div class="col-6 text-muted">市值</div>
                                        <div class="col-6">$<?php echo number_format($stock['market_cap'] / 1000000, 0); ?>M</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isLoggedIn() && $is_owned): ?>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">持有狀況</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">持有股數</small>
                                            <div><?php echo number_format($owned_quantity); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">平均成本</small>
                                            <div>$<?php echo number_format($owned_avg_price, 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <small class="text-muted">市值</small>
                                            <div>$<?php echo number_format($stock['current_price'] * $owned_quantity, 2); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">損益</small>
                                            <?php 
                                            $gain_loss = ($stock['current_price'] - $owned_avg_price) * $owned_quantity;
                                            ?>
                                            <div class="<?php echo $gain_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                $<?php echo number_format($gain_loss, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 價格走勢圖 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">價格走勢</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($price_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>日期</th>
                                        <th>開盤</th>
                                        <th>最高</th>
                                        <th>最低</th>
                                        <th>收盤</th>
                                        <th>成交量</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($price_history, 0, 10) as $price): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($price['date'])); ?></td>
                                        <td>$<?php echo number_format($price['open_price'], 2); ?></td>
                                        <td>$<?php echo number_format($price['high_price'], 2); ?></td>
                                        <td>$<?php echo number_format($price['low_price'], 2); ?></td>
                                        <td>$<?php echo number_format($price['close_price'], 2); ?></td>
                                        <td><?php echo number_format($price['volume']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">暫無歷史價格數據</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 操作按鈕 -->
            <?php if (isLoggedIn()): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">操作</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="transaction.php?action=buy&code=<?php echo $stock['code']; ?>" 
                               class="btn btn-success btn-lg w-100">
                                <i class="fas fa-plus"></i> 買入
                            </a>
                        </div>
                        <?php if ($is_owned): ?>
                        <div class="col-md-6">
                            <a href="transaction.php?action=sell&code=<?php echo $stock['code']; ?>" 
                               class="btn btn-danger btn-lg w-100">
                                <i class="fas fa-minus"></i> 賣出
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
