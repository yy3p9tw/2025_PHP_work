<?php
// D:\YU\church_website\backend\login.php

session_start(); // 啟動 Session

// 如果使用者已登入，直接導向到後台儀表板
if (isset($_SESSION['user_id'])) {
    header('Location: admin/index.php');
    exit();
}

require_once 'includes/db.php'; // 引入資料庫連線檔案

$error_message = '';

// 檢查是否為表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = '請輸入使用者名稱和密碼';
    } else {
        // 從資料庫查詢使用者
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 驗證密碼 (注意：這裡假設密碼是明文儲存，實際專案應使用 password_hash 和 password_verify)
            // 為了能登入，您需要先手動在資料庫中新增一筆使用者資料
            if ($password === $user['password']) { // 在安全的應用中，應使用 password_verify($password, $user['password'])
                // 登入成功，設定 Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // 導向到後台儀表板
                header('Location: admin/index.php');
                exit();
            } else {
                $error_message = '密碼錯誤';
            }
        } else {
            $error_message = '使用者不存在';
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台登入 - 教會網站管理系統</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        h1 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.7rem; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #0056b3; }
        .error-message { color: #dc3545; text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>後台登入</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">使用者名稱</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">登入</button>
        </form>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
