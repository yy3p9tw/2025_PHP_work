<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 搜索功能
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'code';

$where_conditions = ['status = ?'];
$params = ['active'];

if (!empty($search)) {
    $where_conditions[] = '(code LIKE ? OR name LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($category)) {
    $where_conditions[] = 'industry = ?';
    $params[] = $category;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

$order_clause = 'ORDER BY ';
switch ($order) {
    case 'name':
        $order_clause .= 'name ASC';
        break;
    case 'price':
        $order_clause .= 'current_price DESC';
        break;
    case 'change':
        $order_clause .= 'change_percent DESC';
        break;
    default:
        $order_clause .= 'code ASC';
}

$stocks = $db->fetchAll("
    SELECT * FROM stocks 
    $where_clause 
    $order_clause
", $params);

// 獲取所有產業類別
$industries = $db->fetchAll("SELECT DISTINCT industry FROM stocks WHERE industry IS NOT NULL AND status = 'active' ORDER BY industry");

$page_title = '股票列表';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">股票列表</h1>
            
            <!-- 搜索和篩選 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="搜索股票代碼或名稱" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">所有產業</option>
                                <?php foreach ($industries as $industry): ?>
                                    <option value="<?php echo htmlspecialchars($industry['industry']); ?>" 
                                            <?php echo $category === $industry['industry'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($industry['industry']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="order">
                                <option value="code" <?php echo $order === 'code' ? 'selected' : ''; ?>>按代碼排序</option>
                                <option value="name" <?php echo $order === 'name' ? 'selected' : ''; ?>>按名稱排序</option>
                                <option value="price" <?php echo $order === 'price' ? 'selected' : ''; ?>>按價格排序</option>
                                <option value="change" <?php echo $order === 'change' ? 'selected' : ''; ?>>按漲跌幅排序</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">搜索</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 股票列表 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">共 <?php echo count($stocks); ?> 隻股票</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stocks)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">找不到符合條件的股票</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>代碼</th>
                                        <th>名稱</th>
                                        <th>產業</th>
                                        <th>現價</th>
                                        <th>漲跌</th>
                                        <th>漲跌幅</th>
                                        <th>成交量</th>
                                        <th>市值</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stocks as $stock): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stock['code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($stock['name']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($stock['industry']); ?></span>
                                        </td>
                                        <td>$<?php echo number_format($stock['current_price'], 2); ?></td>
                                        <td class="<?php echo $stock['price_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['price_change'] >= 0 ? '+' : '') . number_format($stock['price_change'], 2); ?>
                                        </td>
                                        <td class="<?php echo $stock['change_percent'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($stock['change_percent'] >= 0 ? '+' : '') . number_format($stock['change_percent'], 2); ?>%
                                        </td>
                                        <td><?php echo number_format($stock['volume']); ?></td>
                                        <td>
                                            <?php if ($stock['market_cap'] > 0): ?>
                                                $<?php echo number_format($stock['market_cap'] / 1000000, 0); ?>M
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="stock_detail.php?code=<?php echo $stock['code']; ?>" 
                                               class="btn btn-sm btn-outline-primary">詳情</a>
                                            <?php if (isLoggedIn()): ?>
                                                <a href="add_to_watchlist.php?code=<?php echo $stock['code']; ?>" 
                                                   class="btn btn-sm btn-outline-success">關注</a>
                                            <?php endif; ?>
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
