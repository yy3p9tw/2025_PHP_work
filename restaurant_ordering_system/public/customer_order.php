<?php
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

// 獲取所有餐點分類
$categories_stmt = $conn->query('SELECT * FROM categories ORDER BY id');
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// 獲取所有餐點
$menu_items_stmt = $conn->query('SELECT * FROM menu_items WHERE is_available = TRUE ORDER BY category_id, name');
$menu_items = $menu_items_stmt->fetchAll(PDO::FETCH_ASSOC);

// 將餐點按分類分組
$grouped_menu_items = [];
foreach ($menu_items as $item) {
    $grouped_menu_items[$item['category_id']][] = $item;
}

// 獲取所有客製化選項及其選擇項
$options_stmt = $conn->query('SELECT * FROM customization_options ORDER BY id');
$customization_options = $options_stmt->fetchAll(PDO::FETCH_ASSOC);

$choices_by_option = [];
if (!empty($customization_options)) {
    $option_ids = implode(',', array_column($customization_options, 'id'));
    $choices_stmt = $conn->query("SELECT * FROM customization_choices WHERE option_id IN ($option_ids) ORDER BY option_id, id");
    $customization_choices_data = $choices_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($customization_choices_data as $choice) {
        $choices_by_option[$choice['option_id']][] = $choice;
    }
}

// 獲取餐點與客製化選項的關聯
$menu_item_customization_map = [];
if (!empty($menu_items)) {
    $menu_item_ids = implode(',', array_column($menu_items, 'id'));
    $mic_stmt = $conn->query("SELECT menu_item_id, option_id FROM menu_item_customizations WHERE menu_item_id IN ($menu_item_ids)");
    $mic_data = $mic_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mic_data as $mic) {
        $menu_item_customization_map[$mic['menu_item_id']][] = $mic['option_id'];
    }
}

// 處理購物車 (使用 Session)
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 處理加入購物車請求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $selected_options = $_POST['custom_options'] ?? []; // 獲取選擇的客製化選項

    if ($quantity > 0) {
        // 構建唯一的購物車鍵，包含餐點ID和客製化選項
        // 將客製化選項排序後序列化，確保相同的選項組合有相同的鍵
        ksort($selected_options); // 確保順序一致
        $custom_options_json = json_encode($selected_options);
        $cart_item_key = $item_id . '_' . md5($custom_options_json); // 使用MD5確保鍵的長度可控

        // 獲取客製化選項的價格調整
        $customization_price_adjustment = 0;
        foreach ($selected_options as $option_id => $choice_id) {
            // 查找對應的 choice_id 的價格調整
            foreach ($customization_choices_data as $choice) {
                if ($choice['id'] == $choice_id) {
                    $customization_price_adjustment += $choice['price_adjustment'];
                    break;
                }
            }
        }

        // 將餐點和客製化選項加入購物車
        if (isset($_SESSION['cart'][$cart_item_key])) {
            $_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cart_item_key] = [
                'item_id' => $item_id,
                'quantity' => $quantity,
                'custom_options' => $selected_options,
                'customization_price_adjustment' => $customization_price_adjustment // 儲存客製化價格調整
            ];
        }
    }
    // 重定向以防止表單重複提交
    header('Location: customer_order.php?table_number=' . urlencode($table_number));
    exit();
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>點餐系統 - 菜單</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">桌號: <?php echo htmlspecialchars($table_number); ?> - 菜單</h1>

        <div class="d-flex justify-content-end mb-3">
            <a href="cart.php?table_number=<?php echo urlencode($table_number); ?>" class="btn btn-success">
                查看購物車 (<span id="cart-count"><?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>)
            </a>
        </div>

        <?php foreach ($categories as $category): ?>
            <h2 class="mt-5 mb-3">- <?php echo htmlspecialchars($category['name']); ?> -</h2>
            <div class="row">
                <?php if (isset($grouped_menu_items[$category['id']])): ?>
                    <?php foreach ($grouped_menu_items[$category['id']] as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($item['image_url']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                    <p class="card-text">基本價格: <strong class="item-base-price" data-price="<?php echo htmlspecialchars($item['price']); ?>">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></strong></p>
                                    
                                    <form action="customer_order.php?table_number=<?php echo urlencode($table_number); ?>" method="POST" class="mt-auto order-form" data-item-id="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">

                                        <?php
                                        // 顯示客製化選項
                                        $item_options_ids = $menu_item_customization_map[$item['id']] ?? [];
                                        if (!empty($item_options_ids)):
                                            foreach ($item_options_ids as $option_id):
                                                foreach ($customization_options as $option):
                                                    if ($option['id'] == $option_id):
                                        ?>
                                                        <div class="mb-3">
                                                            <label class="form-label"><strong><?php echo htmlspecialchars($option['name']); ?></strong> <?php echo $option['is_required'] ? '<span class="text-danger">*</span>' : ''; ?></label>
                                                            <?php if (isset($choices_by_option[$option['id']])): ?>
                                                                <?php foreach ($choices_by_option[$option['id']] as $choice): ?>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input custom-option-input" type="<?php echo htmlspecialchars($option['type']); ?>" name="custom_options[<?php echo $option['id']; ?>]" id="option_<?php echo $option['id']; ?>_choice_<?php echo $choice['id']; ?>" value="<?php echo $choice['id']; ?>" data-price-adjustment="<?php echo htmlspecialchars($choice['price_adjustment']); ?>" <?php echo $option['is_required'] && $option['type'] == 'radio' ? 'required' : ''; ?> <?php echo ($option['type'] == 'radio' && $choice['price_adjustment'] == 0) ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" for="option_<?php echo $option['id']; ?>_choice_<?php echo $choice['id']; ?>">
                                                                            <?php echo htmlspecialchars($choice['name']); ?>
                                                                            <?php if ($choice['price_adjustment'] != 0): ?>
                                                                                (<?php echo $choice['price_adjustment'] >= 0 ? '+' : ''; ?><?php echo htmlspecialchars(number_format($choice['price_adjustment'], 2)); ?>)
                                                                            <?php endif; ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <p class="text-muted">無可用選擇項。</p>
                                                            <?php endif; ?>
                                                        </div>
                                        <?php
                                                    endif;
                                                endforeach;
                                            endforeach;
                                        endif;
                                        ?>

                                        <div class="mb-3">
                                            <label for="quantity_<?php echo $item['id']; ?>" class="form-label">數量</label>
                                            <input type="number" name="quantity" class="form-control quantity-input" id="quantity_<?php echo $item['id']; ?>" value="1" min="1" max="99" required>
                                        </div>
                                        <p class="card-text">總計: <strong class="item-total-price">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></strong></p>
                                        <div class="d-grid">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary">加入購物車</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p>此分類暫無餐點。</p></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 更新購物車數量顯示
            const cartCountElement = document.getElementById('cart-count');
            // 這裡的數量是從 PHP session 讀取，所以不需要額外的 JS 邏輯來更新顯示

            // 計算餐點總價的邏輯
            document.querySelectorAll('.order-form').forEach(form => {
                const basePriceElement = form.querySelector('.item-base-price');
                const totalDisplayElement = form.querySelector('.item-total-price');
                const quantityInput = form.querySelector('.quantity-input');
                const customOptionInputs = form.querySelectorAll('.custom-option-input');

                function calculateTotalPrice() {
                    let basePrice = parseFloat(basePriceElement.dataset.price);
                    let quantity = parseInt(quantityInput.value);
                    let customizationAdjustment = 0;

                    customOptionInputs.forEach(input => {
                        if (input.type === 'radio' && input.checked) {
                            customizationAdjustment += parseFloat(input.dataset.priceAdjustment);
                        } else if (input.type === 'checkbox' && input.checked) {
                            customizationAdjustment += parseFloat(input.dataset.priceAdjustment);
                        }
                    });

                    let totalPrice = (basePrice + customizationAdjustment) * quantity;
                    totalDisplayElement.textContent = '$' + totalPrice.toFixed(2);
                }

                // 監聽數量和客製化選項的變化
                quantityInput.addEventListener('change', calculateTotalPrice);
                customOptionInputs.forEach(input => {
                    input.addEventListener('change', calculateTotalPrice);
                });

                // 初始化總價顯示
                calculateTotalPrice();
            });
        });
    </script>
</body>
</html>