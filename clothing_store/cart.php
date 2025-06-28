<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '購物車'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/cart_functions.php';
require_once 'includes/csrf_functions.php';

$pdo = get_pdo();

// 在這裡生成 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

// 處理更新數量或移除商品
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: cart.php?error=invalid_csrf');
        exit;
    }

    if (isset($_POST['update_quantity'])) {
        foreach ($_POST['quantities'] as $variant_id => $quantity) {
            update_cart_quantity($variant_id, (int)$quantity);
        }
    } elseif (isset($_POST['remove_item'])) {
        $variant_id_to_remove = $_POST['remove_item'];
        remove_from_cart($variant_id_to_remove);
    }
    header('Location: cart.php');
    exit;
}

$page_title = '購物車';
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
        <div class="cart-container">
            <h1>我的購物車</h1>

            <?php if (empty($_SESSION['cart'])): ?>
                <p>您的購物車是空的。</p>
                <p><a href="index.php">繼續購物</a></p>
            <?php else: ?>
                <form action="cart.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>顏色</th>
                                <th>尺寸</th>
                                <th>單價</th>
                                <th>數量</th>
                                <th>小計</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $variant_id => $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['variant_info']['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['variant_info']['color_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['variant_info']['size_name']); ?></td>
                                    <td>NT$ <?php echo number_format($item['variant_info']['sell_price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantities[<?php echo $variant_id; ?>]" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="quantity-input">
                                    </td>
                                    <td>NT$ <?php echo number_format($item['variant_info']['sell_price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <button type="submit" name="remove_item" value="<?php echo $variant_id; ?>" class="btn btn-danger btn-small">移除</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right">總計:</td>
                                <td>NT$ <?php echo number_format(get_cart_total(), 2); ?></td>
                                <td>
                                    <button type="submit" name="update_quantity" class="btn btn-primary">更新數量</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="cart-actions">
                        <a href="index.php" class="btn">繼續購物</a>
                        <button type="button" onclick="location.href='checkout.php'" class="btn btn-success">前往結帳</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>