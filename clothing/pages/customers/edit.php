<?php
require_once '../../includes/db.php';
$Customer = new DB('customers');

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = intval($_GET['id']);
$customer = $Customer->all("id = $id");
if (!$customer) {
    echo '查無此客戶';
    exit;
}
$customer = $customer[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'notes' => $_POST['notes'],
    ];
    $Customer->update($id, $data);
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯客戶</title>
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
    <h1 class="main-title">編輯客戶</h1>
    <form method="post" class="card p-4 mx-auto mt-4" style="max-width:420px;" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">姓名：</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($customer['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">電話：</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">電子郵件：</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">地址：</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($customer['address']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">備註：</label>
            <textarea name="notes" class="form-control"><?= htmlspecialchars($customer['notes']) ?></textarea>
        </div>
        <div class="card-action-bar">
            <button type="submit" class="btn btn-back btn-sm">儲存</button>
            <a href="list.php" class="btn btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
