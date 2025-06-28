<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$search_query = $_GET['search'] ?? '';

// 獲取所有訂單
try {
    $sql = 'SELECT s.*, c.name as customer_name, c.phone, c.email, c.address FROM sales s LEFT JOIN customers c ON s.customer_id = c.id';
    $params = [];

    if (!empty($search_query)) {
        $sql .= ' WHERE s.id LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    $sql .= ' ORDER BY s.sale_date DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取訂單資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 訂單列表</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <?php
$current_page = 'orders';
require_once 'admin_nav.php';
?>
    <div class="container">
        <h1>訂單管理</h1>
        <div class="search-container">
            <form action="orders.php" method="get">
                <input type="text" name="search" placeholder="搜尋訂單 (ID, 顧客, 電話, Email)..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <a href="add_order.php" class="btn btn-success">新增訂單</a>
        <table>
            <thead>
                <tr>
                    <th>訂單ID</th>
                    <th>顧客</th>
                    <th>電話</th>
                    <th>地址</th>
                    <th>訂單日期</th>
                    <th>總金額</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="5">目前沒有任何訂單。</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? '訪客'); ?></td>
                            <td><?php echo htmlspecialchars($order['phone'] ?? '無'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($order['address'] ?? '無')); ?></td>
                            <td><?php echo htmlspecialchars($order['sale_date']); ?></td>
                            <td>NT$ <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn">查看詳情</a>
                                <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn">編輯</a>
                                <form action="delete_order.php" method="post" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('確定要刪除此訂單嗎？這將會歸還商品庫存。');">刪除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>