<?php
// filepath: c:\2025-PHP\clothing\pages\colors\add.php
require_once '../../includes/db.php';
$Color = new DB('colors');
$Variant = new DB('item_variants');

$error_message = '';

// 刪除顏色
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    // 檢查是否有商品規格關聯到此顏色
    $related_variants = $Variant->all(['color_id' => $id_to_delete]);

    if (!empty($related_variants)) {
        $error_message = '顏色已被商品規格使用，無法刪除。';
    } else {
        $Color->delete($id_to_delete);
        header('Location: add.php');
        exit;
    }
}

// 新增顏色
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $Color->insert(['name' => $name]);
            header('Location: add.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Duplicate entry
                $error_message = '顏色名稱已存在。';
            } else {
                $error_message = '新增失敗: ' . $e->getMessage();
            }
        }
    } else {
        $error_message = "顏色名稱不能為空。";
    }
}

// 取得所有顏色，並依 id DESC 排序
$colors = $Color->all([], 'id DESC');
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增顏色</title>
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
    <h1 class="main-title">顏色管理</h1>
    <form method="post" class="card p-4 mx-auto mt-4" style="max-width:420px;" autocomplete="off">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">顏色名稱：</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="card-action-bar">
            <button type="submit" class="btn btn-back btn-sm">新增</button>
            <a href="../items/list.php" class="btn btn-back btn-sm">返回商品列表</a>
        </div>
    </form>
    <div class="card mx-auto mt-4" style="max-width:600px;">
        <div class="card-body">
            <h2 class="mb-3" style="font-size:1.1em;color:#d2691e;">現有顏色</h2>
            <div class="row row-cols-1 row-cols-md-2 g-2">
                <?php if (!empty($colors)): ?>
                    <?php foreach($colors as $c): ?>
                    <div class="col">
                        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                            <div class="fw-bold">名稱：<?= htmlspecialchars($c['name']) ?></div>
                            <a href="?delete=<?= $c['id'] ?>" class="btn btn-back btn-sm btn-outline-danger" onclick="return confirm('確定要刪除這個顏色？')">刪除</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center" role="alert">
                            目前沒有任何顏色。
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>