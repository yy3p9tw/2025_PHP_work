<?php
require_once '../../includes/db.php';
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$Category = new DB('categories');
$id = intval($_GET['id']);
$category = $Category->all("id = $id");
if (!$category) {
    echo '查無此分類';
    exit;
}
$category = $category[0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Category->update($id, ['name' => $_POST['name']]);
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯分類</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .card { box-shadow: 0 2px 16px #ffb34733; }
        .form-label { font-weight: bold; color: #b97a56; }
        .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
        .btn-back:hover { background: #ffa500; color: #fff; }
        .card-action-bar { margin-top:1.2em; display:flex; gap:0.5em; flex-wrap:wrap; }
        @media (max-width: 600px) {
            .main-title { font-size: 1.1em; }
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯分類</h1>
    <form method="post" class="card p-4 mx-auto mt-4" style="max-width:420px;" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">分類名稱：</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <div class="card-action-bar">
            <button type="submit" class="btn btn-back btn-sm">儲存</button>
            <a href="list.php" class="btn btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>