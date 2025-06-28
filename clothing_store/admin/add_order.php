<?php
session_start(); // 確保 session 已經啟動
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';
$pdo = get_pdo();

$error = '';
$success = '';

// 確保 CSRF Token 存在於 session 中
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token']; // 將 session 中的 token 賦值給變數

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

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '無效的請求，請重試。';
    } else {
        $customer_id = $_POST['customer_id'] ?? null;
        $new_customer_name = $_POST['new_customer_name'] ?? '';
        $new_customer_phone = $_POST['new_customer_phone'] ?? '';
        $new_customer_email = $_POST['new_customer_email'] ?? '';
        $new_customer_address = $_POST['new_customer_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $selected_variants = $_POST['variants'] ?? []; // 選擇的商品規格ID和數量

    try {
        $pdo->beginTransaction();

        // 處理顧客資訊
        if ($customer_id === 'new') {
            // 新增顧客
            if (empty($new_customer_name) || empty($new_customer_phone) || empty($new_customer_address)) {
                throw new Exception('請填寫新顧客的姓名、電話和地址。');
            }
            $stmt = $pdo->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$new_customer_name, $new_customer_phone, $new_customer_email, $new_customer_address]);
            $customer_id = $pdo->lastInsertId();
        } else if (empty($customer_id)) {
            $customer_id = null; // 訪客訂單
        }

        // 計算總金額並檢查庫存
        $total_amount = 0;
        $order_items_data = [];
        foreach ($selected_variants as $variant_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) continue;

            $stmt = $pdo->prepare('SELECT sell_price, stock FROM item_variants WHERE id = ? FOR UPDATE');
            $stmt->execute([$variant_id]);
            $variant_data = $stmt->fetch();

            if (!$variant_data || $variant_data['stock'] < $quantity) {
                throw new Exception('商品庫存不足或規格不存在。');
            }
            $total_amount += $variant_data['sell_price'] * $quantity;
            $order_items_data[] = [
                'variant_id' => $variant_id,
                'quantity' => $quantity,
                'price_at_sale' => $variant_data['sell_price']
            ];
        }

        if (empty($order_items_data)) {
            throw new Exception('請至少選擇一項商品。');
        }

        // 插入銷售訂單
        $stmt = $pdo->prepare('INSERT INTO sales (customer_id, sale_date, total_amount, notes) VALUES (?, NOW(), ?, ?)');
        $stmt->execute([$customer_id, $total_amount, $notes]);
        $sale_id = $pdo->lastInsertId();

        // 插入銷售商品並更新庫存
        foreach ($order_items_data as $item) {
            $stmt = $pdo->prepare('INSERT INTO sale_items (sale_id, item_variant_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)');
            $stmt->execute([$sale_id, $item['variant_id'], $item['quantity'], $item['price_at_sale']]);

            $stmt = $pdo->prepare('UPDATE item_variants SET stock = stock - ? WHERE id = ?');
            $stmt->execute([$item['quantity'], $item['variant_id']]);
        }

        $pdo->commit();
        $success = '訂單新增成功！訂單編號: ' . $sale_id;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = '新增訂單失敗: ' . $e->getMessage();
    }
}
}

$page_title = '新增訂單';
require_once '../includes/header.php';
?>

<div class="container">
    <a href="orders.php" class="btn">&larr; 回到訂單列表</a>
    <h1>新增訂單</h1>

    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>

    <form action="add_order.php" method="post">
        <div class="section">
            <h2>顧客資訊</h2>
            <div class="form-group">
                <label for="customer_id">選擇現有顧客:</label>
                <select id="customer_id" name="customer_id" onchange="toggleNewCustomerFields()">
                    <option value="">-- 訪客訂單 --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?> (<?php echo htmlspecialchars($customer['phone']); ?>)</option>
                    <?php endforeach; ?>
                    <option value="new">-- 新增顧客 --</option>
                </select>
            </div>

            <div id="new_customer_fields" style="display: none;">
                <h3>新增顧客資訊</h3>
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
        </div>

        <div class="section">
            <h2>訂購商品</h2>
            <div id="order_items_list">
                <!-- 商品會動態新增到這裡 -->
                <p>尚未選擇任何商品。</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="showAddProductModal()">新增商品</button>
        </div>

        <div class="section">
            <h2>訂單備註</h2>
            <div class="form-group">
                <label for="notes">備註:</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <button type="submit" class="btn btn-success btn-full-width">建立訂單</button>
    </form>
</div>

<!-- 新增商品 Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="hideAddProductModal()">&times;</span>
        <h2>選擇商品</h2>
        <div class="form-group">
            <label for="product_search">搜尋商品:</label>
            <input type="text" id="product_search" onkeyup="filterProducts()" placeholder="輸入商品名稱、顏色或尺寸">
        </div>
        <div class="product-selection-list">
            <?php foreach ($all_variants as $variant): ?>
                <div class="product-item" data-id="<?php echo $variant['id']; ?>" data-name="<?php echo htmlspecialchars($variant['item_name'] . ' - ' . $variant['color_name'] . ' - ' . $variant['size_name']); ?>" data-price="<?php echo $variant['sell_price']; ?>" data-stock="<?php echo $variant['stock']; ?>">
                    <span><?php echo htmlspecialchars($variant['item_name']); ?> - <?php echo htmlspecialchars($variant['color_name']); ?> - <?php echo htmlspecialchars($variant['size_name']); ?></span>
                    <span>NT$ <?php echo number_format($variant['sell_price'], 2); ?> (庫存: <?php echo $variant['stock']; ?>)</span>
                    <input type="number" class="variant-quantity" value="1" min="1" max="<?php echo $variant['stock']; ?>" <?php echo ($variant['stock'] <= 0) ? 'disabled' : ''; ?>>
                    <button type="button" class="btn btn-primary btn-small" onclick="addVariantToOrder(this)" <?php echo ($variant['stock'] <= 0) ? 'disabled' : ''; ?>>加入</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let orderItems = {}; // 儲存已選擇的商品

function toggleNewCustomerFields() {
    const customerIdSelect = document.getElementById('customer_id');
    const newCustomerFields = document.getElementById('new_customer_fields');
    if (customerIdSelect.value === 'new') {
        newCustomerFields.style.display = 'block';
        newCustomerFields.querySelectorAll('input, textarea').forEach(input => input.required = true);
    } else {
        newCustomerFields.style.display = 'none';
        newCustomerFields.querySelectorAll('input, textarea').forEach(input => input.required = false);
    }
}

function showAddProductModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

function hideAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
}

function filterProducts() {
    const searchInput = document.getElementById('product_search').value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.dataset.name.toLowerCase();
        if (name.includes(searchInput)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function addVariantToOrder(button) {
    const itemDiv = button.closest('.product-item');
    const variantId = itemDiv.dataset.id;
    const variantName = itemDiv.dataset.name;
    const variantPrice = parseFloat(itemDiv.dataset.price);
    const quantityInput = itemDiv.querySelector('.variant-quantity');
    let quantity = parseInt(quantityInput.value);
    const maxStock = parseInt(quantityInput.max);

    if (quantity <= 0 || isNaN(quantity)) {
        alert('數量必須大於 0。');
        return;
    }
    if (quantity > maxStock) {
        alert(`庫存不足，最多只能選擇 ${maxStock} 件。`);
        quantity = maxStock; // 調整數量為最大庫存
        quantityInput.value = maxStock;
    }

    if (orderItems[variantId]) {
        // 如果已存在，更新數量
        orderItems[variantId].quantity += quantity;
        if (orderItems[variantId].quantity > maxStock) {
            alert(`總數量不能超過庫存 ${maxStock} 件。`);
            orderItems[variantId].quantity = maxStock;
        }
    } else {
        // 新增項目
        orderItems[variantId] = {
            name: variantName,
            price: variantPrice,
            quantity: quantity,
            maxStock: maxStock // 記錄最大庫存
        };
    }
    updateOrderItemsList();
    hideAddProductModal();
}

function updateOrderItemsList() {
    const listDiv = document.getElementById('order_items_list');
    listDiv.innerHTML = '';
    let totalOrderAmount = 0;

    if (Object.keys(orderItems).length === 0) {
        listDiv.innerHTML = '<p>尚未選擇任何商品。</p>';
        return;
    }

    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>商品</th>
                <th>單價</th>
                <th>數量</th>
                <th>小計</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">總計:</td>
                <td id="total_order_amount"></td>
                <td></td>
            </tr>
        </tfoot>
    `;
    const tbody = table.querySelector('tbody');

    for (const variantId in orderItems) {
        const item = orderItems[variantId];
        const subtotal = item.price * item.quantity;
        totalOrderAmount += subtotal;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>NT$ ${item.price.toFixed(2)}</td>
            <td>
                <input type="number" name="variants[${variantId}]" value="${item.quantity}" min="1" max="${item.maxStock}" onchange="updateOrderItemQuantity(${variantId}, this.value)">
            </td>
            <td>NT$ ${subtotal.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-small" onclick="removeOrderItem(${variantId})">移除</button>
            </td>
        `;
        tbody.appendChild(row);
    }
    listDiv.appendChild(table);
    document.getElementById('total_order_amount').textContent = `NT$ ${totalOrderAmount.toFixed(2)}`;
}

function updateOrderItemQuantity(variantId, newQuantity) {
    let quantity = parseInt(newQuantity);
    if (isNaN(quantity) || quantity <= 0) {
        alert('數量必須大於 0。');
        quantity = 1; // 預設為1
    }
    if (quantity > orderItems[variantId].maxStock) {
        alert(`數量不能超過庫存 ${orderItems[variantId].maxStock} 件。`);
        quantity = orderItems[variantId].maxStock;
    }
    orderItems[variantId].quantity = quantity;
    updateOrderItemsList();
}

function removeOrderItem(variantId) {
    delete orderItems[variantId];
    updateOrderItemsList();
}

// 初始化時執行一次，以處理預設選中的顧客類型
document.addEventListener('DOMContentLoaded', toggleNewCustomerFields);
</script>

<style>
/* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1001; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 800px;
    border-radius: 8px;
    position: relative;
}

.close-button {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-button:hover,
.close-button:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.product-selection-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #eee;
    padding: 10px;
    margin-top: 15px;
}

.product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.product-item:last-child {
    border-bottom: none;
}

.product-item span:first-child {
    flex-grow: 1;
    margin-right: 10px;
}

.product-item .variant-quantity {
    width: 60px;
    text-align: center;
    margin-right: 10px;
}

.section {
    background-color: #fdfdfd;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}

.section h2 {
    color: #34495e;
    margin-top: 0;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.form-group .required {
    color: #dc3545;
    margin-left: 5px;
}
</style>