<?php
require_once 'includes/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>管理員帳號設定</h2>";
    
    // 檢查 users 表格是否存在
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    
    if (empty($tables)) {
        echo "<h3>建立 users 表格...</h3>";
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(255) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','staff','user') DEFAULT 'user',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p>✅ users 表格建立成功</p>";
    }
    
    // 檢查是否有管理員帳號
    $admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    
    if ($admin_count == 0) {
        echo "<h3>建立預設管理員帳號...</h3>";
        
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $password]);
        
        echo "<p>✅ 預設管理員帳號建立成功</p>";
        echo "<p><strong>帳號：admin</strong></p>";
        echo "<p><strong>密碼：admin123</strong></p>";
    } else {
        echo "<p>✅ 已存在 $admin_count 個管理員帳號</p>";
        
        $admins = $pdo->query("SELECT username, created_at FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>管理員列表：</h4>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>{$admin['username']} (建立於: {$admin['created_at']})</li>";
        }
        echo "</ul>";
    }
    
    // 顯示所有使用者
    $all_users = $pdo->query("SELECT username, role, created_at FROM users ORDER BY role, username")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h4>所有使用者：</h4>";
    echo "<ul>";
    foreach ($all_users as $user) {
        echo "<li>{$user['username']} ({$user['role']}) - {$user['created_at']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ 錯誤：" . $e->getMessage() . "</h2>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>管理員帳號設定</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3, h4 { color: #333; }
        p { margin: 10px 0; }
        ul { margin: 10px 0 10px 20px; }
        li { margin: 5px 0; }
        strong { color: #d73502; }
    </style>
</head>
<body>
    <h1>管理員帳號設定檢查</h1>
    <a href="admin/index.php">→ 前往後台登入</a> | 
    <a href="setup_check.php">→ 資料庫設定檢查</a>
</body>
</html>
