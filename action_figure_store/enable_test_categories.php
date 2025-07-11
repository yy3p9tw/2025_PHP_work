<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 啟用一些測試分類
    $stmt = $conn->prepare('UPDATE categories SET status = 1 WHERE id IN (1, 2, 11, 12)');
    $stmt->execute();
    
    echo "已啟用分類 1, 2, 11, 12\n";
    
    // 檢查結果
    $stmt = $conn->query("SELECT id, name, status FROM categories WHERE id IN (1, 2, 11, 12)");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, 名稱: {$row['name']}, 狀態: {$row['status']}\n";
    }
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
