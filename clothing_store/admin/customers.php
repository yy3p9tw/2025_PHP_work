<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$search_query = $_GET['search'] ?? '';

// 獲取所有顧客
try {
    $sql = 'SELECT * FROM customers';
    $params = [];

    if (!empty($search_query)) {
        $sql .= ' WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? OR address LIKE ?';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    $sql .= ' ORDER BY name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取顧客資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 顧客列表</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <?php
$current_page = 'customers';
require_once 'admin_nav.php';
?>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>顧客管理</h1>
            <a href="add_customer.php" class="btn btn-success">新增顧客</a>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'add'): ?>
            <p class="success-message">新增顧客成功！</p>
        <?php endif; ?>
        <div class="search-container">
            <form action="customers.php" method="get">
                <input type="text" name="search" placeholder="搜尋顧客 (姓名, 電話, Email, 地址)..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>姓名</th>
                    <th>電話</th>
                    <th>Email</th>
                    <th>地址</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="6">目前沒有任何顧客。</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($customer['address'])); ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn">編輯</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>