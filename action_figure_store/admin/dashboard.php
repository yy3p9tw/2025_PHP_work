<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// 獲取產品總數
$stmt_products = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $stmt_products->fetchColumn();

// 獲取管理員總數 (如果需要)
$stmt_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$total_admins = $stmt_users->fetchColumn();

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>儀表板 - 公仔銷售網站後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 側邊導航欄 -->
            <?php include __DIR__ . '/sidebar.php'; ?>

            <!-- 主要內容區域 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> 儀表板</h1>
                </div>

                <!-- 麵包屑導覽已移除 -->

                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">產品總數</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_products; ?></h5>
                                <p class="card-text">目前網站上的公仔產品數量。</p>
                                <div class="progress mt-3" style="background-color: #b03060;"> <!-- 更暗的粉紅底色 -->
                                    <div class="progress-bar" role="progressbar" style="width: 75%; background-color: #ff69b4;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">管理員帳號</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_admins; ?></h5>
                                <p class="card-text">後台管理員帳號數量。</p>
                            </div>
                        </div>
                    </div>
                    <!-- 可以添加更多統計數據 -->
                </div>

                <h3>歡迎來到公仔銷售網站後台管理系統！</h3>
                <p>您可以在這裡管理產品、使用者等。</p>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
</body>
</html>