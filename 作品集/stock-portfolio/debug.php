<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統狀態檢查</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">系統狀態檢查</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Session 狀態</h5>
                </div>
                <div class="card-body">
                    <pre><?php
                    echo "Session ID: " . session_id() . "\n";
                    echo "登入狀態: " . (isLoggedIn() ? "已登入" : "未登入") . "\n";
                    if (isLoggedIn()) {
                        echo "用戶 ID: " . $_SESSION['user_id'] . "\n";
                        echo "用戶名: " . $_SESSION['username'] . "\n";
                        echo "角色: " . $_SESSION['role'] . "\n";
                    }
                    ?></pre>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>資料庫連接</h5>
                </div>
                <div class="card-body">
                    <pre><?php
                    try {
                        $db = new Database();
                        $conn = $db->getConnection();
                        echo "資料庫連接: 成功\n";
                        
                        // 檢查表是否存在
                        $tables = ['users', 'stocks', 'portfolios', 'news', 'market_indices'];
                        foreach ($tables as $table) {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM $table");
                            $stmt->execute();
                            $count = $stmt->fetchColumn();
                            echo "表 $table: $count 筆記錄\n";
                        }
                    } catch (Exception $e) {
                        echo "資料庫連接錯誤: " . $e->getMessage() . "\n";
                    }
                    ?></pre>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>API 測試</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary" onclick="testAPI('news')">測試新聞 API</button>
                    <button class="btn btn-primary" onclick="testAPI('market-indices')">測試市場指數 API</button>
                    <button class="btn btn-primary" onclick="testAPI('stocks')">測試股票 API</button>
                    
                    <div id="apiResults" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function testAPI(endpoint) {
    const url = `api/${endpoint}.php`;
    
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            $('#apiResults').html(
                `<h6>API ${endpoint} 測試結果:</h6>
                <pre>${JSON.stringify(response, null, 2)}</pre>`
            );
        },
        error: function(xhr, status, error) {
            $('#apiResults').html(
                `<h6>API ${endpoint} 測試失敗:</h6>
                <pre>Status: ${xhr.status}\nError: ${error}\nResponse: ${xhr.responseText}</pre>`
            );
        }
    });
}
</script>
</body>
</html>
