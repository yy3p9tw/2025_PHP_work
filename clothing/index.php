<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>衣服管理系統 - 首頁</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
    body { background: #fff7f0; }
    .main-title { color: #b97a56; text-align: center; margin-top: 2em; letter-spacing: 0.05em; }
    .card-link.card {
      background: linear-gradient(135deg, #ffb347 0%, #ff9966 100%);
      border-radius: 16px;
      box-shadow: 0 2px 16px #ffb34733;
      color: #fff;
      font-weight: 600;
      text-decoration: none;
      transition: box-shadow 0.18s, background 0.18s, color 0.18s;
      border: 2px solid #fffbe6;
      min-height: 160px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-size: 1.18em;
      padding: 2.2em 1em 2em 1em;
    }
    .card-link.card:hover {
      background: linear-gradient(135deg, #ff9966 0%, #ffb347 100%);
      color: #fffbe6;
      box-shadow: 0 4px 24px #ffb34755;
      border-color: #ffb347;
    }
    .card-icon {
      font-size: 2.2em;
      margin-bottom: 0.6em;
    }
    .card-title {
      font-size: 1.1em;
      font-weight: 600;
      letter-spacing: 0.02em;
    }
    @media (max-width: 700px) {
      .main-title { font-size: 1.2em; }
      .card-link.card { font-size: 1.02em; padding: 1.5em 0.5em 1.5em 0.5em; }
      .card-icon { font-size: 1.5em; }
    }
    footer { color: #b97a56 !important; }
    </style>
</head>
<body class="warm-bg">
    <main class="container py-4" style="max-width:900px;">
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
        </div>
    </main>
    <footer class="text-center my-4 text-secondary">YU &copy; 2025 管理系統</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
