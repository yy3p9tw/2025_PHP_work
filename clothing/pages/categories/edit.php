<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯分類</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;">
        <label>分類名稱：<input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required></label>
        <button type="submit">儲存</button>
        <a href="list.php" class="btn-back">返回列表</a>
    </form>
</body>