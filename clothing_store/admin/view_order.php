<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// 獲取訂單基本資訊
try {
    $stmt = $pdo->prepare('SELECT s.*, c.name as customer_name, c.phone, c.email, c.address FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = ?');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {
        die('找不到該訂單');
    }
} catch (PDOException $e) {
    die("無法讀取訂單資料: " . $e->getMessage());
}

// 獲取訂單中的商品詳情
try {
    $stmt = $pdo->prepare(
       'SELECT si.*, i.name as item_name, c.name as color_name, s.name as size_name 
        FROM sale_items si 
        JOIN item_variants iv ON si.item_variant_id = iv.id 
        JOIN items i ON iv.item_id = i.id 
        JOIN colors c ON iv.color_id = c.id 
        JOIN sizes s ON iv.size_id = s.id 
        WHERE si.sale_id = ?'
    );
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取訂單商品資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 訂單詳情 #<?php echo htmlspecialchars($order['id']); ?></title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <nav class="admin-navbar">
        <a href="index.php" class="admin-nav-item">商品管理</a>
        <a href="orders.php" class="admin-nav-item selected">訂單管理</a>
    </nav>
    <div class="container">
        <a href="orders.php" class="btn">&larr; 回到訂單列表</a>
        <h1>訂單詳情 #<?php echo htmlspecialchars($order['id']); ?></h1>

        <div class="order-details-section">
            <h2>訂單資訊</h2>
            <p><strong>訂單日期:</strong> <?php echo htmlspecialchars($order['sale_date']); ?></p>
            <p><strong>總金額:</strong> NT$ <?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>備註:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>

        <div class="customer-details-section">
            <h2>顧客資訊</h2>
            <p><strong>姓名:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? '訪客'); ?></p>
            <p><strong>電話:</strong> <?php echo htmlspecialchars($order['phone'] ?? '無'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? '無'); ?></p>
            <p><strong>地址:</strong> <?php echo nl2br(htmlspecialchars($order['address'] ?? '無')); ?></p>
        </div>

        <div class="order-items-section">
            <h2>訂購商品</h2>
            <table>
                <thead>
                    <tr>
                        <th>商品名稱</th>
                        <th>顏色</th>
                        <th>尺寸</th>
                        <th>單價</th>
                        <th>數量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($order_items)): ?>
                        <tr><td colspan="6">此訂單沒有商品。</td></tr>
                    <?php else: ?>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['color_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['size_name']); ?></td>
                                <td>NT$ <?php echo number_format($item['price_at_sale'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>NT$ <?php echo number_format($item['price_at_sale'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>