<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 檢查是否已登入，並檢查權限
if (!isLoggedIn() || !isStaff()) {
    echo '<div class="alert alert-danger">您沒有權限查看此內容。</div>';
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo '<div class="alert alert-danger">無效的訂單 ID。</div>';
    exit();
}

// 獲取訂單主表資訊
$order_stmt = $conn->prepare("SELECT o.*, t.table_number FROM orders o JOIN tables t ON o.table_id = t.id WHERE o.id = :order_id");
$order_stmt->bindParam(':order_id', $order_id);
$order_stmt->execute();
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo '<div class="alert alert-warning">找不到該訂單。</div>';
    exit();
}

// 獲取訂單明細
$details_stmt = $conn->prepare("SELECT od.*, mi.name AS item_name FROM order_details od JOIN menu_items mi ON od.menu_item_id = mi.id WHERE od.order_id = :order_id");
$details_stmt->bindParam(':order_id', $order_id);
$details_stmt->execute();
$details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

// 獲取所有客製化選項及其選擇項 (用於顯示)
$options_stmt = $conn->query('SELECT * FROM customization_options ORDER BY id');
$customization_options_data = $options_stmt->fetchAll(PDO::FETCH_ASSOC);

$choices_by_id = [];
if (!empty($customization_options_data)) {
    $option_ids = implode(',', array_column($customization_options_data, 'id'));
    $choices_stmt = $conn->query("SELECT * FROM customization_choices WHERE option_id IN ($option_ids) ORDER BY option_id, id");
    $customization_choices_raw = $choices_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($customization_choices_raw as $choice) {
        $choices_by_id[$choice['id']] = $choice; // 方便通過 choice_id 查找
    }
}

// 獲取訂單明細的客製化選項
$order_details_ids = array_column($details, 'id');
$order_item_customizations = [];
if (!empty($order_details_ids)) {
    $order_details_ids_str = implode(',', $order_details_ids);
    $oic_stmt = $conn->query("SELECT * FROM order_item_customizations WHERE order_detail_id IN ($order_details_ids_str)");
    $oic_data = $oic_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($oic_data as $oic) {
        $order_item_customizations[$oic['order_detail_id']][] = $oic;
    }
}

?>
<p><strong>訂單 ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
<p><strong>桌號:</strong> <?php echo htmlspecialchars($order['table_number']); ?></p>
<p><strong>下單時間:</strong> <?php echo htmlspecialchars($order['order_time']); ?></p>
<p><strong>總金額:</strong> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
<p><strong>狀態:</strong> <?php echo htmlspecialchars(getOrderStatusText($order['status'])); ?></p>
<p><strong>支付狀態:</strong> <?php echo htmlspecialchars(getPaymentStatusText($order['payment_status'])); ?></p>

<h5>訂單明細:</h5>
<?php if ($details): ?>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>餐點名稱</th>
                <th>單價</th>
                <th>數量</th>
                <th>客製化選項</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>
                        <?php if (isset($order_item_customizations[$item['id']])): ?>
                            <ul class="list-unstyled mb-0 small">
                                <?php foreach ($order_item_customizations[$item['id']] as $oic): ?>
                                    <?php
                                    $choice = $choices_by_id[$oic['customization_choice_id']] ?? null;
                                    if ($choice) {
                                        $option_name = '';
                                        foreach ($customization_options_data as $opt) {
                                            if ($opt['id'] == $choice['option_id']) {
                                                $option_name = $opt['name'];
                                                break;
                                            }
                                        }
                                        $display_text = htmlspecialchars($choice['name']);
                                        if ($oic['price_at_order'] != 0) {
                                            $display_text .= ' (' . ($oic['price_at_order'] >= 0 ? '+' : '') . htmlspecialchars(number_format($oic['price_at_order'], 2)) . ')';
                                        }
                                        echo '<li>' . htmlspecialchars($option_name) . ': ' . $display_text . '</li>';
                                    }
                                    ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            無
                        <?php endif; ?>
                    </td>
                    <td>$<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>此訂單沒有明細。</p>
<?php endif; ?>

<?php if (isAdmin()): ?>
<div class="mt-4 text-end">
    <form action="orders.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此訂單嗎？此動作無法復原！');">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
        <button type="submit" name="delete_order" class="btn btn-danger">刪除訂單</button>
    </form>
</div>
<?php endif; ?>