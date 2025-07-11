<?php
// 簡單的 API 測試
echo "測試分類 API...\n";

// 模擬 API 調用
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 遞迴函數：建立分類樹狀結構
    function buildCategoryTree($conn, $parent_id = null) {
        $sql = "SELECT id, name, description, parent_id, sort_order, status 
                FROM categories 
                WHERE " . ($parent_id ? "parent_id = ?" : "parent_id IS NULL") . " 
                AND status = 1
                ORDER BY sort_order, name";
        
        $stmt = $conn->prepare($sql);
        if ($parent_id) {
            $stmt->execute([$parent_id]);
        } else {
            $stmt->execute();
        }
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['children'] = buildCategoryTree($conn, $row['id']);
            $categories[] = $row;
        }
        
        return $categories;
    }

    $categories = buildCategoryTree($conn);

    echo "API 回應:\n";
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
