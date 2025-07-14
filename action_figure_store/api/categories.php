<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

function buildCategoryTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = buildCategoryTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }

    return $branch;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query('SELECT id, name, parent_id FROM categories ORDER BY sort_order');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categoryTree = buildCategoryTree($categories);
    
    jsonResponse(true, ['categories' => $categoryTree]);
} catch (Exception $e) {
    jsonResponse(false, null, $e->getMessage());
}
?>
