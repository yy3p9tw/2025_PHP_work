<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

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

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '無法載入分類資料'
    ]);
}
?>
