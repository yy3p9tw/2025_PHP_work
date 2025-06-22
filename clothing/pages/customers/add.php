<?php
require_once '../../includes/db.php';
$Customer = new DB('customers');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'notes' => $_POST['notes'],
    ];
    $Customer->insert($data);
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增顧客</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body.warm-bg { background: #fff7f0; }
        h1.main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .form-container {
            max-width: 420px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px #ffb34733;
            padding: 2em 1.5em 1.5em 1.5em;
            display: flex;
            flex-direction: column;
            gap: 1.2em;
        }
        .form-container label {
            font-weight: bold;
            color: #b97a56;
            margin-bottom: 0.3em;
        }
        .form-container input, .form-container textarea {
            padding: 0.6em 1em;
            border: 1px solid #ffb347;
            border-radius: 6px;
            font-size: 1em;
            margin-bottom: 0.5em;
        }
        .form-container button, .form-container .btn-back {
            padding: 0.5em 1.2em;
            border-radius: 6px;
            border: 1px solid #ffb347;
            background: #ffb347;
            color: #fff;
            font-size: 1em;
            margin-top: 0.5em;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s;
        }
        .form-container button:hover, .form-container .btn-back:hover {
            background: #ffa500;
        }
        @media (max-width: 600px) {
            .form-container {
                max-width: 98vw;
                padding: 1.2em 0.5em 1em 0.5em;
            }
            .main-title { font-size: 1.1em; }
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">新增客戶</h1>
    <form method="post" class="form-container">
        <label>姓名：<input type="text" name="name" required></label>
        <label>電話：<input type="text" name="phone"></label>
        <label>電子郵件：<input type="email" name="email"></label>
        <label>地址：<input type="text" name="address"></label>
        <label>備註：<textarea name="notes"></textarea></label>
        <button type="submit">新增</button>
        <a href="list.php" class="btn-back">回客戶列表</a>
    </form>
</body>
</html>
