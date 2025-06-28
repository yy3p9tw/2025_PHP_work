<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '商品詳情'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/csrf_functions.php';
$pdo = get_pdo();

$item_id = $_GET['id'] ?? null;
if (!$item_id) {
    header('Location: index.php');
    exit;
}

// 在這裡生成 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

// 獲取商品基本資訊
try {
    $stmt = $pdo->prepare('SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id WHERE i.id = ?');
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    if (!$item) {
        die('找不到該商品');
    }
} catch (PDOException $e) {
    die("無法讀取商品資料: " . $e->getMessage());
}

// 獲取所有規格，並轉換成一個易於在 JavaScript 中使用的格式
try {
    $stmt = $pdo->prepare(
       'SELECT iv.color_id, iv.size_id, iv.sell_price, iv.stock, c.name as color_name, s.name as size_name
        FROM item_variants iv
        JOIN colors c ON iv.color_id = c.id
        JOIN sizes s ON iv.size_id = s.id
        WHERE iv.item_id = ?'
    );
    $stmt->execute([$item_id]);
    $variants_raw = $stmt->fetchAll();

    $variants_json = [];
    $available_colors = [];
    $available_sizes = [];

    foreach ($variants_raw as $v) {
        $variants_json["{$v['color_id']}-{$v['size_id']}"] = [
            'price' => $v['sell_price'],
            'stock' => $v['stock']
        ];
        $available_colors[$v['color_id']] = $v['color_name'];
        $available_sizes[$v['size_id']] = $v['size_name'];
    }
    // 保持鍵名唯一，同時也方便按ID查找
    $available_colors = array_unique($available_colors);
    $available_sizes = array_unique($available_sizes);

} catch (PDOException $e) {
    die("無法讀取規格資料: " . $e->getMessage());
}

$page_title = htmlspecialchars($item['name']);
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
        <div class="item-container">
            <div class="item-image-section">
                <?php if (!empty($item['image'])) : ?>
                    <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <?php else: ?>
                    <div class="product-image-placeholder">無圖片</div>
                <?php endif; ?>
            </div>
            <div class="item-details-section">
                <a href="index.php" class="back-link">&larr; 回到商品列表</a>
                <p class="category"><?php echo htmlspecialchars($item['category_name'] ?? '未分類'); ?></p>
                <h1><?php echo htmlspecialchars($item['name']); ?></h1>
                <p class="description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                
                <div class="variant-selector">
                    <div class="form-group">
                        <label>顏色:</label>
                        <div class="options">
                            <?php foreach ($available_colors as $id => $name): ?>
                                <button class="option-btn" data-type="color" data-id="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>尺寸:</label>
                        <div class="options">
                            <?php foreach ($available_sizes as $id => $name): ?>
                                <button class="option-btn" data-type="size" data-id="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="price-stock-info">
                    <span class="price">請選擇規格</span>
                    <span class="stock"></span>
                </div>

                <div class="actions">
                        <form id="add-to-cart-form" action="add_to_cart.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="color_id" id="selected-color-id">
                            <input type="hidden" name="size_id" id="selected-size-id">
                            <div class="form-group quantity-selector">
                                <label for="quantity">數量:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" disabled>
                            </div>
                            <button type="submit" id="add-to-cart-btn" class="btn-add-to-cart" disabled>請先選擇規格</button>
                        </form>
                    </div>
                </div>
            </div>

        <script>
        const variantsData = <?php echo json_encode($variants_json); ?>;
        const optionButtons = document.querySelectorAll('.option-btn');
        const priceDisplay = document.querySelector('.price');
        const stockDisplay = document.querySelector('.stock');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const quantityInput = document.getElementById('quantity');
        const selectedColorIdInput = document.getElementById('selected-color-id');
        const selectedSizeIdInput = document.getElementById('selected-size-id');

        let selectedColor = null;
        let selectedSize = null;

        optionButtons.forEach(button => {
            button.addEventListener('click', () => {
                const type = button.dataset.type;
                const id = button.dataset.id;

                // 處理按鈕選中狀態
                document.querySelectorAll(`.option-btn[data-type='${type}']`).forEach(btn => btn.classList.remove('selected'));
                button.classList.add('selected');

                if (type === 'color') {
                    selectedColor = id;
                    selectedColorIdInput.value = id;
                } else if (type === 'size') {
                    selectedSize = id;
                    selectedSizeIdInput.value = id;
                }

                updatePriceAndStock();
            });
        });

        function updatePriceAndStock() {
            if (selectedColor && selectedSize) {
                const key = `${selectedColor}-${selectedSize}`;
                const variant = variantsData[key];

                if (variant) {
                    priceDisplay.textContent = `NT$ ${variant.price}`;
                    if (variant.stock > 0) {
                        stockDisplay.textContent = `庫存: ${variant.stock}`;
                        stockDisplay.className = 'stock available';
                        addToCartBtn.textContent = '加入購物車';
                        addToCartBtn.disabled = false;
                        quantityInput.disabled = false;
                        quantityInput.max = variant.stock; // 設定最大購買數量為庫存量
                        if (parseInt(quantityInput.value) > variant.stock) {
                            quantityInput.value = variant.stock; // 如果當前數量超過庫存，則調整為庫存量
                        }
                    } else {
                        stockDisplay.textContent = '已售完';
                        stockDisplay.className = 'stock out-of-stock';
                        addToCartBtn.textContent = '已售完';
                        addToCartBtn.disabled = true;
                        quantityInput.disabled = true;
                        quantityInput.value = 1;
                    }
                } else {
                    priceDisplay.textContent = '無此規格組合';
                    stockDisplay.textContent = '';
                    addToCartBtn.textContent = '無法選擇';
                    addToCartBtn.disabled = true;
                    quantityInput.disabled = true;
                    quantityInput.value = 1;
                }
            } else {
                // 如果還沒選完，保持初始狀態
                priceDisplay.textContent = '請選擇規格';
                stockDisplay.textContent = '';
                addToCartBtn.textContent = '請先選擇規格';
                addToCartBtn.disabled = true;
                quantityInput.disabled = true;
                quantityInput.value = 1;
            }
        }

        </script>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>