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
    <title>編輯客戶</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body.warm-bg { background: #fff7f0; }
        h1.main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .form-container {
            background: #fff;
            max-width: 500px;
            margin: 40px auto;
            padding: 2em 2em 1em 2em;
            border-radius: 14px;
            box-shadow: 0 2px 16px #ffb34733;
        }
        label { display: block; margin-bottom: 0.5em; color: #b97a56; font-weight: 500; }
        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ffb347;
            border-radius: 6px;
            margin-bottom: 1.2em;
            font-size: 1em;
        }
        textarea { min-height: 60px; }
        .btn-back, button, input[type="submit"] {
            background: linear-gradient(135deg, #ffb347 0%, #ff9966 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.6em 1.5em;
            font-size: 1em;
            font-weight: 500;
            margin-right: 0.5em;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px #ffb34744;
            display: inline-block;
        }
        .btn-back:hover, button:hover, input[type="submit"]:hover {
            background: linear-gradient(135deg, #ff9966 0%, #ffb347 100%);
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯客戶</h1>
    <form method="post" class="form-container">
        <label>姓名：<input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required></label>
        <label>電話：<input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>"></label>
        <label>電子郵件：<input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>"></label>
        <label>地址：<input type="text" name="address" value="<?= htmlspecialchars($customer['address']) ?>"></label>
        <label>備註：<textarea name="notes"><?= htmlspecialchars($customer['notes']) ?></textarea></label>
        <button type="submit">儲存</button>
        <a href="list.php" class="btn-back">回客戶列表</a>
    </form>
</body>
</html>
