<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$variant_id = $_GET['id'] ?? null;
if (!$variant_id) {
    header('Location: index.php');
    exit;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stock = $_POST['stock'];
    $min_stock = $_POST['min_stock'];
    $cost_price = $_POST['cost_price'];
    $sell_price = $_POST['sell_price'];
    $item_id = $_POST['item_id']; // 用於跳轉

    try {
        $stmt = $pdo->prepare(
            'UPDATE item_variants SET stock = ?, min_stock = ?, cost_price = ?, sell_price = ? WHERE id = ?'
        );
        $stmt->execute([$stock, $min_stock, $cost_price, $sell_price, $variant_id]);
        header('Location: manage_variants.php?item_id=' . $item_id);
        exit;
    } catch (PDOException $e) {
        die("更新規格失敗: " . $e->getMessage());
    }
}

// 獲取規格的現有資料
try {
    $stmt = $pdo->prepare(
       'SELECT iv.*, i.name as item_name, c.name as color_name, s.name as size_name 
        FROM item_variants iv 
        JOIN items i ON iv.item_id = i.id
        JOIN colors c ON iv.color_id = c.id 
        JOIN sizes s ON iv.size_id = s.id 
        WHERE iv.id = ?'
    );
    $stmt->execute([$variant_id]);
    $variant = $stmt->fetch();
    if (!$variant) {
        die('找不到該規格');
    }
} catch (PDOException $e) {
    die("無法讀取規格資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>編輯規格</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="container">
    <a href="manage_variants.php?item_id=<?php echo $variant['item_id']; ?>" class="btn">&larr; 回到庫存管理</a>
    <h1>編輯規格</h1>
    <h2><?php echo htmlspecialchars($variant['item_name']); ?></h2>
    
    <form action="edit_variant.php?id=<?php echo $variant_id; ?>" method="post">
        <input type="hidden" name="item_id" value="<?php echo $variant['item_id']; ?>">
        
        <div class="form-group-inline">
            <div class="form-group">
                <label>顏色</label>
                <input type="text" value="<?php echo htmlspecialchars($variant['color_name']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>尺寸</label>
                <input type="text" value="<?php echo htmlspecialchars($variant['size_name']); ?>" disabled>
            </div>
        </div>

        <div class="form-group-inline">
            <div class="form-group">
                <label for="cost_price">成本</label>
                <input type="number" id="cost_price" name="cost_price" step="0.01" value="<?php echo htmlspecialchars($variant['cost_price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="sell_price">售價</label>
                <input type="number" id="sell_price" name="sell_price" step="0.01" value="<?php echo htmlspecialchars($variant['sell_price']); ?>" required>
            </div>
        </div>

        <div class="form-group-inline">
            <div class="form-group">
                <label for="stock">庫存</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($variant['stock']); ?>" required>
            </div>
            <div class="form-group">
                <label for="min_stock">最低庫存</label>
                <input type="number" id="min_stock" name="min_stock" value="<?php echo htmlspecialchars($variant['min_stock']); ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success">更新規格</button>
    </form>
</div>
</body>
</html>
<style>
.form-group-inline {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}
.form-group-inline .form-group {
    flex: 1;
    margin-bottom: 0;
}
input:disabled {
    background-color: #e9ecef;
    opacity: 1;
}
</style>