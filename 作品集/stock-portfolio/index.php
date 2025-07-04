<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// 獲取用戶投資組合總覽
$stmt = $conn->prepare("
    SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent
    FROM portfolios p
    LEFT JOIN stocks s ON p.stock_code = s.code
    WHERE p.user_id = ?
");
$stmt->execute([$user_id]);
$portfolios = $stmt->fetchAll();

// 計算總投資金額和總市值
$total_investment = 0;
$total_market_value = 0;
foreach ($portfolios as $portfolio) {
    $total_investment += $portfolio['quantity'] * $portfolio['avg_price'];
    // 確保 current_price 存在且不為 null
    $current_price = isset($portfolio['current_price']) ? $portfolio['current_price'] : 0;
    $total_market_value += $portfolio['quantity'] * $current_price;
}

$profit_loss = $total_market_value - $total_investment;
$profit_loss_percentage = $total_investment > 0 ? ($profit_loss / $total_investment) * 100 : 0;

// 獲取最新股市新聞
$stmt = $conn->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$news = $stmt->fetchAll();

// 獲取市場指數
$market_indices = getMarketIndices($conn);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-graph-up"></i> 股票投資組合
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">首頁</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="portfolio.php">投資組合</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stocks.php">股票查詢</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">財經新聞</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">交易記錄</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">個人資料</a></li>
                            <li><a class="dropdown-item" href="settings.php">設定</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/index.php">管理後台</a></li>
                            <li><a class="dropdown-item" href="test.php">系統測試</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- 市場指數 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up-arrow"></i> 市場指數</h5>
                        <div class="row" id="market-indices">
                            <?php foreach ($market_indices as $index): ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card <?php echo $index['change'] >= 0 ? 'border-success' : 'border-danger'; ?>">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?php echo $index['name']; ?></h6>
                                        <h5 class="<?php echo $index['change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($index['value'], 2); ?>
                                        </h5>
                                        <small class="<?php echo $index['change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $index['change'] >= 0 ? '+' : ''; ?><?php echo number_format($index['change'], 2); ?>
                                            (<?php echo $index['change'] >= 0 ? '+' : ''; ?><?php echo number_format($index['change_percent'], 2); ?>%)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 投資組合總覽 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-wallet2"></i> 總投資金額</h5>
                        <h3>$<?php echo number_format($total_investment, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-currency-exchange"></i> 總市值</h5>
                        <h3>$<?php echo number_format($total_market_value, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white <?php echo $profit_loss >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up"></i> 損益</h5>
                        <h3><?php echo $profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($profit_loss, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white <?php echo $profit_loss_percentage >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-percent"></i> 損益率</h5>
                        <h3><?php echo $profit_loss_percentage >= 0 ? '+' : ''; ?><?php echo number_format($profit_loss_percentage, 2); ?>%</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 投資組合 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-briefcase"></i> 我的投資組合</h5>
                        <a href="portfolio.php" class="btn btn-sm btn-primary">查看詳細</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($portfolios)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">尚未建立投資組合</p>
                            <a href="stocks.php" class="btn btn-primary">開始投資</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>持股數</th>
                                        <th>成本價</th>
                                        <th>現價</th>
                                        <th>損益</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($portfolios, 0, 5) as $portfolio): ?>
                                    <?php
                                    $investment = $portfolio['quantity'] * $portfolio['avg_price'];
                                    $current_price = isset($portfolio['current_price']) ? $portfolio['current_price'] : 0;
                                    $stock_name = isset($portfolio['stock_name']) ? $portfolio['stock_name'] : $portfolio['stock_code'];
                                    $market_value = $portfolio['quantity'] * $current_price;
                                    $profit_loss = $market_value - $investment;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($portfolio['stock_code']); ?></td>
                                        <td><?php echo htmlspecialchars($stock_name); ?></td>
                                        <td><?php echo number_format($portfolio['quantity']); ?></td>
                                        <td>$<?php echo number_format($portfolio['avg_price'], 2); ?></td>
                                        <td>$<?php echo number_format($current_price, 2); ?></td>
                                        <td class="<?php echo $profit_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($profit_loss, 2); ?>
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

            <!-- 財經新聞 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-newspaper"></i> 財經新聞</h5>
                        <a href="news.php" class="btn btn-sm btn-outline-primary">更多新聞</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($news)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-newspaper display-4 text-muted"></i>
                            <p class="text-muted mt-2">暫無新聞</p>
                        </div>
                        <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($news as $article): ?>
                            <div class="list-group-item px-0 py-2">
                                <h6 class="mb-1">
                                    <a href="<?php echo htmlspecialchars($article['url']); ?>" target="_blank" class="text-decoration-none">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($article['created_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // 自動更新市場指數
        setInterval(function() {
            $.get('api/market-indices.php', function(data) {
                if (data.success) {
                    updateMarketIndices(data.indices);
                }
            });
        }, 30000); // 每30秒更新一次
    </script>
</body>
</html>
