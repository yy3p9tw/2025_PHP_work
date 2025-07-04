<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// 測試結果
$test_results = [];

// 執行測試
if (isset($_POST['run_tests'])) {
    $test_results = runAllTests($conn);
}

function runAllTests($conn) {
    $results = [];
    
    // 1. 資料庫連接測試
    $results['database'] = testDatabase($conn);
    
    // 2. API 端點測試
    $results['api'] = testAPIEndpoints();
    
    // 3. 用戶功能測試
    $results['user_functions'] = testUserFunctions($conn);
    
    // 4. 股票功能測試
    $results['stock_functions'] = testStockFunctions($conn);
    
    // 5. 投資組合功能測試
    $results['portfolio_functions'] = testPortfolioFunctions($conn);
    
    // 6. 交易功能測試
    $results['transaction_functions'] = testTransactionFunctions($conn);
    
    return $results;
}

function testDatabase($conn) {
    $results = [];
    
    try {
        // 測試資料庫連接
        $conn->query("SELECT 1");
        $results['connection'] = ['status' => 'success', 'message' => '資料庫連接正常'];
        
        // 測試各個表是否存在
        $tables = ['users', 'stocks', 'portfolios', 'transactions', 'news', 'market_indices', 'settings'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                $results[$table] = ['status' => 'success', 'message' => "表 $table 存在，有 $count 筆記錄"];
            } catch (Exception $e) {
                $results[$table] = ['status' => 'error', 'message' => "表 $table 不存在或無法訪問"];
            }
        }
        
    } catch (Exception $e) {
        $results['connection'] = ['status' => 'error', 'message' => '資料庫連接失敗：' . $e->getMessage()];
    }
    
    return $results;
}

function testAPIEndpoints() {
    $results = [];
    $base_url = 'http://localhost:8000/api/';
    
    $endpoints = [
        'stocks.php' => 'GET',
        'portfolio.php' => 'GET',
        'market-indices.php' => 'GET',
        'news.php' => 'GET',
        'watchlist.php' => 'GET'
    ];
    
    foreach ($endpoints as $endpoint => $method) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $results[$endpoint] = ['status' => 'success', 'message' => 'API 回應正常'];
                } else {
                    $results[$endpoint] = ['status' => 'warning', 'message' => 'API 回應但格式錯誤'];
                }
            } else {
                $results[$endpoint] = ['status' => 'error', 'message' => "HTTP 錯誤碼：$http_code"];
            }
        } catch (Exception $e) {
            $results[$endpoint] = ['status' => 'error', 'message' => 'API 測試失敗：' . $e->getMessage()];
        }
    }
    
    return $results;
}

function testUserFunctions($conn) {
    $results = [];
    
    try {
        // 測試用戶查詢
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
        $stmt->execute();
        $active_users = $stmt->fetchColumn();
        $results['active_users'] = ['status' => 'success', 'message' => "活躍用戶數：$active_users"];
        
        // 測試管理員用戶
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();
        $results['admin_users'] = ['status' => 'success', 'message' => "管理員用戶數：$admin_count"];
        
    } catch (Exception $e) {
        $results['user_query'] = ['status' => 'error', 'message' => '用戶功能測試失敗：' . $e->getMessage()];
    }
    
    return $results;
}

function testStockFunctions($conn) {
    $results = [];
    
    try {
        // 測試股票查詢
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stocks WHERE status = 'active'");
        $stmt->execute();
        $active_stocks = $stmt->fetchColumn();
        $results['active_stocks'] = ['status' => 'success', 'message' => "啟用股票數：$active_stocks"];
        
        // 測試股票價格更新
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stocks WHERE current_price > 0");
        $stmt->execute();
        $priced_stocks = $stmt->fetchColumn();
        $results['priced_stocks'] = ['status' => 'success', 'message' => "有價格的股票數：$priced_stocks"];
        
    } catch (Exception $e) {
        $results['stock_query'] = ['status' => 'error', 'message' => '股票功能測試失敗：' . $e->getMessage()];
    }
    
    return $results;
}

function testPortfolioFunctions($conn) {
    $results = [];
    
    try {
        // 測試投資組合查詢
        $stmt = $conn->prepare("SELECT COUNT(*) FROM portfolios");
        $stmt->execute();
        $portfolio_count = $stmt->fetchColumn();
        $results['portfolio_count'] = ['status' => 'success', 'message' => "投資組合記錄數：$portfolio_count"];
        
        // 測試投資組合與股票的關聯
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM portfolios p 
            LEFT JOIN stocks s ON p.stock_code = s.code 
            WHERE s.id IS NOT NULL
        ");
        $stmt->execute();
        $linked_portfolios = $stmt->fetchColumn();
        $results['linked_portfolios'] = ['status' => 'success', 'message' => "關聯到股票的投資組合：$linked_portfolios"];
        
    } catch (Exception $e) {
        $results['portfolio_query'] = ['status' => 'error', 'message' => '投資組合功能測試失敗：' . $e->getMessage()];
    }
    
    return $results;
}

function testTransactionFunctions($conn) {
    $results = [];
    
    try {
        // 測試交易記錄查詢
        $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions");
        $stmt->execute();
        $transaction_count = $stmt->fetchColumn();
        $results['transaction_count'] = ['status' => 'success', 'message' => "交易記錄數：$transaction_count"];
        
        // 測試交易統計
        $stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN type = 'buy' THEN total_amount ELSE 0 END) as buy_total,
                SUM(CASE WHEN type = 'sell' THEN total_amount ELSE 0 END) as sell_total
            FROM transactions
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $results['transaction_stats'] = ['status' => 'success', 'message' => "買入總額：$" . number_format($stats['buy_total'], 2) . "，賣出總額：$" . number_format($stats['sell_total'], 2)];
        
    } catch (Exception $e) {
        $results['transaction_query'] = ['status' => 'error', 'message' => '交易功能測試失敗：' . $e->getMessage()];
    }
    
    return $results;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統測試 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line"></i> 股票投資組合
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">首頁</a>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="test.php">系統測試</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php">
                            <i class="fas fa-cog"></i> 管理後台
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-vial"></i> 系統綜合測試</h1>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="run_tests" class="btn btn-primary">
                            <i class="fas fa-play"></i> 執行全部測試
                        </button>
                    </form>
                </div>

                <?php if (!empty($test_results)): ?>
                <!-- 測試結果總覽 -->
                <div class="row mb-4">
                    <?php 
                    $total_tests = 0;
                    $passed_tests = 0;
                    $failed_tests = 0;
                    $warning_tests = 0;
                    
                    foreach ($test_results as $category => $tests) {
                        foreach ($tests as $test) {
                            $total_tests++;
                            if ($test['status'] == 'success') $passed_tests++;
                            elseif ($test['status'] == 'error') $failed_tests++;
                            elseif ($test['status'] == 'warning') $warning_tests++;
                        }
                    }
                    ?>
                    
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $total_tests; ?></h3>
                                <p>總測試數</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $passed_tests; ?></h3>
                                <p>通過測試</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $warning_tests; ?></h3>
                                <p>警告測試</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $failed_tests; ?></h3>
                                <p>失敗測試</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 詳細測試結果 -->
                <?php foreach ($test_results as $category => $tests): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> <?php echo ucfirst($category); ?> 測試結果</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>測試項目</th>
                                        <th>狀態</th>
                                        <th>結果</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $test_name => $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($test_name); ?></td>
                                        <td>
                                            <?php if ($result['status'] == 'success'): ?>
                                                <span class="badge bg-success">通過</span>
                                            <?php elseif ($result['status'] == 'warning'): ?>
                                                <span class="badge bg-warning">警告</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">失敗</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['message']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php else: ?>
                <!-- 測試說明 -->
                <div class="card">
                    <div class="card-body">
                        <h5>系統測試說明</h5>
                        <p>這個測試工具會檢查系統的各個重要功能：</p>
                        <ul>
                            <li><strong>資料庫測試</strong>：檢查資料庫連接和表結構</li>
                            <li><strong>API 測試</strong>：測試各個 API 端點是否正常運作</li>
                            <li><strong>用戶功能測試</strong>：驗證用戶相關功能</li>
                            <li><strong>股票功能測試</strong>：檢查股票資料和查詢功能</li>
                            <li><strong>投資組合功能測試</strong>：測試投資組合計算和顯示</li>
                            <li><strong>交易功能測試</strong>：驗證交易記錄和統計</li>
                        </ul>
                        <p>點擊「執行全部測試」按鈕開始測試。</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
