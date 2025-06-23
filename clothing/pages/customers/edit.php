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
    <title>編輯顧客</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <h1 class="main-title">編輯客戶</h1>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;">
        <label>姓名：<input type="text" name="name" required value="<?= htmlspecialchars($customer['name']) ?>"></label>
        <label>電話：<input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>"></label>
        <label>電子郵件：<input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>"></label>
        <label>地址：<input type="text" name="address" value="<?= htmlspecialchars($customer['address']) ?>"></label>
        <label>備註：<textarea name="notes"><?= htmlspecialchars($customer['notes']) ?></textarea></label>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">儲存</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
</body>
</html>
