<?php
// 銷售記錄搜尋功能（簡易版，僅查詢，不回傳 HTML）
require_once '../../includes/db.php';
$Sale = new DB('sales');
$Item = new DB('items');
$where = [];
$params = [];
if (!empty($_GET['customer_id'])) {
    $where[] = "customer_id = :customer_id";
    $params['customer_id'] = $_GET['customer_id'];
}
if (!empty($_GET['item_name'])) {
    $itemName = trim($_GET['item_name']);
    $Item = new DB('items');
    $items = $Item->all();
    $itemIds = array_column(array_filter($items, function($item) use ($itemName) {
        return mb_strpos($item['name'], $itemName) !== false;
    }), 'id');
    if ($itemIds) {
        $where[] = "item_id IN (".implode(",", array_map('intval', $itemIds)).")";
    } else {
        $where[] = "0"; // 無結果
    }
}
if (!empty($_GET['date_from'])) {
    $where[] = "sale_date >= :date_from";
    $params['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where[] = "sale_date <= :date_to";
    $params['date_to'] = $_GET['date_to'];
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$dbh = $Sale->getPdo();
$sql = "SELECT * FROM sales $where_sql ORDER BY id DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute($params);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
