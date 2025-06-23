<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯顏色</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;">
        <label>顏色名稱：<input type="text" name="name" value="<?= htmlspecialchars($color['name']) ?>" required></label>
        <button type="submit">儲存</button>
        <a href="add.php" class="btn-back">返回顏色管理</a>
    </form>
</body>