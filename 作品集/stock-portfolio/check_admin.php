<?php
require_once 'includes/database.php';

$db = new Database();

// 檢查管理員帳戶
$admin_users = $db->fetchAll("SELECT * FROM users WHERE role = 'admin'");

echo "管理員帳戶：\n";
foreach ($admin_users as $admin) {
    echo "ID: {$admin['id']}, 用戶名: {$admin['username']}, 信箱: {$admin['email']}\n";
}

// 如果沒有管理員，創建一個
if (empty($admin_users)) {
    echo "沒有管理員帳戶，創建默認管理員...\n";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $result = $db->execute("
        INSERT INTO users (username, email, password_hash, role, is_active) 
        VALUES (?, ?, ?, ?, ?)
    ", ['admin', 'admin@example.com', $password, 'admin', 1]);
    
    if ($result) {
        echo "管理員帳戶已創建：\n";
        echo "用戶名: admin\n";
        echo "密碼: admin123\n";
        echo "信箱: admin@example.com\n";
    } else {
        echo "創建管理員帳戶失敗\n";
    }
}

// 檢查系統統計
$stats = [
    'total_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users')['count'],
    'total_stocks' => $db->fetchOne('SELECT COUNT(*) as count FROM stocks')['count'],
    'total_news' => $db->fetchOne('SELECT COUNT(*) as count FROM news')['count'],
    'total_transactions' => $db->fetchOne('SELECT COUNT(*) as count FROM transactions')['count'],
];

echo "\n系統統計：\n";
echo "用戶數: {$stats['total_users']}\n";
echo "股票數: {$stats['total_stocks']}\n";
echo "新聞數: {$stats['total_news']}\n";
echo "交易數: {$stats['total_transactions']}\n";
?>
