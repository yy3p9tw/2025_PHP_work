<?php
require_once '../includes/db.php';
$database = new Database();
$conn = $database->getConnection();

// 這裡可以加入一些邏輯來處理桌號輸入
// 例如：檢查桌號是否存在於資料庫中

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>點餐系統 - 輸入桌號</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>歡迎使用點餐系統</h2>
                    </div>
                    <div class="card-body">
                        <form action="customer_order.php" method="GET">
                            <div class="mb-3">
                                <label for="table_number" class="form-label">請輸入您的桌號：</label>
                                <input type="text" class="form-control" id="table_number" name="table_number" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">進入點餐</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap JS (Bundle 包含 Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>