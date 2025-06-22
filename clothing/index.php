<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>衣服管理系統 - 首頁</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #fff7f0;
        }
        h1 {
            color: #d2691e;
        }
        .card-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 24px;
            max-width: 900px;
            margin: 60px auto 40px auto;
            padding: 0;
            list-style: none;
        }
        .card-nav li {
            margin: 0;
        }
        .card-link {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 90px;
            background: linear-gradient(135deg, #ffb347 0%, #ff9966 100%);
            color: #fff;
            border-radius: 14px;
            font-size: 1.15em;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 2px 12px #ffb34744;
            transition: background 0.18s, transform 0.18s, box-shadow 0.18s;
            border: none;
            letter-spacing: 1px;
        }
        .card-link:hover, .card-link:focus {
            background: linear-gradient(135deg, #ff9966 0%, #ffb347 100%);
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 6px 24px #ffb34766;
        }
        @media (max-width: 600px) {
            .card-nav { grid-template-columns: 1fr 1fr; }
            .card-link { font-size: 1em; height: 70px; }
        }
        footer {
            color: #b97a56;
        }
    </style>
</head>
<body>
    <main style="max-width:900px;margin:auto;">
        <h1 style="text-align:center;margin-top:2em;">管理系統</h1>
        <ul class="card-nav">
            <li><a class="card-link" href="pages/items/list.php">商品管理</a></li>
            <li><a class="card-link" href="pages/customers/list.php">客戶管理</a></li>
            <li><a class="card-link" href="pages/sales/list.php">銷售記錄</a></li>
            <li><a class="card-link" href="pages/report.php">統計報表</a></li>
        </ul>
    </main>
    <footer style="text-align:center;margin:2em 0 1em 0;">YU &copy; 2025 管理系統</footer>
</body>
</html>
