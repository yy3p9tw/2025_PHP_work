<?php
// 取得 id
if (!isset($_GET['id'])) {
    header('Location: manage.php?msg=未指定編輯項目');
    exit;
}
$id = intval($_GET['id']);

// 連接資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, "root", "");

// 取得原始資料
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: manage.php?msg=找不到該檔案');
    exit;
}

// 如果有送出表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $update = $pdo->prepare("UPDATE uploads SET type=?, description=? WHERE id=?");
    $update->execute([$type, $description, $id]);
    header('Location: manage.php?msg=編輯成功');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯檔案資訊</title>
    <style>
        body { font-family: 'Noto Sans TC', Arial, sans-serif; background: #f7f8fa; }
        .edit-form {
            background: #fff; max-width: 420px; margin: 40px auto; padding: 32px 28px 24px 28px;
            border-radius: 16px; box-shadow: 0 4px 24px #bfa04633; display: flex; flex-direction: column; gap: 18px;
        }
        .edit-form label { font-weight: bold; color: #1a237e; margin-bottom: 6px; }
        .edit-form input, .edit-form select, .edit-form textarea {
            padding: 10px 12px; border-radius: 8px; border: 1px solid #b6c7e6; font-size: 1em; font-family: inherit; margin-bottom: 8px;
        }
        .edit-form textarea { min-height: 60px; resize: vertical; }
        .edit-form button {
            background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%); color: #fff; border: none; border-radius: 20px;
            padding: 12px 0; font-weight: bold; font-size: 1.08em; cursor: pointer; transition: background 0.2s;
        }
        .edit-form button:hover { background: linear-gradient(90deg, #60a5fa 60%, #3b82f6 100%); }
        .back-link { text-align: center; margin-top: 18px; display: block; color: #3b82f6; text-decoration: underline; }
    </style>
</head>
<body>
    <form class="edit-form" method="post">
        <h2>編輯檔案資訊</h2>
        <label>檔名：</label>
        <input type="text" value="<?= htmlspecialchars($row['name']) ?>" disabled>
        <label for="type">檔案類型：</label>
        <select name="type" id="type">
            <option value="image" <?= $row['type']==='image'?'selected':'' ?>>影像</option>
            <option value="document" <?= $row['type']==='document'?'selected':'' ?>>文件</option>
            <option value="video" <?= $row['type']==='video'?'selected':'' ?>>影片</option>
            <option value="music" <?= $row['type']==='music'?'selected':'' ?>>音樂</option>
        </select>
        <label for="description">檔案描述：</label>
        <textarea name="description" id="description" placeholder="請輸入檔案描述..."><?= htmlspecialchars($row['description']) ?></textarea>
        <button type="submit">儲存變更</button>
        <a href="manage.php" class="back-link">返回管理頁</a>
    </form>
</body>
</html>
