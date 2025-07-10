<?php
// 管理端登入頁
session_start();
if(isset($_SESSION['user'])) header('Location: dashboard.php');
$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  require_once '../includes/db.php';
  $user = login($_POST['username'], $_POST['password']);
  if($user){
    $_SESSION['user'] = $user;
    header('Location: dashboard.php');
    exit;
  } else {
    $error = '帳號或密碼錯誤';
  }
}
?><!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>管理登入</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm p-4">
        <h3 class="mb-4 text-center">管理登入</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">帳號</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">密碼</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">登入</button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
