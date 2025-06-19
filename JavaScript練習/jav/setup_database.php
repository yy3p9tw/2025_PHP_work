<?php
// 建立 member_system 資料庫和資料表的 PHP 腳本
header('Content-Type: text/html; charset=utf-8');

// 資料庫連線設定（不指定資料庫名稱，用來建立資料庫）
$host = 'localhost';
$username = 'root';
$password = '';

echo "<h2>🗄️ Member System 資料庫建立工具</h2>";
echo "<hr>";

try {
    // 先連線到 MySQL（不指定資料庫）
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ MySQL 連線成功<br><br>";
    
    // 1. 建立資料庫
    echo "<h3>📋 步驟 1: 建立資料庫</h3>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS member_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ 資料庫 'member_system' 建立成功<br><br>";
    
    // 選擇資料庫
    $pdo->exec("USE member_system");
    
    // 2. 建立 users 資料表
    echo "<h3>👥 步驟 2: 建立 users 資料表</h3>";
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'user', 'demo') DEFAULT 'user',
        status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        login_count INT DEFAULT 0
    )";
    
    $pdo->exec($sql_users);
    echo "✅ users 資料表建立成功<br>";
    echo "   - 包含：id, username, password, name, email, role, status 等欄位<br><br>";
    
    // 3. 建立 login_logs 資料表
    echo "<h3>📊 步驟 3: 建立 login_logs 資料表</h3>";
    $sql_logs = "
    CREATE TABLE IF NOT EXISTS login_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        username VARCHAR(50),
        ip VARCHAR(45),
        user_agent TEXT,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_logs);
    echo "✅ login_logs 資料表建立成功<br>";
    echo "   - 用於記錄用戶登入歷史<br><br>";
    
    // 4. 檢查是否已有測試資料
    echo "<h3>🧪 步驟 4: 插入測試資料</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        // 插入測試用戶
        $test_users = [
            [
                'username' => 'admin',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'name' => '系統管理員',
                'email' => 'admin@example.com',
                'role' => 'admin'
            ],
            [
                'username' => 'user',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'name' => '一般用戶',
                'email' => 'user@example.com',
                'role' => 'user'
            ],
            [
                'username' => 'demo',
                'password' => password_hash('demo123', PASSWORD_DEFAULT),
                'name' => '演示帳號',
                'email' => 'demo@example.com',
                'role' => 'demo'
            ]
        ];
        
        $sql_insert = "INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql_insert);
        
        foreach ($test_users as $user) {
            $stmt->execute([
                $user['username'],
                $user['password'],
                $user['name'],
                $user['email'],
                $user['role']
            ]);
            echo "✅ 建立測試帳號：{$user['username']} / " . 
                 ($user['username'] == 'admin' ? '123456' : 
                  ($user['username'] == 'user' ? 'password' : 'demo123')) . "<br>";
        }
        echo "<br>";
    } else {
        echo "ℹ️ 測試資料已存在（共 {$count} 個用戶）<br><br>";
    }
    
    // 5. 顯示建立結果
    echo "<h3>📋 步驟 5: 建立結果</h3>";
    
    // 顯示所有資料表
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>📊 已建立的資料表：</strong><br>";
    foreach ($tables as $table) {
        echo "   - {$table}<br>";
    }
    echo "<br>";
    
    // 顯示用戶資料
    $stmt = $pdo->query("SELECT username, name, email, role, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<strong>👥 用戶列表：</strong><br>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>帳號</th><th>姓名</th><th>信箱</th><th>角色</th><th>建立時間</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>🎉 完成！</h3>";
    echo "<p style='color: green; font-weight: bold;'>資料庫建立完成！您現在可以使用登入系統了。</p>";
    echo "<p><strong>測試帳號：</strong></p>";
    echo "<ul>";
    echo "<li>管理員：admin / 123456</li>";
    echo "<li>一般用戶：user / password</li>";
    echo "<li>演示帳號：demo / demo123</li>";
    echo "</ul>";
    
    echo "<p><a href='login.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 前往登入頁面</a></p>";
    echo "<p><a href='test_db.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 測試資料庫連線</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ 錯誤</h3>";
    echo "<p style='color: red;'>資料庫操作失敗：" . $e->getMessage() . "</p>";
    echo "<p><strong>常見解決方法：</strong></p>";
    echo "<ul>";
    echo "<li>確認 MySQL 服務已啟動</li>";
    echo "<li>確認帳號密碼正確（預設：root / 空密碼）</li>";
    echo "<li>確認 PHP 已安裝 PDO MySQL 擴充</li>";
    echo "</ul>";
}
?>
