<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '服飾店'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/csrf_functions.php';

$search_query = $_GET['search'] ?? '';

// 在這裡生成 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

try {
    $pdo = get_pdo();
    $sql = 'SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id';
    $params = [];

    if (!empty($search_query)) {
        $sql .= ' WHERE i.name LIKE ? OR i.description LIKE ?';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    $sql .= ' ORDER BY i.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取商品資料: " . $e->getMessage());
}
?>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">衣櫥小舖</a>
            <div class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-item">歡迎, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="cart.php" class="nav-item">購物車</a>
                    <a href="logout.php" class="nav-item">登出</a>
                <?php else: ?>
                    <a href="login.php" class="nav-item">登入</a>
                    <a href="register.php" class="nav-item">註冊</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <div class="search-container">
            <form action="index.php" method="get">
                <input type="text" name="search" placeholder="搜尋商品..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <main class="product-grid">
            <?php if (empty($items)): ?>
                <p>目前沒有任何商品。</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <a href="view_item.php?id=<?php echo $item['id']; ?>" class="product-card-link">
                        <div class="product-card">
                            <?php if (!empty($item['image'])) : ?>
                                <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image-placeholder">無圖片</div>
                            <?php endif; ?>
                            <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                            <p class="category"><?php echo htmlspecialchars($item['category_name'] ?? '未分類'); ?></p>
                            <p class="description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>