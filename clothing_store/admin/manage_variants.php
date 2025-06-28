<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
$pdo = get_pdo();

$item_id = $_GET['item_id'] ?? null;
if (!$item_id) {
    header('Location: index.php');
    exit;
}

// 處理新增規格的表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    $color_id = $_POST['color_id'];
    $size_id = $_POST['size_id'];
    $stock = $_POST['stock'];
    $min_stock = $_POST['min_stock'];
    $cost_price = $_POST['cost_price'];
    $sell_price = $_POST['sell_price'];

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO item_variants (item_id, color_id, size_id, stock, min_stock, cost_price, sell_price) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$item_id, $color_id, $size_id, $stock, $min_stock, $cost_price, $sell_price]);
        header('Location: manage_variants.php?item_id=' . $item_id);
        exit;
    } catch (PDOException $e) {
        // 捕獲重複鍵值的錯誤
        if ($e->getCode() == 23000) {
            die('新增失敗：該顏色和尺寸的組合已經存在。');
        } else {
            die("新增規格失敗: " . $e->getMessage());
        }
    }
}

// 獲取商品資訊
try {
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
} catch (PDOException $e) {
    die("無法讀取商品資料: " . $e->getMessage());
}

// 獲取該商品的所有規格
try {
    $stmt = $pdo->prepare(
       'SELECT iv.*, c.name as color_name, s.name as size_name 
        FROM item_variants iv 
        JOIN colors c ON iv.color_id = c.id 
        JOIN sizes s ON iv.size_id = s.id 
        WHERE iv.item_id = ? 
        ORDER BY c.name, s.name'
    );
    $stmt->execute([$item_id]);
    $variants = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取規格資料: " . $e->getMessage());
}

// 獲取所有顏色和尺寸用於下拉選單
$colors = $pdo->query('SELECT * FROM colors ORDER BY name')->fetchAll();
$sizes = $pdo->query('SELECT * FROM sizes ORDER BY name')->fetchAll();

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理庫存 - <?php echo htmlspecialchars($item['name']); ?></title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="container">
    <a href="index.php" class="btn">&larr; 回到商品列表</a>
    <h1>管理庫存</h1>
    <h2><?php echo htmlspecialchars($item['name']); ?></h2>

    <div class="section">
        <h3>現有規格</h3>
        <table>
            <thead>
            <tr>
                <th>顏色</th>
                <th>尺寸</th>
                <th>成本</th>
                <th>售價</th>
                <th>庫存</th>
                <th>最低庫存</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($variants)): ?>
                <tr><td colspan="7">尚未設定任何規格。</td></tr>
            <?php else: ?>
                <?php foreach ($variants as $variant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($variant['color_name']); ?></td>
                        <td><?php echo htmlspecialchars($variant['size_name']); ?></td>
                        <td><?php echo htmlspecialchars($variant['cost_price']); ?></td>
                        <td><?php echo htmlspecialchars($variant['sell_price']); ?></td>
                        <td><?php echo htmlspecialchars($variant['stock']); ?></td>
                        <td><?php echo htmlspecialchars($variant['min_stock']); ?></td>
                        <td>
                            <a href="edit_variant.php?id=<?php echo $variant['id']; ?>" class="btn">編輯</a>
                            <form action="delete_variant.php" method="post" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo $variant['id']; ?>">
                                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('確定刪除此規格嗎？');">刪除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>新增規格</h3>
        <form action="manage_variants.php?item_id=<?php echo $item_id; ?>" method="post">
            <input type="hidden" name="add_variant" value="1">
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="color_id">顏色</label>
                    <select id="color_id" name="color_id" required>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?php echo $color['id']; ?>"><?php echo htmlspecialchars($color['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="size_id">尺寸</label>
                    <select id="size_id" name="size_id" required>
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?php echo $size['id']; ?>"><?php echo htmlspecialchars($size['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="cost_price">成本</label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="sell_price">售價</label>
                    <input type="number" id="sell_price" name="sell_price" step="0.01" required>
                </div>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="stock">庫存</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                <div class="form-group">
                    <label for="min_stock">最低庫存</label>
                    <input type="number" id="min_stock" name="min_stock" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success">新增規格</button>
        </form>
    </div>
</div>
</body>
</html>