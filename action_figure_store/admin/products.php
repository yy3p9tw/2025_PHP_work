<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin(); // 只有管理員才能管理產品

$db = new Database();
$conn = $db->getConnection();

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// 處理產品新增、編輯、刪除
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $image_url = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image_url = uploadImage($_FILES['image']);
            if (!$image_url) {
                header('Location: products.php');
                exit();
            }
        }

        $stmt = $conn->prepare('INSERT INTO products (name, description, price, image_url) VALUES (:name, :description, :price, :image_url)');
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->execute();

        $_SESSION['flash_message'] = '產品已新增！';
        $_SESSION['flash_type'] = 'success';
        header('Location: products.php');
        exit();
    }

    if (isset($_POST['edit_product'])) {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $current_image_url = $_POST['current_image_url'] ?? null;
        $image_url = $current_image_url;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $new_image_url = uploadImage($_FILES['image']);
            if ($new_image_url) {
                $image_url = $new_image_url;
                // 如果有舊圖片且不是預設圖片，可以考慮刪除舊圖片
                // if ($current_image_url && file_exists(ROOT_PATH . '/uploads/' . $current_image_url) && $current_image_url != 'default.jpg') {
                //     unlink(ROOT_PATH . '/uploads/' . $current_image_url);
                // }
            } else {
                header('Location: products.php');
                exit();
            }
        }

        $stmt = $conn->prepare('UPDATE products SET name = :name, description = :description, price = :price, image_url = :image_url WHERE id = :id');
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $_SESSION['flash_message'] = '產品已更新！';
        $_SESSION['flash_type'] = 'success';
        header('Location: products.php');
        exit();
    }

    if (isset($_POST['delete_product'])) {
        $id = $_POST['id'] ?? 0;

        // 獲取圖片路徑以便刪除檔案
        $stmt_img = $conn->prepare('SELECT image_url FROM products WHERE id = :id');
        $stmt_img->bindParam(':id', $id);
        $stmt_img->execute();
        $product_image = $stmt_img->fetchColumn();

        $stmt = $conn->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // 刪除圖片檔案 (如果存在且不是預設圖片)
        if ($product_image && file_exists(ROOT_PATH . '/uploads/' . $product_image) && $product_image != 'default.jpg') {
            unlink(ROOT_PATH . '/uploads/' . $product_image);
        }

        $_SESSION['flash_message'] = '產品已刪除！';
        $_SESSION['flash_type'] = 'success';
        header('Location: products.php');
        exit();
    }
}

// 獲取所有產品
$products_stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>產品管理 - 公仔銷售網站後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 側邊導航欄 -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                儀表板
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="products.php">
                                產品管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="carousel.php">
                                輪播管理
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                使用者管理
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                登出
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- 主要內容區域 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">產品管理</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-circle"></i> 新增產品
                        </button>
                    </div>
                </div>

                <?php if ($flash_message): ?>
                    <div class="alert alert-<?php echo $flash_type; ?>" role="alert">
                        <?php echo $flash_message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>圖片</th>
                                <th>名稱</th>
                                <th>描述</th>
                                <th>價格</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($products): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td>
                                            <?php if ($product['image_url']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                無圖片
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($product['description'], 0, 50, 'utf-8')) . (mb_strlen($product['description'], 'utf-8') > 50 ? '...' : ''); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm edit-product-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                data-id="<?php echo $product['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                                data-image="<?php echo htmlspecialchars($product['image_url']); ?>">
                                                編輯
                                            </button>
                                            <form action="products.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此產品嗎？');">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">刪除</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">目前沒有任何產品。</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- 新增產品 Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="products.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">新增產品</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="productName" class="form-label">產品名稱</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">產品描述</label>
                            <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">價格</label>
                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">產品圖片</label>
                            <input type="file" class="form-control" id="productImage" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_product" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯產品 Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="products.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">編輯產品</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editProductId">
                        <input type="hidden" name="current_image_url" id="editProductCurrentImage">
                        <div class="mb-3">
                            <label for="editProductName" class="form-label">產品名稱</label>
                            <input type="text" class="form-control" id="editProductName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductDescription" class="form-label">產品描述</label>
                            <textarea class="form-control" id="editProductDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editProductPrice" class="form-label">價格</label>
                            <input type="number" class="form-control" id="editProductPrice" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductImage" class="form-label">產品圖片</label>
                            <input type="file" class="form-control" id="editProductImage" name="image" accept="image/*">
                            <small class="text-muted" id="currentProductImagePreview"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_product" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editProductModal = document.getElementById('editProductModal');
            editProductModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var description = button.getAttribute('data-description');
                var price = button.getAttribute('data-price');
                var image = button.getAttribute('data-image');

                var modalTitle = editProductModal.querySelector('.modal-title');
                var productIdInput = editProductModal.querySelector('#editProductId');
                var productNameInput = editProductModal.querySelector('#editProductName');
                var productDescriptionInput = editProductModal.querySelector('#editProductDescription');
                var productPriceInput = editProductModal.querySelector('#editProductPrice');
                var currentImageInput = editProductModal.querySelector('#editProductCurrentImage');
                var currentImagePreview = editProductModal.querySelector('#currentProductImagePreview');

                modalTitle.textContent = '編輯產品: ' + name;
                productIdInput.value = id;
                productNameInput.value = name;
                productDescriptionInput.value = description;
                productPriceInput.value = price;
                currentImageInput.value = image;

                if (image) {
                    currentImagePreview.innerHTML = '當前圖片: <img src="../uploads/' + image + '" style="width: 50px; height: 50px; object-fit: cover;"> (' + image + ')';
                } else {
                    currentImagePreview.innerHTML = '無圖片';
                }
            });
        });
    </script>
</body>
</html>