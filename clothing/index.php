<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>衣服管理系統 - 首頁</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    .card-nav-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 2em 2em;
      margin: 2.5em 0 2em 0;
      justify-items: center;
    }
    .card-link.card {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #ffb347;
      border-radius: 16px;
      box-shadow: 0 2px 16px #ffb34733;
      padding: 2.2em 1em 2em 1em;
      text-align: center;
      font-size: 1.18em;
      color: #fff;
      font-weight: 600;
      text-decoration: none;
      transition: box-shadow 0.18s, background 0.18s, color 0.18s;
      border: 2px solid #fff7f0;
    }
    .card-link.card:hover {
      background: #ff9966;
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
    </style>
</head>
<body>
    <main style="max-width:900px;margin:auto;">
        <h1 class="main-title" style="text-align:center;margin-top:2em;">衣服管理系統</h1>
        <div class="card-nav-grid">
            <a class="card-link card" href="pages/items/list.php">
                <div class="card-icon"><span>🛒</span></div>
                <div class="card-title">商品管理</div>
            </a>
            <a class="card-link card" href="pages/customers/list.php">
                <div class="card-icon"><span>👤</span></div>
                <div class="card-title">客戶管理</div>
            </a>
            <a class="card-link card" href="pages/sales/list.php">
                <div class="card-icon"><span>💰</span></div>
                <div class="card-title">銷售記錄</div>
            </a>
            <a class="card-link card" href="pages/report.php">
                <div class="card-icon"><span>📊</span></div>
                <div class="card-title">統計報表</div>
            </a>
        </div>
    </main>
    <footer style="text-align:center;margin:2em 0 1em 0;color:#b97a56;">YU &copy; 2025 管理系統</footer>
</body>
</html>
