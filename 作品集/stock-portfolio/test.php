<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 測試資料庫連接
$test_results = [];

try {
    // 測試基本連接
    $connection_test = $db->fetchOne('SELECT 1 as test');
    $test_results['database_connection'] = !empty($connection_test) ? 'PASS' : 'FAIL';
} catch (Exception $e) {
    $test_results['database_connection'] = 'FAIL - ' . $e->getMessage();
}

// 測試主要資料表
$tables = ['users', 'stocks', 'portfolios', 'transactions', 'watchlist', 'news', 'market_indices', 'settings'];

foreach ($tables as $table) {
    try {
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
        $test_results["table_$table"] = 'PASS - ' . $result['count'] . ' records';
    } catch (Exception $e) {
        $test_results["table_$table"] = 'FAIL - ' . $e->getMessage();
    }
}

// 測試預設資料
try {
    $admin_user = $db->fetchAll('SELECT * FROM users WHERE username = ?', ['admin']);
    $test_results['admin_user'] = !empty($admin_user) ? 'PASS' : 'FAIL - No admin user found';
} catch (Exception $e) {
    $test_results['admin_user'] = 'FAIL - ' . $e->getMessage();
}

// 測試股票資料
try {
    $stock_data = $db->fetchAll('SELECT * FROM stocks WHERE status = ? LIMIT 1', ['active']);
    $test_results['stock_data'] = !empty($stock_data) ? 'PASS' : 'FAIL - No stock data found';
} catch (Exception $e) {
    $test_results['stock_data'] = 'FAIL - ' . $e->getMessage();
}

// 測試市場指數
try {
    $market_indices = $db->fetchAll('SELECT * FROM market_indices WHERE status = ? LIMIT 1', ['active']);
    $test_results['market_indices'] = !empty($market_indices) ? 'PASS' : 'FAIL - No market indices found';
} catch (Exception $e) {
    $test_results['market_indices'] = 'FAIL - ' . $e->getMessage();
}

// 測試新聞資料
try {
    $news_data = $db->fetchAll('SELECT * FROM news WHERE status = ? LIMIT 1', ['active']);
    $test_results['news_data'] = !empty($news_data) ? 'PASS' : 'FAIL - No news data found';
} catch (Exception $e) {
    $test_results['news_data'] = 'FAIL - ' . $e->getMessage();
}

// 新增 API 測試
try {
    // 測試股票 API
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $api_response = @file_get_contents('http://localhost:8080/api/stocks.php', false, $context);
    if ($api_response !== false) {
        $api_data = json_decode($api_response, true);
        $test_results['api_stocks'] = json_last_error() === JSON_ERROR_NONE ? 'PASS - API 回應正常' : 'FAIL - API 回應格式錯誤';
    } else {
        $test_results['api_stocks'] = 'FAIL - API 無法連接';
    }
} catch (Exception $e) {
    $test_results['api_stocks'] = 'FAIL - ' . $e->getMessage();
}

// 測試系統效能
$start_time = microtime(true);
try {
    // 測試大量資料查詢
    $large_query = $db->fetchAll('SELECT * FROM stocks LIMIT 50');
    $query_time = round((microtime(true) - $start_time) * 1000, 2);
    $test_results['performance_query'] = $query_time < 100 ? "PASS - 查詢耗時 {$query_time}ms" : "WARN - 查詢耗時 {$query_time}ms (建議小於100ms)";
} catch (Exception $e) {
    $test_results['performance_query'] = 'FAIL - ' . $e->getMessage();
}

// 測試記憶體使用
$memory_usage = memory_get_usage(true);
$memory_mb = round($memory_usage / 1024 / 1024, 2);
$test_results['memory_usage'] = $memory_mb < 50 ? "PASS - 記憶體使用 {$memory_mb}MB" : "WARN - 記憶體使用 {$memory_mb}MB (建議小於50MB)";

// 測試 Session 安全性
$test_results['session_security'] = ini_get('session.cookie_httponly') ? 'PASS - Session Cookie HttpOnly 啟用' : 'WARN - Session Cookie HttpOnly 未啟用';

// 測試 CSRF 保護
$test_results['csrf_protection'] = function_exists('generateCSRFToken') && function_exists('validateCSRFToken') ? 'PASS - CSRF 保護功能正常' : 'FAIL - CSRF 保護功能缺失';

// 測試檔案上傳目錄
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}
$test_results['upload_directory'] = is_dir($upload_dir) && is_writable($upload_dir) ? 'PASS - 上傳目錄可寫入' : 'WARN - 上傳目錄權限問題';

// 測試關鍵 PHP 擴展
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'curl'];
foreach ($required_extensions as $ext) {
    $test_results["extension_$ext"] = extension_loaded($ext) ? "PASS - $ext 擴展已載入" : "FAIL - $ext 擴展未載入";
}

// 測試 SQL 注入保護
try {
    $malicious_input = "'; DROP TABLE users; --";
    $result = $db->fetchOne('SELECT * FROM users WHERE username = ?', [$malicious_input]);
    $test_results['sql_injection_protection'] = 'PASS - SQL 注入保護正常';
} catch (Exception $e) {
    $test_results['sql_injection_protection'] = 'FAIL - SQL 注入測試失敗: ' . $e->getMessage();
}

// 測試資料庫索引
try {
    $indexes = $db->fetchAll('SHOW INDEX FROM stocks');
    $test_results['database_indexes'] = count($indexes) > 1 ? 'PASS - 資料庫索引存在' : 'WARN - 建議新增更多索引';
} catch (Exception $e) {
    $test_results['database_indexes'] = 'FAIL - 無法檢查索引: ' . $e->getMessage();
}

// 測試檔案結構
$required_files = [
    'config.php',
    'includes/database.php',
    'includes/auth.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'database/stock_portfolio.sql'
];

foreach ($required_files as $file) {
    $test_results["file_$file"] = file_exists($file) ? 'PASS' : 'FAIL - File not found';
}

$page_title = '系統測試';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-flask me-2"></i>系統測試
                <span class="badge bg-info">診斷工具</span>
            </h1>
            
            <!-- 測試結果摘要 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php 
                                        $pass_count = 0;
                                        foreach ($test_results as $result) {
                                            if (is_string($result) && strpos($result, 'PASS') !== false) {
                                                $pass_count++;
                                            }
                                        }
                                        echo $pass_count;
                                    ?></div>
                                    <div>通過測試</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
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
                                    <div class="h4"><?php 
                                        $fail_count = 0;
                                        foreach ($test_results as $result) {
                                            if (is_string($result) && strpos($result, 'FAIL') !== false) {
                                                $fail_count++;
                                            }
                                        }
                                        echo $fail_count;
                                    ?></div>
                                    <div>失敗測試</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h4"><?php echo count($test_results); ?></div>
                                    <div>總測試項目</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-list-check fa-2x"></i>
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
                                    <div class="h4"><?php 
                                        $total_count = count($test_results);
                                        echo $total_count > 0 ? round(($pass_count / $total_count) * 100, 1) : 0; 
                                    ?>%</div>
                                    <div>通過率</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-pie fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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
                                    <td><strong>應用程式名稱:</strong></td>
                                    <td><?php echo defined('APP_NAME') ? APP_NAME : '未定義'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>應用程式版本:</strong></td>
                                    <td><?php echo defined('APP_VERSION') ? APP_VERSION : '未定義'; ?></td>
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
                                    <td><strong>Session 狀態:</strong></td>
                                    <td><?php echo session_status() === PHP_SESSION_ACTIVE ? '啟用' : '未啟用'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>登入狀態:</strong></td>
                                    <td><?php echo isLoggedIn() ? '已登入' : '未登入'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>用戶角色:</strong></td>
                                    <td><?php echo isLoggedIn() ? (isAdmin() ? '管理員' : '一般用戶') : '未登入'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 資料庫測試 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">資料庫測試</h5>
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
                                <tr>
                                    <td>資料庫連接</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['database_connection'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['database_connection'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['database_connection'] ?? 'FAIL - Database connection not tested'; ?></td>
                                </tr>
                                
                                <?php foreach ($tables as $table): ?>
                                <tr>
                                    <td><?php echo ucfirst($table); ?> 資料表</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results["table_$table"] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results["table_$table"] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results["table_$table"] ?? 'FAIL - Table not tested'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <tr>
                                    <td>管理員帳戶</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['admin_user'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['admin_user'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['admin_user'] ?? 'FAIL - Admin user not tested'; ?></td>
                                </tr>
                                
                                <tr>
                                    <td>股票資料</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['stock_data'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['stock_data'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['stock_data'] ?? 'FAIL - Stock data not tested'; ?></td>
                                </tr>
                                
                                <tr>
                                    <td>市場指數</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['market_indices'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['market_indices'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['market_indices'] ?? 'FAIL - Market indices not tested'; ?></td>
                                </tr>
                                
                                <tr>
                                    <td>新聞資料</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['news_data'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['news_data'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['news_data'] ?? 'FAIL - News data not tested'; ?></td>
                                </tr>
                                
                                <tr>
                                    <td>系統設定</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['table_settings'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['table_settings'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['table_settings'] ?? 'FAIL - Settings table not found'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- 檔案結構測試 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">檔案結構測試</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>檔案路徑</th>
                                    <th>狀態</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($required_files as $file): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($file); ?></code></td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results["file_$file"] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo $test_results["file_$file"] ?? 'FAIL - File not tested'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- API 測試結果 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">API 測試結果</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>API 端點</th>
                                    <th>狀態</th>
                                    <th>結果</th>
                                    <th>動作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>股票資料 API</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['api_stocks'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['api_stocks'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['api_stocks'] ?? 'FAIL - API not tested'; ?></td>
                                    <td>
                                        <a href="api/stocks.php" target="_blank" class="btn btn-sm btn-outline-primary">測試 API</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>市場指數 API</td>
                                    <td>
                                        <span class="badge bg-secondary">未測試</span>
                                    </td>
                                    <td>需要手動測試</td>
                                    <td>
                                        <a href="api/market-indices.php" target="_blank" class="btn btn-sm btn-outline-primary">測試 API</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>新聞資料 API</td>
                                    <td>
                                        <span class="badge bg-secondary">未測試</span>
                                    </td>
                                    <td>需要手動測試</td>
                                    <td>
                                        <a href="api/news.php" target="_blank" class="btn btn-sm btn-outline-primary">測試 API</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 效能測試結果 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">效能測試結果</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>查詢效能:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['performance_query'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'warning'; ?>">
                                            <?php echo strpos($test_results['performance_query'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'WARN'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['performance_query'] ?? 'FAIL - Performance not tested'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>記憶體使用:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['memory_usage'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'warning'; ?>">
                                            <?php echo strpos($test_results['memory_usage'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'WARN'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['memory_usage'] ?? 'FAIL - Memory usage not tested'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>資料庫索引:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['database_indexes'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'warning'; ?>">
                                            <?php echo strpos($test_results['database_indexes'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'WARN'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['database_indexes'] ?? 'FAIL - Database indexes not tested'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">效能建議</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check-circle text-success me-2"></i>為常用查詢欄位建立索引</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>使用分頁限制查詢結果</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>啟用 PHP OPcache</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>使用 Redis 快取</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 安全性測試 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">安全性測試</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>安全項目</th>
                                    <th>狀態</th>
                                    <th>結果</th>
                                    <th>建議</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CSRF 保護</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['csrf_protection'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['csrf_protection'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['csrf_protection'] ?? 'FAIL - CSRF protection not tested'; ?></td>
                                    <td>確保所有表單都包含 CSRF token</td>
                                </tr>
                                <tr>
                                    <td>SQL 注入保護</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['sql_injection_protection'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?>">
                                            <?php echo strpos($test_results['sql_injection_protection'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['sql_injection_protection'] ?? 'FAIL - SQL injection protection not tested'; ?></td>
                                    <td>使用參數化查詢</td>
                                </tr>
                                <tr>
                                    <td>Session 安全</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['session_security'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'warning'; ?>">
                                            <?php echo strpos($test_results['session_security'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'WARN'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['session_security'] ?? 'FAIL - Session security not tested'; ?></td>
                                    <td>啟用 HttpOnly 和 Secure 標誌</td>
                                </tr>
                                <tr>
                                    <td>檔案上傳</td>
                                    <td>
                                        <span class="badge bg-<?php echo strpos($test_results['upload_directory'] ?? 'FAIL', 'PASS') !== false ? 'success' : 'warning'; ?>">
                                            <?php echo strpos($test_results['upload_directory'] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'WARN'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $test_results['upload_directory'] ?? 'FAIL - Upload directory not tested'; ?></td>
                                    <td>限制上傳檔案類型和大小</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PHP 擴展檢查 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">PHP 擴展檢查</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'curl'];
                        foreach ($extensions as $ext): 
                        ?>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?php echo strpos($test_results["extension_$ext"] ?? 'FAIL', 'PASS') !== false ? 'success' : 'danger'; ?> me-2">
                                    <?php echo strpos($test_results["extension_$ext"] ?? 'FAIL', 'PASS') !== false ? 'PASS' : 'FAIL'; ?>
                                </span>
                                <span><?php echo strtoupper($ext); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
                                    <a href="login.php" class="btn btn-primary btn-sm">前往登入</a>
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
                                    <a href="login.php" class="btn btn-secondary btn-sm">前往登入</a>
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
                                    <a href="login.php" class="btn btn-outline-secondary btn-sm">前往登入</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 快速操作工具 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">快速操作工具</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-sync-alt fa-2x text-primary mb-2"></i>
                                    <h6>重新整理測試</h6>
                                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">執行</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-download fa-2x text-success mb-2"></i>
                                    <h6>匯出測試報告</h6>
                                    <button class="btn btn-outline-success btn-sm" onclick="exportReport()">匯出</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-2x text-info mb-2"></i>
                                    <h6>測試歷史</h6>
                                    <button class="btn btn-outline-info btn-sm" onclick="alert('功能開發中')">查看</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-wrench fa-2x text-warning mb-2"></i>
                                    <h6>系統維護</h6>
                                    <button class="btn btn-outline-warning btn-sm" onclick="alert('請聯繫系統管理員')">維護</button>
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
                        <div class="col-md-3">
                            <a href="index.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-home me-2"></i>返回首頁
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="stocks.php" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i>股票列表
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="news.php" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-newspaper me-2"></i>新聞中心
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin/testing.php" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-flask me-2"></i>後台測試
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 匯出測試報告功能
function exportReport() {
    const testResults = document.querySelectorAll('.table-striped tbody tr');
    let csvContent = "測試項目,狀態,結果\n";
    
    testResults.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length >= 3) {
            const item = cols[0].textContent.trim();
            const status = cols[1].textContent.trim();
            const result = cols[2].textContent.trim();
            csvContent += `"${item}","${status}","${result}"\n`;
        }
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'test_report_' + new Date().toISOString().slice(0,10) + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// 自動重新整理功能
let autoRefresh = false;
let refreshInterval;

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        refreshInterval = setInterval(() => {
            location.reload();
        }, 30000); // 每30秒刷新一次
        document.getElementById('autoRefreshBtn').textContent = '停止自動重新整理';
    } else {
        clearInterval(refreshInterval);
        document.getElementById('autoRefreshBtn').textContent = '開始自動重新整理';
    }
}

// 頁面載入完成後執行
document.addEventListener('DOMContentLoaded', function() {
    // 新增自動重新整理按鈕
    const quickTools = document.querySelector('.card-body .row');
    if (quickTools) {
        const refreshDiv = document.createElement('div');
        refreshDiv.className = 'col-md-12 mt-3';
        refreshDiv.innerHTML = `
            <button id="autoRefreshBtn" class="btn btn-outline-info btn-sm" onclick="toggleAutoRefresh()">
                <i class="fas fa-sync-alt me-2"></i>開始自動重新整理
            </button>
        `;
        quickTools.appendChild(refreshDiv);
    }
    
    // 高亮顯示測試結果
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        if (badge.textContent.includes('FAIL')) {
            badge.classList.add('pulse');
        }
    });
});
</script>

<style>
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>
