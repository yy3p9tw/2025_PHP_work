<?php
session_start();
require_once '../includes/db.php';

$order_id = $_GET['order_id'] ?? null;
$total_amount = $_GET['total_amount'] ?? null;
$table_number = $_GET['table_number'] ?? '';

// 如果沒有訂單ID或總金額，導回首頁
if (!$order_id || !$total_amount) {
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單完成</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-success-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #ffffff;
        }
        .order-success-card .icon {
            font-size: 4rem;
            color: #28a745; /* Green for success */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-success-card text-center">
            <div class="icon mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h1 class="mb-3 text-success">訂單已送出！</h1>
            <p class="lead">您的訂單 <strong>#<?php echo htmlspecialchars($order_id); ?></strong> 已成功送出。</p>
            <p class="lead">桌號: <strong><?php echo htmlspecialchars($table_number); ?></strong></p>
            <h2 class="my-4">總金額: <strong class="text-primary">$<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></strong></h2>
            <p class="fs-5">請您稍候，並前往櫃檯結帳。</p>
            <hr>
            <a href="customer_order.php?table_number=<?php echo urlencode($table_number); ?>" class="btn btn-outline-primary mt-3">繼續點餐</a>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>