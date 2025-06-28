<?php
require_once 'auth_check.php';
require_once '../includes/db.php';

$search_query = $_GET['search'] ?? '';

try {
    $pdo = get_pdo();
    $sql = 'SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id';
    $params = [];

    if (!empty($search_query)) {
        $sql .= ' WHERE i.name LIKE ? OR i.description LIKE ?';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    $sql .= ' ORDER BY i.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取商品資料: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 商品列表</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <?php
$current_page = 'items'; 
require_once 'admin_nav.php'; 
?>
    <div class="container">
        <h1>商品管理</h1>
        <div class="search-container">
            <form action="index.php" method="get">
                <input type="text" name="search" placeholder="搜尋商品..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <a href="add_item.php" class="btn btn-success">新增商品</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>圖片</th>
                    <th>名稱</th>
                    <th>分類</th>
                    <th>描述</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                    <td>
                        <?php if (!empty($item['image'])): ?>
                            <img src="../images/<?php echo htmlspecialchars($item['image']); ?>" alt="" width="100">
                        <?php else: ?>
                            無圖片
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['category_name'] ?? '未分類'); ?></td>
                    <td class="description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></td>
                    <td>
                        <a href="manage_variants.php?item_id=<?php echo $item['id']; ?>" class="btn">管理庫存</a>
                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn">編輯</a>
                                <form action="delete_item.php" method="post" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('確定要刪除嗎？');">刪除</button>
                                </form>
                            </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>