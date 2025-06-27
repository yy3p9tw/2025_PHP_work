<?php
require_once '../../includes/db.php';
$Item = new DB('items');

$id = $_GET['id'] ?? 0;

if ($id) {
    $item = $Item->find(['id' => $id]);
    if ($item) {
        // 刪除相關圖片檔案
        if (!empty($item['image'])) {
            $image_path = '../../uploads/' . $item['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $Item->delete($id);
    }
}
header('Location: list.php');
exit;