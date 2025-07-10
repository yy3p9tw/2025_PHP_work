<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 暫時回傳固定的分類資料，後續可以從資料庫讀取
    $categories = [
        [
            'id' => 1,
            'name' => '動漫公仔',
            'children' => [
                ['id' => 11, 'name' => '火影忍者', 'children' => []],
                ['id' => 12, 'name' => '鬼滅之刃', 'children' => []],
                ['id' => 13, 'name' => '海賊王', 'children' => []],
                ['id' => 14, 'name' => '進擊的巨人', 'children' => []]
            ]
        ],
        [
            'id' => 2,
            'name' => '遊戲公仔',
            'children' => [
                ['id' => 21, 'name' => '英雄聯盟', 'children' => []],
                ['id' => 22, 'name' => '原神', 'children' => []],
                ['id' => 23, 'name' => '最終幻想', 'children' => []]
            ]
        ],
        [
            'id' => 3,
            'name' => '電影公仔',
            'children' => [
                ['id' => 31, 'name' => '漫威系列', 'children' => []],
                ['id' => 32, 'name' => 'DC 系列', 'children' => []],
                ['id' => 33, 'name' => '星際大戰', 'children' => []]
            ]
        ],
        [
            'id' => 4,
            'name' => '特殊系列',
            'children' => [
                ['id' => 41, 'name' => '限定版', 'children' => []],
                ['id' => 42, 'name' => '預購商品', 'children' => []],
                ['id' => 43, 'name' => '二手商品', 'children' => []]
            ]
        ]
    ];

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
