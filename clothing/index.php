<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>衣服管理系統 - 首頁</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="warm-bg">
    <main class="container py-4">
        <h1 class="main-title text-center my-4">衣服管理系統</h1>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 justify-content-center">
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/items/list.php">
                    <div class="card-icon"><span>🛒</span></div>
                    <div class="card-title">商品管理</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/customers/list.php">
                    <div class="card-icon"><span>👤</span></div>
                    <div class="card-title">客戶管理</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/sales/list.php">
                    <div class="card-icon"><span>💰</span></div>
                    <div class="card-title">銷售記錄</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/report.php">
                    <div class="card-icon"><span>📊</span></div>
                    <div class="card-title">統計報表</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/sizes/list.php">
                    <div class="card-icon"><span>📏</span></div>
                    <div class="card-title">尺寸管理</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/colors/add.php">
                    <div class="card-icon"><span>🎨</span></div>
                    <div class="card-title">顏色管理</div>
                </a>
            </div>
            <div class="col d-flex">
                <a class="card-link card flex-fill" href="pages/categories/list.php">
                    <div class="card-icon"><span>🏷️</span></div>
                    <div class="card-title">分類管理</div>
                </a>
            </div>
        </div>
    </main>
    <footer class="text-center my-4 text-secondary">YU &copy; 2025 管理系統</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>