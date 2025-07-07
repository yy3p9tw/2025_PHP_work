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

// 執行測試功能
$test_action = $_GET['test'] ?? '';
$test_result = null;

if ($test_action) {
    switch ($test_action) {
        case 'database':
            $test_result = testDatabase($db);
            break;
        case 'auth':
            $test_result = testAuth($db);
            break;
        case 'data':
            $test_result = testData($db);
            break;
        case 'api':
            $test_result = testAPI();
            break;
        case 'performance':
            $test_result = testPerformance($db);
            break;
        case 'security':
            $test_result = testSecurity($db);
            break;
    }
}

// 測試函數
function testDatabase($db) {
    $results = [];
    
    // 測試基本連接
    try {
        $connection_test = $db->fetchOne('SELECT 1 as test');
        $results['connection'] = ['status' => 'PASS', 'message' => '資料庫連接正常'];
    } catch (Exception $e) {
        $results['connection'] = ['status' => 'FAIL', 'message' => '資料庫連接失敗: ' . $e->getMessage()];
    }
    
    // 測試資料表
    $tables = ['users', 'stocks', 'portfolios', 'transactions', 'watchlist', 'news', 'market_indices', 'settings'];
    foreach ($tables as $table) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
            $results["table_$table"] = ['status' => 'PASS', 'message' => "$table 表格正常 ({$result['count']} 筆記錄)"];
        } catch (Exception $e) {
            $results["table_$table"] = ['status' => 'FAIL', 'message' => "$table 表格錯誤: " . $e->getMessage()];
        }
    }
    
    return $results;
}

function testAuth($db) {
    $results = [];
    
    // 測試管理員帳戶
    try {
        $admin = $db->fetchOne('SELECT * FROM users WHERE username = ?', ['admin']);
        if ($admin) {
            $results['admin_account'] = ['status' => 'PASS', 'message' => '管理員帳戶存在'];
        } else {
            $results['admin_account'] = ['status' => 'FAIL', 'message' => '管理員帳戶不存在'];
        }
    } catch (Exception $e) {
        $results['admin_account'] = ['status' => 'FAIL', 'message' => '查詢管理員帳戶失敗: ' . $e->getMessage()];
    }
    
    // 測試 Session 功能
    $results['session'] = ['status' => session_status() === PHP_SESSION_ACTIVE ? 'PASS' : 'FAIL', 
                          'message' => 'Session 狀態: ' . (session_status() === PHP_SESSION_ACTIVE ? '啟用' : '未啟用')];
    
    // 測試登入狀態
    $results['login_status'] = ['status' => isLoggedIn() ? 'PASS' : 'FAIL', 
                               'message' => '登入狀態: ' . (isLoggedIn() ? '已登入' : '未登入')];
    
    return $results;
}

function testData($db) {
    $results = [];
    
    // 測試股票資料
    try {
        $stocks = $db->fetchAll('SELECT * FROM stocks WHERE status = ? LIMIT 5', ['active']);
        $results['stock_data'] = ['status' => !empty($stocks) ? 'PASS' : 'FAIL', 
                                 'message' => '股票資料: ' . count($stocks) . ' 筆有效記錄'];
    } catch (Exception $e) {
        $results['stock_data'] = ['status' => 'FAIL', 'message' => '股票資料錯誤: ' . $e->getMessage()];
    }
    
    // 測試新聞資料
    try {
        $news = $db->fetchAll('SELECT * FROM news WHERE status = ? LIMIT 5', ['active']);
        $results['news_data'] = ['status' => !empty($news) ? 'PASS' : 'FAIL', 
                                'message' => '新聞資料: ' . count($news) . ' 筆有效記錄'];
    } catch (Exception $e) {
        $results['news_data'] = ['status' => 'FAIL', 'message' => '新聞資料錯誤: ' . $e->getMessage()];
    }
    
    // 測試市場指數
    try {
        $indices = $db->fetchAll('SELECT * FROM market_indices WHERE status = ? LIMIT 5', ['active']);
        $results['market_indices'] = ['status' => !empty($indices) ? 'PASS' : 'FAIL', 
                                     'message' => '市場指數: ' . count($indices) . ' 筆有效記錄'];
    } catch (Exception $e) {
        $results['market_indices'] = ['status' => 'FAIL', 'message' => '市場指數錯誤: ' . $e->getMessage()];
    }
    
    // 測試交易資料
    try {
        $transactions = $db->fetchAll('SELECT * FROM transactions LIMIT 5');
        $results['transaction_data'] = ['status' => 'PASS', 'message' => '交易資料: ' . count($transactions) . ' 筆記錄'];
    } catch (Exception $e) {
        $results['transaction_data'] = ['status' => 'FAIL', 'message' => '交易資料錯誤: ' . $e->getMessage()];
    }
    
    return $results;
}

function testAPI() {
    $results = [];
    
    // 測試 API 端點
    $api_endpoints = [
        'stocks' => 'api/stocks.php',
        'market_indices' => 'api/market-indices.php',
        'news' => 'api/news.php'
    ];
    
    foreach ($api_endpoints as $name => $endpoint) {
        $url = "http://localhost:8080/$endpoint";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $results["api_$name"] = ['status' => 'PASS', 'message' => "$name API 回應正常"];
            } else {
                $results["api_$name"] = ['status' => 'FAIL', 'message' => "$name API 回應格式錯誤"];
            }
        } else {
            $results["api_$name"] = ['status' => 'FAIL', 'message' => "$name API 無法連接"];
        }
    }
    
    return $results;
}

function testPerformance($db) {
    $results = [];
    
    // 測試查詢效能
    $start = microtime(true);
    try {
        $db->fetchAll('SELECT * FROM stocks LIMIT 100');
        $end = microtime(true);
        $time = round(($end - $start) * 1000, 2);
        $results['query_performance'] = ['status' => $time < 100 ? 'PASS' : 'WARN', 
                                        'message' => "查詢 100 筆股票資料耗時: {$time}ms"];
    } catch (Exception $e) {
        $results['query_performance'] = ['status' => 'FAIL', 'message' => '查詢效能測試失敗: ' . $e->getMessage()];
    }
    
    // 測試記憶體使用
    $memory_usage = memory_get_usage(true);
    $memory_mb = round($memory_usage / 1024 / 1024, 2);
    $results['memory_usage'] = ['status' => $memory_mb < 50 ? 'PASS' : 'WARN', 
                               'message' => "記憶體使用量: {$memory_mb}MB"];
    
    return $results;
}

function testSecurity($db) {
    $results = [];
    
    // 測試 CSRF 保護
    $results['csrf_protection'] = ['status' => function_exists('generateCSRFToken') ? 'PASS' : 'FAIL', 
                                  'message' => 'CSRF 保護功能: ' . (function_exists('generateCSRFToken') ? '啟用' : '未啟用')];
    
    // 測試 SQL 注入保護
    try {
        $malicious_input = "'; DROP TABLE users; --";
        $result = $db->fetchOne('SELECT * FROM users WHERE username = ?', [$malicious_input]);
        $results['sql_injection'] = ['status' => 'PASS', 'message' => 'SQL 注入保護正常'];
    } catch (Exception $e) {
        $results['sql_injection'] = ['status' => 'FAIL', 'message' => 'SQL 注入測試失敗: ' . $e->getMessage()];
    }
    
    // 測試檔案權限
    $critical_files = ['config.php', 'includes/database.php'];
    $file_security = true;
    foreach ($critical_files as $file) {
        if (file_exists("../$file")) {
            $perms = fileperms("../$file");
            if ($perms & 0x0004) { // 檢查是否其他用戶可讀
                $file_security = false;
                break;
            }
        }
    }
    $results['file_security'] = ['status' => $file_security ? 'PASS' : 'WARN', 
                                'message' => '檔案權限檢查: ' . ($file_security ? '安全' : '需要檢查')];
    
    return $results;
}

$page_title = '系統測試';
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-flask me-2"></i>系統測試
                    <span class="badge bg-info">診斷工具</span>
                </h1>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                此頁面用於測試系統各項功能是否正常運作。請定期執行測試以確保系統穩定性。
            </div>

            <!-- 測試控制面板 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">測試控制面板</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="?test=database" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-database me-2"></i>資料庫測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="?test=auth" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-user-shield me-2"></i>認證測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="?test=data" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i>資料測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="?test=api" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-code me-2"></i>API 測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="?test=performance" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>效能測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="?test=security" class="btn btn-danger w-100 mb-2">
                                <i class="fas fa-shield-alt me-2"></i>安全測試
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 測試結果 -->
            <?php if ($test_result): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">測試結果: <?php echo ucfirst($test_action); ?></h5>
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
                                <?php foreach ($test_result as $test_name => $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($test_name); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $result['status'] === 'PASS' ? 'success' : 
                                                ($result['status'] === 'WARN' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo $result['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($result['message']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 系統資訊 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">系統資訊</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>PHP 版本:</strong></td>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>MySQL 版本:</strong></td>
                                    <td><?php 
                                        try {
                                            $version = $db->fetchOne('SELECT VERSION() as version');
                                            echo $version['version'];
                                        } catch (Exception $e) {
                                            echo '無法取得';
                                        }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td><strong>伺服器軟體:</strong></td>
                                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>時區:</strong></td>
                                    <td><?php echo date_default_timezone_get(); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>當前時間:</strong></td>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>記憶體限制:</strong></td>
                                    <td><?php echo ini_get('memory_limit'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>記憶體使用:</strong></td>
                                    <td><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?>MB</td>
                                </tr>
                                <tr>
                                    <td><strong>最大執行時間:</strong></td>
                                    <td><?php echo ini_get('max_execution_time'); ?>秒</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 檔案結構檢查 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">檔案結構檢查</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>核心檔案</h6>
                            <ul class="list-group">
                                <?php 
                                $core_files = ['../config.php', '../includes/database.php', '../includes/auth.php'];
                                foreach ($core_files as $file):
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo basename($file); ?>
                                    <span class="badge bg-<?php echo file_exists($file) ? 'success' : 'danger'; ?>">
                                        <?php echo file_exists($file) ? '存在' : '缺失'; ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>資源檔案</h6>
                            <ul class="list-group">
                                <?php 
                                $asset_files = ['../assets/css/style.css', '../assets/js/main.js'];
                                foreach ($asset_files as $file):
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo basename($file); ?>
                                    <span class="badge bg-<?php echo file_exists($file) ? 'success' : 'danger'; ?>">
                                        <?php echo file_exists($file) ? '存在' : '缺失'; ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 測試帳戶資訊 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">測試帳戶資訊</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">管理員帳戶</h6>
                                    <p class="card-text">
                                        <strong>帳號:</strong> admin<br>
                                        <strong>密碼:</strong> admin123<br>
                                        <strong>權限:</strong> 管理員
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">測試用戶</h6>
                                    <p class="card-text">
                                        <strong>帳號:</strong> demo<br>
                                        <strong>密碼:</strong> demo123<br>
                                        <strong>權限:</strong> 一般用戶
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">測試用戶 2</h6>
                                    <p class="card-text">
                                        <strong>帳號:</strong> test<br>
                                        <strong>密碼:</strong> test123<br>
                                        <strong>權限:</strong> 一般用戶
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 常見問題與解決方案 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">常見問題與解決方案</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="troubleshootingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    資料庫連接失敗
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <strong>可能原因:</strong>
                                    <ul>
                                        <li>MySQL 服務未啟動</li>
                                        <li>config.php 設定錯誤</li>
                                        <li>資料庫不存在</li>
                                        <li>用戶權限不足</li>
                                    </ul>
                                    <strong>解決方案:</strong>
                                    <ol>
                                        <li>確認 MySQL 服務已啟動</li>
                                        <li>檢查 config.php 中的資料庫設定</li>
                                        <li>確認資料庫 'stock_portfolio' 已創建</li>
                                        <li>匯入 database/stock_portfolio.sql 檔案</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    API 測試失敗
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <strong>可能原因:</strong>
                                    <ul>
                                        <li>Web 伺服器未啟動</li>
                                        <li>API 檔案不存在</li>
                                        <li>權限問題</li>
                                    </ul>
                                    <strong>解決方案:</strong>
                                    <ol>
                                        <li>確認 PHP 內建伺服器已啟動 (php -S localhost:8080)</li>
                                        <li>檢查 api/ 目錄下的檔案是否存在</li>
                                        <li>確認檔案權限設定正確</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    效能問題
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <strong>可能原因:</strong>
                                    <ul>
                                        <li>資料庫索引缺失</li>
                                        <li>大量資料查詢</li>
                                        <li>記憶體不足</li>
                                    </ul>
                                    <strong>解決方案:</strong>
                                    <ol>
                                        <li>為常用查詢欄位建立索引</li>
                                        <li>使用分頁限制查詢結果</li>
                                        <li>增加 PHP 記憶體限制</li>
                                        <li>使用快取機制</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 快速連結 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">快速連結</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="../index.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-home me-2"></i>返回首頁
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="../test.php" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-vial me-2"></i>前台測試
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="index.php" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>管理首頁
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="users.php" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-users me-2"></i>用戶管理
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="stocks.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i>股票管理
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="news.php" class="btn btn-outline-dark w-100 mb-2">
                                <i class="fas fa-newspaper me-2"></i>新聞管理
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 自動重新整理功能
let autoRefresh = false;
let refreshInterval;

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        refreshInterval = setInterval(() => {
            if (window.location.search.includes('test=')) {
                window.location.reload();
            }
        }, 30000); // 每30秒刷新一次
        document.getElementById('autoRefreshBtn').textContent = '停止自動重新整理';
    } else {
        clearInterval(refreshInterval);
        document.getElementById('autoRefreshBtn').textContent = '開始自動重新整理';
    }
}

// 新增自動重新整理按鈕（如果需要）
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.search.includes('test=')) {
        const controlPanel = document.querySelector('.card-body');
        if (controlPanel) {
            const refreshBtn = document.createElement('button');
            refreshBtn.id = 'autoRefreshBtn';
            refreshBtn.className = 'btn btn-outline-info btn-sm mt-2';
            refreshBtn.textContent = '開始自動重新整理';
            refreshBtn.onclick = toggleAutoRefresh;
            controlPanel.appendChild(refreshBtn);
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
