<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$error = '';
$success = '';

// CSRF Token
require_once '../includes/csrf_functions.php';
$csrf_token = get_csrf_token();

// 獲取所有顧客 (用於選擇現有顧客)
try {
    $customers = $pdo->query('SELECT id, name, phone, email, address FROM customers ORDER BY name ASC')->fetchAll();
} catch (PDOException $e) {
    die("無法讀取顧客資料: " . $e->getMessage());
}

// 獲取所有商品規格 (用於選擇商品)
try {
    $stmt = $pdo->query(
       'SELECT iv.id, i.name as item_name, c.name as color_name, s.name as size_name, iv.sell_price, iv.stock 
        FROM item_variants iv 
        JOIN items i ON iv.item_id = i.id 
        JOIN colors c ON iv.color_id = c.id 
        JOIN sizes s ON iv.size_id = s.id 
        ORDER BY i.name, c.name, s.name'
    );
    $all_variants = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取商品規格資料: " . $e->getMessage());
}

// 獲取訂單現有資料
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
       'SELECT si.*, i.name as item_name, c.name as color_name, s.name as size_name, iv.stock as current_stock_in_db 
        FROM sale_items si 
        JOIN item_variants iv ON si.item_variant_id = iv.id 
        JOIN items i ON iv.item_id = i.id 
        JOIN colors c ON iv.color_id = c.id 
        JOIN sizes s ON iv.size_id = s.id 
        WHERE si.sale_id = ?'
    );
    $stmt->execute([$order_id]);
    $order_items_from_db = $stmt->fetchAll();

    $initial_order_items = [];
    foreach ($order_items_from_db as $item) {
        $initial_order_items[$item['item_variant_id']] = [
            'name' => $item['item_name'] . ' - ' . $item['color_name'] . ' - ' . $item['size_name'],
            'price' => (float)$item['price_at_sale'],
            'quantity' => $item['quantity'],
            'maxStock' => $item['current_stock_in_db'] + $item['quantity']
        ];
    }

} catch (PDOException $e) {
    die("無法讀取訂單商品資料: " . $e->getMessage());
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }
    $customer_id = $_POST['customer_id'] ?? null;
    $new_customer_name = $_POST['new_customer_name'] ?? '';
    $new_customer_phone = $_POST['new_customer_phone'] ?? '';
    $new_customer_email = $_POST['new_customer_email'] ?? '';
    $new_customer_address = $_POST['new_customer_address'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $selected_variants = $_POST['variants'] ?? [];

    try {
        $pdo->beginTransaction();

        if ($customer_id === 'new') {
            if (empty($new_customer_name) || empty($new_customer_phone) || empty($new_customer_address)) {
                throw new Exception('請填寫新顧客的姓名、電話和地址。');
            }
            $stmt = $pdo->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$new_customer_name, $new_customer_phone, $new_customer_email, $new_customer_address]);
            $customer_id = $pdo->lastInsertId();
        } elseif (empty($customer_id)) {
            $customer_id = null;
        }

        $total_amount = 0;
        $variants_to_update_stock = [];

        // 遍歷資料庫中原始訂單的商品，先將庫存全部歸還
        foreach ($order_items_from_db as $original_item) {
            $variants_to_update_stock[$original_item['item_variant_id']] = - (int)$original_item['quantity'];
        }

        $updated_order_items_data = [];
        // 遍歷新提交的商品，計算總金額並準備更新庫存
        foreach ($selected_variants as $variant_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) continue;

            $stmt = $pdo->prepare('SELECT sell_price, stock FROM item_variants WHERE id = ? FOR UPDATE');
            $stmt->execute([$variant_id]);
            $variant_data = $stmt->fetch();

            if (!$variant_data) throw new Exception('商品規格不存在。');

            $available_stock = $variant_data['stock'] + ($variants_to_update_stock[$variant_id] ?? 0) * -1; // 可用庫存 = DB庫存 + 已歸還的庫存
            if ($available_stock < $quantity) throw new Exception('商品庫存不足。');

            $total_amount += $variant_data['sell_price'] * $quantity;
            $updated_order_items_data[] = ['variant_id' => $variant_id, 'quantity' => $quantity, 'price_at_sale' => $variant_data['sell_price']];
            $variants_to_update_stock[$variant_id] = ($variants_to_update_stock[$variant_id] ?? 0) + $quantity;
        }

        if (empty($updated_order_items_data)) throw new Exception('請至少選擇一項商品。');

        $stmt = $pdo->prepare('UPDATE sales SET customer_id = ?, total_amount = ?, notes = ? WHERE id = ?');
        $stmt->execute([$customer_id, $total_amount, $notes, $order_id]);

        $stmt = $pdo->prepare('DELETE FROM sale_items WHERE sale_id = ?');
        $stmt->execute([$order_id]);

        foreach ($updated_order_items_data as $item) {
            $stmt = $pdo->prepare('INSERT INTO sale_items (sale_id, item_variant_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)');
            $stmt->execute([$order_id, $item['variant_id'], $item['quantity'], $item['price_at_sale']]);
        }

        foreach ($variants_to_update_stock as $variant_id => $change) {
            if ($change != 0) {
                $stmt = $pdo->prepare('UPDATE item_variants SET stock = stock - ? WHERE id = ?');
                $stmt->execute([$change, $variant_id]);
            }
        }

        $pdo->commit();
        header('Location: orders.php?success=1');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = '更新訂單失敗: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 編輯訂單</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <div class="container">
        <h1>編輯訂單 #<?php echo htmlspecialchars($order['id']); ?></h1>

        <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><p class="success-message">訂單更新成功！</p><?php endif; ?>

        <form action="edit_order.php?id=<?php echo $order_id; ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="customer_id">顧客</label>
                <select id="customer_id" name="customer_id" onchange="toggleNewCustomerFields()">
                    <option value="">-- 訪客訂單 --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo ($order['customer_id'] == $customer['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($customer['name']); ?> (<?php echo htmlspecialchars($customer['phone']); ?>)</option>
                    <?php endforeach; ?>
                    <option value="new">-- 新增顧客 --</option>
                </select>
            </div>

            <div id="new_customer_fields" style="display: none; border: 1px solid #eee; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <h4>新增顧客資訊</h4>
                <div class="form-group">
                    <label for="new_customer_name">姓名 <span class="required">*</span></label>
                    <input type="text" id="new_customer_name" name="new_customer_name">
                </div>
                <div class="form-group">
                    <label for="new_customer_phone">電話 <span class="required">*</span></label>
                    <input type="text" id="new_customer_phone" name="new_customer_phone">
                </div>
                <div class="form-group">
                    <label for="new_customer_email">Email</label>
                    <input type="email" id="new_customer_email" name="new_customer_email">
                </div>
                <div class="form-group">
                    <label for="new_customer_address">地址 <span class="required">*</span></label>
                    <textarea id="new_customer_address" name="new_customer_address" rows="3"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>訂購商品</label>
                <div id="order_items_list" style="border: 1px solid #eee; padding: 15px; border-radius: 5px; min-height: 100px;">
                    <!-- JS會填充這裡 -->
                </div>
                <button type="button" class="btn" onclick="showAddProductModal()" style="margin-top: 10px;">新增/修改商品</button>
            </div>

            <div class="form-group">
                <label for="notes">訂單備註</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">更新訂單</button>
            <a href="orders.php" class="btn">取消</a>
        </form>
    </div>

    <!-- 新增商品 Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="hideAddProductModal()">&times;</span>
            <h2>選擇商品</h2>
            <input type="text" id="product_search" onkeyup="filterProducts()" placeholder="搜尋商品..." style="width: 100%; padding: 8px; margin-bottom: 10px;">
            <div class="product-selection-list">
                <?php foreach ($all_variants as $variant): ?>
                    <div class="product-item" data-id="<?php echo $variant['id']; ?>">
                        <span><?php echo htmlspecialchars($variant['item_name'] . ' - ' . $variant['color_name'] . ' - ' . $variant['size_name']); ?> (庫存: <span id="stock-<?php echo $variant['id']; ?>"><?php echo $variant['stock']; ?></span>)</span>
                        <div>
                            <input type="number" id="quantity-<?php echo $variant['id']; ?>" min="0" value="0" style="width: 60px; text-align: center;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-primary" onclick="applySelectedProducts()" style="margin-top: 10px;">確定</button>
        </div>
    </div>

<script>
let orderItems = <?php echo json_encode($initial_order_items); ?>;
const allVariants = <?php echo json_encode($all_variants); ?>;

function toggleNewCustomerFields() {
    const customerIdSelect = document.getElementById('customer_id');
    const newCustomerFields = document.getElementById('new_customer_fields');
    const isNew = customerIdSelect.value === 'new';
    newCustomerFields.style.display = isNew ? 'block' : 'none';
    newCustomerFields.querySelectorAll('input, textarea').forEach(input => input.required = isNew);
}

function showAddProductModal() {
    // 當打開 modal 時，根據 orderItems 更新 modal 中的數量和顯示的庫存
    allVariants.forEach(variant => {
        const variantId = variant.id;
        const orderItem = orderItems[variantId];
        const dbStock = parseInt(variant.stock);
        const originalOrderQuantity = <?php echo json_encode(array_column($order_items_from_db, 'quantity', 'item_variant_id')); ?>[variantId] || 0;
        
        let displayStock = dbStock;
        let quantityInOrder = 0;

        if(orderItem) { // 如果當前編輯的訂單已有此商品
            quantityInOrder = orderItem.quantity;
            displayStock = dbStock + originalOrderQuantity; // 可用庫存 = 資料庫現有庫存 + 此訂單原始佔用的庫存
        }

        document.getElementById(`quantity-${variantId}`).value = quantityInOrder;
        document.getElementById(`stock-${variantId}`).textContent = displayStock;
        document.getElementById(`quantity-${variantId}`).max = displayStock;
    });
    document.getElementById('addProductModal').style.display = 'block';
}

function hideAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
}

function applySelectedProducts() {
    orderItems = {}; // 清空現有訂單
    document.querySelectorAll('.product-item').forEach(item => {
        const variantId = item.dataset.id;
        const quantity = parseInt(document.getElementById(`quantity-${variantId}`).value);
        if (quantity > 0) {
            const variantData = allVariants.find(v => v.id == variantId);
            orderItems[variantId] = {
                name: variantData.item_name + ' - ' + variantData.color_name + ' - ' + variantData.size_name,
                price: parseFloat(variantData.sell_price),
                quantity: quantity,
                maxStock: parseInt(document.getElementById(`stock-${variantId}`).textContent)
            };
        }
    });
    updateOrderItemsList();
    hideAddProductModal();
}

function updateOrderItemsList() {
    const listDiv = document.getElementById('order_items_list');
    listDiv.innerHTML = '';
    if (Object.keys(orderItems).length === 0) {
        listDiv.innerHTML = '<p>尚未選擇任何商品。</p>';
        return;
    }

    const table = document.createElement('table');
    table.innerHTML = `<thead><tr><th>商品</th><th>單價</th><th>數量</th><th>小計</th></tr></thead><tbody></tbody>`;
    const tbody = table.querySelector('tbody');

    for (const variantId in orderItems) {
        const item = orderItems[variantId];
        const subtotal = item.price * item.quantity;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>NT$ ${item.price.toFixed(2)}</td>
            <td>${item.quantity}</td>
            <td>NT$ ${subtotal.toFixed(2)}</td>
        `;
        tbody.appendChild(row);
        // 為表單提交創建隱藏的 input
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `variants[${variantId}]`;
        hiddenInput.value = item.quantity;
        listDiv.appendChild(hiddenInput);
    }
    listDiv.insertBefore(table, listDiv.firstChild);
}

function filterProducts() {
    const searchInput = document.getElementById('product_search').value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(searchInput) ? 'flex' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    toggleNewCustomerFields();
    updateOrderItemsList();
});
</script>
</body>
</html>
