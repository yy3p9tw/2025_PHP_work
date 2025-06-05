<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>美食論壇</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* 請加到 assets/css/style.css 最後 */
        .back-btn {
            position: fixed;
            right: 24px;
            bottom: 24px;
            background: linear-gradient(120deg, #d72660 60%, #f8bbd0 100%);
            color: #fff;
            padding: 12px 22px;
            border-radius: 30px;
            font-size: 1.08em;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 4px 16px #d7266055;
            border: 2px solid #f8bbd0;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            z-index: 999;
            letter-spacing: 1px;
        }
        .back-btn:hover {
            background: linear-gradient(120deg, #f8bbd0 60%, #d72660 100%);
            color: #fff;
            box-shadow: 0 8px 24px #d72660;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<main></main>
<?php include 'includes/footer.php'; ?>

<!-- 固定右下角回作品集按鈕 -->
<a href="../作品集.html" class="back-btn">回作品集</a>

</body>
</html>