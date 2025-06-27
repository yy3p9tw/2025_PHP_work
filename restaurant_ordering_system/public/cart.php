<?php
session_start();
require_once '../includes/db.php';

$database = new Database();
$conn = $database->getConnection();

$table_number = $_GET['table_number'] ?? '';

// 驗證桌號是否存在於資料庫
$stmt = $conn->prepare('SELECT id FROM tables WHERE table_number = :table_number');
$stmt->bindParam(':table_number', $table_number);
$stmt->execute();
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    // 如果桌號不存在，導回首頁或顯示錯誤訊息
    header('Location: index.php?error=invalid_table');
    exit();
}

$table_id = $table['id'];

// 獲取所有客製化選項及其選擇項 (用於顯示)
$options_stmt = $conn->query('SELECT * FROM customization_options ORDER BY id');
$customization_options_data = $options_stmt->fetchAll(PDO::FETCH_ASSOC);

$choices_by_option_id = [];
if (!empty($customization_options_data)) {
    $option_ids = implode(',', array_column($customization_options_data, 'id'));
    $choices_stmt = $conn->query("SELECT * FROM customization_choices WHERE option_id IN ($option_ids) ORDER BY option_id, id");
    $customization_choices_data = $choices_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($customization_choices_data as $choice) {
        $choices_by_option_id[$choice['option_id']][$choice['id']] = $choice; // 方便通過 option_id 和 choice_id 查找
    }
}

// 處理購物車更新或移除請求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        $cart_item_key = $_POST['cart_item_key'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $_SESSION['cart'][$cart_item_key]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$cart_item_key]); // 數量為0則移除
        }
    } elseif (isset($_POST['remove_item'])) {
        $cart_item_key = $_POST['cart_item_key'];
        unset($_SESSION['cart'][$cart_item_key]);
    }
    // 重定向以防止表單重複提交
    header('Location: cart.php?table_number=' . urlencode($table_number));
    exit();
}

$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $menu_item_ids_in_cart = [];
    foreach ($_SESSION['cart'] as $cart_item_key => $cart_item) {
        $menu_item_ids_in_cart[] = $cart_item['item_id'];
    }

    if (!empty($menu_item_ids_in_cart)) {
        $menu_item_ids_str = implode(',', array_unique($menu_item_ids_in_cart));
        $stmt = $conn->query("SELECT id, name, price, image_url FROM menu_items WHERE id IN ($menu_item_ids_str)");
        $raw_menu_items_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $menu_items_data = [];
        foreach ($raw_menu_items_data as $item) {
            $menu_items_data[$item['id']] = $item;
        }
    }

    foreach ($_SESSION['cart'] as $cart_item_key => $cart_item) {
        $item_id = $cart_item['item_id'];
        $quantity = $cart_item['quantity'];
        $custom_options = $cart_item['custom_options'] ?? [];
        $customization_price_adjustment = $cart_item['customization_price_adjustment'] ?? 0;

        $menu_item = $menu_items_data[$item_id] ?? null; // 獲取餐點基本資訊

        if ($menu_item) {
            $base_price = $menu_item['price'];
            $item_total_price = ($base_price + $customization_price_adjustment) * $quantity;
            $total_amount += $item_total_price;

            $custom_options_display = [];
            foreach ($custom_options as $option_id => $choice_id) {
                $option_name = '';
                foreach ($customization_options_data as $opt) {
                    if ($opt['id'] == $option_id) {
                        $option_name = $opt['name'];
                        break;
                    }
                }
                $choice_name = $choices_by_option_id[$option_id][$choice_id]['name'] ?? '';
                $choice_price_adj = $choices_by_option_id[$option_id][$choice_id]['price_adjustment'] ?? 0;

                $display_text = htmlspecialchars($choice_name);
                if ($choice_price_adj != 0) {
                    $display_text .= ' (' . ($choice_price_adj >= 0 ? '+' : '') . htmlspecialchars(number_format($choice_price_adj, 2)) . ')';
                }
                $custom_options_display[] = htmlspecialchars($option_name) . ': ' . $display_text;
            }

            $cart_items[] = [
                'cart_item_key' => $cart_item_key,
                'id' => $item_id,
                'name' => $menu_item['name'],
                'price' => $base_price,
                'image_url' => $menu_item['image_url'],
                'quantity' => $quantity,
                'custom_options_display' => $custom_options_display,
                'customization_price_adjustment' => $customization_price_adjustment,
                'subtotal' => $item_total_price
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購物車 - 桌號: <?php echo htmlspecialchars($table_number); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">桌號: <?php echo htmlspecialchars($table_number); ?> - 購物車</h1>

        <div class="d-flex justify-content-end mb-3">
            <a href="customer_order.php?table_number=<?php echo urlencode($table_number); ?>" class="btn btn-secondary">繼續點餐</a>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info text-center" role="alert">
                您的購物車是空的！
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>圖片</th>
                            <th>餐點</th>
                            <th>單價</th>
                            <th>客製化</th>
                            <th>數量</th>
                            <th>小計</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image_url']): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/60" alt="無圖片" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                <td>
                                    <?php if (!empty($item['custom_options_display'])): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($item['custom_options_display'] as $option_text): ?>
                                                <li><?php echo $option_text; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        無
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="cart.php?table_number=<?php echo urlencode($table_number); ?>" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="cart_item_key" value="<?php echo htmlspecialchars($item['cart_item_key']); ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" name="update_cart" class="btn btn-sm btn-outline-primary ms-2">更新</button>
                                    </form>
                                </td>
                                <td>$<?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                <td>
                                    <form action="cart.php?table_number=<?php echo urlencode($table_number); ?>" method="POST">
                                        <input type="hidden" name="cart_item_key" value="<?php echo htmlspecialchars($item['cart_item_key']); ?>">
                                        <button type="submit" name="remove_item" class="btn btn-sm btn-danger">移除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">總金額:</th>
                            <th colspan="2">$<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-center mt-4">
                <form action="process_order.php" method="POST">
                    <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                    <input type="hidden" name="table_number" value="<?php echo htmlspecialchars($table_number); ?>">
                    <button type="submit" class="btn btn-primary btn-lg" <?php echo empty($cart_items) ? 'disabled' : ''; ?>>送出訂單</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>