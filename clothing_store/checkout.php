<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '結帳'; ?></title>
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

// 如果購物車是空的，導回購物車頁面
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';

// 在這裡生成 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

// 處理結帳表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '無效的請求，請重試。';
    } else {
        $customer_name = $_POST['customer_name'] ?? '';
        $customer_phone = $_POST['customer_phone'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $customer_address = $_POST['customer_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $total_amount = get_cart_total();
        $user_id = $_SESSION['user_id'] ?? null; // 如果有登入，記錄使用者ID

        if (empty($customer_name) || empty($customer_phone) || empty($customer_address)) {
            $error = '請填寫所有必填的顧客資訊。';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. 插入銷售訂單到 sales 表
                $stmt = $pdo->prepare(
                    'INSERT INTO sales (customer_id, sale_date, total_amount, notes) VALUES (?, NOW(), ?, ?)'
                );
                // 這裡簡化處理 customer_id，如果沒有登入，則為 NULL
                $stmt->execute([$user_id, $total_amount, $notes]);
                $sale_id = $pdo->lastInsertId();

                // 2. 插入銷售商品到 sale_items 表並更新庫存
                foreach ($_SESSION['cart'] as $variant_id => $item) {
                    $quantity = $item['quantity'];
                    $price_at_sale = $item['variant_info']['sell_price'];

                    // 檢查庫存是否足夠 (再次檢查，防止併發問題)
                    $stmt = $pdo->prepare('SELECT stock FROM item_variants WHERE id = ? FOR UPDATE'); // FOR UPDATE 鎖定行
                    $stmt->execute([$variant_id]);
                    $current_stock = $stmt->fetchColumn();

                    if ($current_stock < $quantity) {
                        $pdo->rollBack();
                        die('商品 ' . htmlspecialchars($item['variant_info']['item_name']) . ' 庫存不足。');
                    }

                    // 插入銷售商品
                    $stmt = $pdo->prepare(
                        'INSERT INTO sale_items (sale_id, item_variant_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->execute([$sale_id, $variant_id, $quantity, $price_at_sale]);

                    // 更新庫存
                    $stmt = $pdo->prepare('UPDATE item_variants SET stock = stock - ? WHERE id = ?');
                    $stmt->execute([$quantity, $variant_id]);
                }

                // 3. 清空購物車
                clear_cart();

                $pdo->commit();

                // 導向訂單確認頁面
                header('Location: order_confirmation.php?order_id=' . $sale_id);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                die("結帳失敗: " . $e->getMessage());
            }
        }
    }
}

$page_title = '結帳';
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
        <div class="checkout-container">
            <h1>結帳</h1>

            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <div class="order-summary">
                <h2>訂單摘要</h2>
                <table>
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>顏色</th>
                            <th>尺寸</th>
                            <th>單價</th>
                            <th>數量</th>
                            <th>小計</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $variant_id => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['variant_info']['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['variant_info']['color_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['variant_info']['size_name']); ?></td>
                                <td>NT$ <?php echo number_format($item['variant_info']['sell_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>NT$ <?php echo number_format($item['variant_info']['sell_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right">總計:</td>
                            <td>NT$ <?php echo number_format(get_cart_total(), 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="customer-info-form">
                <h2>顧客資訊</h2>
                <form action="checkout.php" method="post">
                    <div class="form-group">
                        <label for="customer_name">姓名 <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">電話 <span class="required">*</span></label>
                        <input type="text" id="customer_phone" name="customer_phone" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email</label>
                        <input type="email" id="customer_email" name="customer_email">
                    </div>
                    <div class="form-group">
                        <label for="customer_address">地址 <span class="required">*</span></label>
                        <textarea id="customer_address" name="customer_address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">備註</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <button type="submit" class="btn btn-success btn-full-width">確認訂單</button>
                </form>
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>