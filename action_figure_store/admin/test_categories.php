<?php
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>資料表檢查</h2>";
    
    // 檢查 categories 表是否存在
    $stmt = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ categories 表存在</p>";
        
        // 檢查資料
        $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>分類數量: " . $result['count'] . "</p>";
        
        // 顯示所有分類
        $stmt = $conn->query("SELECT * FROM categories ORDER BY parent_id, sort_order");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>現有分類:</h3>";
        echo "<ul>";
        foreach ($categories as $cat) {
            echo "<li>ID: {$cat['id']}, 名稱: {$cat['name']}, 父分類: " . ($cat['parent_id'] ?: '無') . ", 排序: {$cat['sort_order']}, 狀態: {$cat['status']}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>❌ categories 表不存在</p>";
        
        // 嘗試建立表格
        echo "<h3>嘗試建立 categories 表...</h3>";
        
        $sql = file_get_contents('../create_categories_tables.sql');
        $conn->exec($sql);
        
        echo "<p>✅ 表格建立完成</p>";
        
        // 重新檢查
        $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>分類數量: " . $result['count'] . "</p>";
    }
    
    // 檢查 product_category 表
    $stmt = $conn->query("SHOW TABLES LIKE 'product_category'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ product_category 表存在</p>";
    } else {
        echo "<p>❌ product_category 表不存在</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ 錯誤: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>分類系統測試</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        p { margin: 10px 0; }
        ul { margin: 10px 0 10px 20px; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>分類系統測試頁面</h1>
    <a href="../admin/categories.php">← 回到分類管理</a>
</body>
</html>
