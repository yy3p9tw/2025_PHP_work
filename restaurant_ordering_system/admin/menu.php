<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php'; // 確保引入 functions.php

// 檢查是否已登入，並檢查權限 (只有管理員可以管理餐點和客製化選項)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// --- 處理餐點相關操作 ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 新增/編輯餐點
    if (isset($_POST['add_menu_item']) || isset($_POST['edit_menu_item'])) {
        $item_id = $_POST['item_id'] ?? null;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $image_url = $_POST['current_image_url'] ?? null; // 編輯時保留舊圖片
        $selected_customization_options = $_POST['customization_options'] ?? []; // 獲取選中的客製化選項

        // 處理圖片上傳
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/";
            $image_name = basename($_FILES["image"]["name"]);
            $image_file_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $new_image_name = uniqid() . '.' . $image_file_type; // 生成唯一檔名
            $target_file = $target_dir . $new_image_name;

            // 檢查檔案類型
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                // 檢查檔案大小 (例如：不超過 5MB)
                if ($_FILES["image"]["size"] > 5000000) {
                    $message = "抱歉，您的檔案太大。";
                    $message_type = "danger";
                } else {
                    // 允許的檔案格式
                    if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif") {
                        $message = "抱歉，只允許 JPG, JPEG, PNG & GIF 檔案。";
                        $message_type = "danger";
                    } else {
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $image_url = $new_image_name;
                        } else {
                            $message = "抱歉，上傳您的檔案時發生錯誤。";
                            $message_type = "danger";
                        }
                    }
                }
            } else {
                $message = "檔案不是圖片。";
                $message_type = "danger";
            }
        }

        if (empty($name) || $price <= 0 || $category_id <= 0) {
            $message = "請填寫所有必填欄位並確保價格有效。";
            $message_type = "danger";
        } else {
            try {
                $conn->beginTransaction();

                if ($item_id) { // 編輯
                    $stmt = $conn->prepare('UPDATE menu_items SET name = :name, description = :description, price = :price, category_id = :category_id, image_url = :image_url, is_available = :is_available WHERE id = :id');
                    $stmt->bindParam(':id', $item_id);
                    $message = "餐點已更新。";
                    $message_type = "success";
                } else { // 新增
                    $stmt = $conn->prepare('INSERT INTO menu_items (name, description, price, category_id, image_url, is_available) VALUES (:name, :description, :price, :category_id, :image_url, :is_available)');
                    $message = "餐點已新增。";
                    $message_type = "success";
                }
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':image_url', $image_url);
                $stmt->bindParam(':is_available', $is_available);
                $stmt->execute();

                // 如果是新增餐點，獲取新插入的 ID
                if (!$item_id) {
                    $item_id = $conn->lastInsertId();
                }

                // 更新餐點與客製化選項的關聯
                // 先刪除舊的關聯
                $delete_stmt = $conn->prepare('DELETE FROM menu_item_customizations WHERE menu_item_id = :menu_item_id');
                $delete_stmt->bindParam(':menu_item_id', $item_id);
                $delete_stmt->execute();

                // 再插入新的關聯
                if (!empty($selected_customization_options)) {
                    $insert_stmt = $conn->prepare('INSERT INTO menu_item_customizations (menu_item_id, option_id) VALUES (:menu_item_id, :option_id)');
                    foreach ($selected_customization_options as $option_id_val) {
                        $insert_stmt->bindParam(':menu_item_id', $item_id);
                        $insert_stmt->bindParam(':option_id', $option_id_val);
                        $insert_stmt->execute();
                    }
                }

                $conn->commit();

            } catch (PDOException $e) {
                $conn->rollBack();
                $message = "操作失敗: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }

    // 刪除餐點
    if (isset($_POST['delete_menu_item'])) {
        $item_id = $_POST['item_id'];
        $stmt = $conn->prepare('DELETE FROM menu_items WHERE id = :id');
        $stmt->bindParam(':id', $item_id);
        $stmt->execute();
        $message = "餐點已刪除。";
        $message_type = "success";
    }

    // --- 處理分類相關操作 ---
    // 新增/編輯分類
    if (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
        $category_id = $_POST['category_id'] ?? null;
        $category_name = trim($_POST['category_name']);

        if (empty($category_name)) {
            $message = "分類名稱不能為空。";
            $message_type = "danger";
        } else {
            if ($category_id) { // 編輯
                $stmt = $conn->prepare('UPDATE categories SET name = :name WHERE id = :id');
                $stmt->bindParam(':id', $category_id);
                $message = "分類已更新。";
                $message_type = "success";
            } else { // 新增
                $stmt = $conn->prepare('INSERT INTO categories (name) VALUES (:name)');
                $message = "分類已新增。";
                $message_type = "success";
            }
            $stmt->bindParam(':name', $category_name);
            $stmt->execute();
        }
    }

    // 刪除分類
    if (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];
        $stmt = $conn->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->bindParam(':id', $category_id);
        $stmt->execute();
        $message = "分類已刪除。";
        $message_type = "success";
    }

    // --- 處理客製化選項相關操作 ---
    // 新增/編輯客製化選項
    if (isset($_POST['add_option']) || isset($_POST['edit_option'])) {
        $option_id = $_POST['option_id'] ?? null;
        $option_name = trim($_POST['option_name']);
        $option_type = $_POST['option_type'];
        $is_required = isset($_POST['option_is_required']) ? 1 : 0;

        if (empty($option_name)) {
            $message = "客製化選項名稱不能為空。";
            $message_type = "danger";
        } else {
            if ($option_id) { // 編輯
                $stmt = $conn->prepare('UPDATE customization_options SET name = :name, type = :type, is_required = :is_required WHERE id = :id');
                $stmt->bindParam(':id', $option_id);
                $message = "客製化選項已更新。";
                $message_type = "success";
            } else { // 新增
                $stmt = $conn->prepare('INSERT INTO customization_options (name, type, is_required) VALUES (:name, :type, :is_required)');
                $message = "客製化選項已新增。";
                $message_type = "success";
            }
            $stmt->bindParam(':name', $option_name);
            $stmt->bindParam(':type', $option_type);
            $stmt->bindParam(':is_required', $is_required);
            $stmt->execute();
        }
    }

    // 刪除客製化選項
    if (isset($_POST['delete_option'])) {
        $option_id = $_POST['option_id'];
        $stmt = $conn->prepare('DELETE FROM customization_options WHERE id = :id');
        $stmt->bindParam(':id', $option_id);
        $stmt->execute();
        $message = "客製化選項已刪除。";
        $message_type = "success";
    }

    // --- 處理客製化選擇項相關操作 ---
    // 新增/編輯客製化選擇項
    if (isset($_POST['add_choice']) || isset($_POST['edit_choice'])) {
        $choice_id = $_POST['choice_id'] ?? null;
        $option_id = (int)$_POST['choice_option_id'];
        $choice_name = trim($_POST['choice_name']);
        $price_adjustment = (float)$_POST['price_adjustment'];

        if (empty($choice_name)) {
            $message = "客製化選擇項名稱不能為空。";
            $message_type = "danger";
        } else {
            if ($choice_id) { // 編輯
                $stmt = $conn->prepare('UPDATE customization_choices SET name = :name, price_adjustment = :price_adjustment WHERE id = :id');
                $stmt->bindParam(':id', $choice_id);
                $message = "客製化選擇項已更新。";
                $message_type = "success";
            } else { // 新增
                $stmt = $conn->prepare('INSERT INTO customization_choices (option_id, name, price_adjustment) VALUES (:option_id, :name, :price_adjustment)');
                $stmt->bindParam(':option_id', $option_id);
                $message = "客製化選擇項已新增。";
                $message_type = "success";
            }
            $stmt->bindParam(':name', $choice_name);
            $stmt->bindParam(':price_adjustment', $price_adjustment);
            $stmt->execute();
        }
    }

    // 刪除客製化選擇項
    if (isset($_POST['delete_choice'])) {
        $choice_id = $_POST['choice_id'];
        $stmt = $conn->prepare('DELETE FROM customization_choices WHERE id = :id');
        $stmt->bindParam(':id', $choice_id);
        $stmt->execute();
        $message = "客製化選擇項已刪除。";
        $message_type = "success";
    }

    // 重定向以防止表單重複提交
    header('Location: menu.php?msg=' . urlencode($message) . '&type=' . urlencode($message_type));
    exit();
}

// 獲取所有分類
$categories_stmt = $conn->query('SELECT * FROM categories ORDER BY id');
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

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

// 獲取所有餐點及其關聯的客製化選項
$menu_items_stmt = $conn->query('SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id ORDER BY c.name, mi.name');
$menu_items = $menu_items_stmt->fetchAll(PDO::FETCH_ASSOC);

$menu_item_customization_map = [];
if (!empty($menu_items)) {
    $menu_item_ids = implode(',', array_column($menu_items, 'id'));
    $mic_stmt = $conn->query("SELECT menu_item_id, option_id FROM menu_item_customizations WHERE menu_item_id IN ($menu_item_ids)");
    $mic_data = $mic_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mic_data as $mic) {
        $menu_item_customization_map[$mic['menu_item_id']][] = $mic['option_id'];
    }
}

// 處理重定向帶來的訊息
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = urldecode($_GET['msg']);
    $message_type = urldecode($_GET['type']);
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>餐點管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
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
                            <a class="nav-link" href="orders.php">
                                訂單管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="menu.php">
                                餐點管理
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
                    <h1 class="h2">餐點管理</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- 客製化選項管理區塊 -->
                <div class="card mb-4">
                    <div class="card-header">
                        客製化選項管理
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                            新增客製化選項
                        </button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>選項名稱</th>
                                        <th>類型</th>
                                        <th>必選</th>
                                        <th>選擇項</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($customization_options): ?>
                                        <?php foreach ($customization_options as $option): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($option['id']); ?></td>
                                                <td><?php echo htmlspecialchars($option['name']); ?></td>
                                                <td><?php echo htmlspecialchars($option['type']); ?></td>
                                                <td><?php echo $option['is_required'] ? '是' : '否'; ?></td>
                                                <td>
                                                    <?php if (isset($choices_by_option[$option['id']])): ?>
                                                        <?php foreach ($choices_by_option[$option['id']] as $choice): ?>
                                                            <span class="badge bg-secondary me-1">
                                                                <?php echo htmlspecialchars($choice['name']); ?> (<?php echo $choice['price_adjustment'] >= 0 ? '+' : ''; ?><?php echo htmlspecialchars(number_format($choice['price_adjustment'], 2)); ?>)
                                                                <button type="button" class="btn btn-link btn-sm text-white p-0 ms-1 edit-choice-btn" data-bs-toggle="modal" data-bs-target="#editChoiceModal" data-id="<?php echo $choice['id']; ?>" data-option-id="<?php echo $choice['option_id']; ?>" data-name="<?php echo htmlspecialchars($choice['name']); ?>" data-price-adjustment="<?php echo htmlspecialchars($choice['price_adjustment']); ?>">編輯</button>
                                                                <form action="menu.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此選擇項嗎？');">
                                                                    <input type="hidden" name="choice_id" value="<?php echo $choice['id']; ?>">
                                                                    <button type="submit" name="delete_choice" class="btn btn-link btn-sm text-white p-0">x</button>
                                                                </form>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        無
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-success btn-sm ms-2 add-choice-btn" data-bs-toggle="modal" data-bs-target="#addChoiceModal" data-option-id="<?php echo $option['id']; ?>">新增選擇項</button>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-option-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editOptionModal"
                                                        data-id="<?php echo $option['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($option['name']); ?>"
                                                        data-type="<?php echo htmlspecialchars($option['type']); ?>"
                                                        data-is-required="<?php echo htmlspecialchars($option['is_required']); ?>">
                                                        編輯
                                                    </button>
                                                    <form action="menu.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此客製化選項嗎？這將會刪除所有相關的選擇項！');">
                                                        <input type="hidden" name="option_id" value="<?php echo $option['id']; ?>">
                                                        <button type="submit" name="delete_option" class="btn btn-danger btn-sm">刪除</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">目前沒有任何客製化選項。</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 分類管理區塊 -->
                <div class="card mb-4">
                    <div class="card-header">
                        餐點分類管理
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            新增分類
                        </button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>分類名稱</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($categories): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-category-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                        data-id="<?php echo $category['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                                        編輯
                                                    </button>
                                                    <form action="menu.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此分類嗎？這將會刪除所有屬於此分類的餐點！');">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                        <button type="submit" name="delete_category" class="btn btn-danger btn-sm">刪除</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center">目前沒有任何分類。</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 餐點管理區塊 -->
                <div class="card mb-4">
                    <div class="card-header">
                        餐點列表
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                            新增餐點
                        </button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>圖片</th>
                                        <th>名稱</th>
                                        <th>分類</th>
                                        <th>價格</th>
                                        <th>描述</th>
                                        <th>狀態</th>
                                        <th>客製化選項</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($menu_items): ?>
                                        <?php foreach ($menu_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['id']); ?></td>
                                                <td>
                                                    <?php if ($item['image_url']): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        無圖片
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars(mb_strimwidth($item['description'], 0, 50, '...'))); ?></td>
                                                <td><?php echo $item['is_available'] ? '<span class="badge bg-success">上架</span>' : '<span class="badge bg-danger">下架</span>'; ?></td>
                                                <td>
                                                    <?php
                                                    $item_options = $menu_item_customization_map[$item['id']] ?? [];
                                                    if (!empty($item_options)) {
                                                        $option_names = [];
                                                        foreach ($item_options as $option_id) {
                                                            foreach ($customization_options as $opt) {
                                                                if ($opt['id'] == $option_id) {
                                                                    $option_names[] = htmlspecialchars($opt['name']);
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        echo implode(', ', $option_names);
                                                    } else {
                                                        echo '無';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-menu-item-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editMenuItemModal"
                                                        data-id="<?php echo $item['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                                        data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                                        data-price="<?php echo htmlspecialchars($item['price']); ?>"
                                                        data-category-id="<?php echo htmlspecialchars($item['category_id']); ?>"
                                                        data-image-url="<?php echo htmlspecialchars($item['image_url']); ?>"
                                                        data-is-available="<?php echo htmlspecialchars($item['is_available']); ?>"
                                                        data-customization-options="<?php echo htmlspecialchars(json_encode($item_options)); ?>">
                                                        編輯
                                                    </button>
                                                    <form action="menu.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此餐點嗎？');">
                                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" name="delete_menu_item" class="btn btn-danger btn-sm">刪除</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="9" class="text-center">目前沒有任何餐點。</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 新增客製化選項 Modal -->
    <div class="modal fade" id="addOptionModal" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOptionModalLabel">新增客製化選項</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="option_name" class="form-label">選項名稱</label>
                            <input type="text" class="form-control" id="option_name" name="option_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="option_type" class="form-label">選項類型</label>
                            <select class="form-select" id="option_type" name="option_type" required>
                                <option value="radio">單選 (Radio)</option>
                                <option value="checkbox">多選 (Checkbox)</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="option_is_required" name="option_is_required">
                            <label class="form-check-label" for="option_is_required">
                                必選
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_option" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯客製化選項 Modal -->
    <div class="modal fade" id="editOptionModal" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editOptionModalLabel">編輯客製化選項</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="option_id" id="edit_option_id">
                        <div class="mb-3">
                            <label for="edit_option_name" class="form-label">選項名稱</label>
                            <input type="text" class="form-control" id="edit_option_name" name="option_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_option_type" class="form-label">選項類型</label>
                            <select class="form-select" id="edit_option_type" name="option_type" required>
                                <option value="radio">單選 (Radio)</option>
                                <option value="checkbox">多選 (Checkbox)</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_option_is_required" name="option_is_required">
                            <label class="form-check-label" for="edit_option_is_required">
                                必選
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_option" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 新增選擇項 Modal -->
    <div class="modal fade" id="addChoiceModal" tabindex="-1" aria-labelledby="addChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addChoiceModalLabel">新增客製化選擇項</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="choice_option_id" id="add_choice_option_id">
                        <div class="mb-3">
                            <label for="choice_name" class="form-label">選擇項名稱</label>
                            <input type="text" class="form-control" id="choice_name" name="choice_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price_adjustment" class="form-label">價格調整</label>
                            <input type="number" step="0.01" class="form-control" id="price_adjustment" name="price_adjustment" value="0.00">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_choice" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯選擇項 Modal -->
    <div class="modal fade" id="editChoiceModal" tabindex="-1" aria-labelledby="editChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editChoiceModalLabel">編輯客製化選擇項</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="choice_id" id="edit_choice_id">
                        <input type="hidden" name="choice_option_id" id="edit_choice_option_id">
                        <div class="mb-3">
                            <label for="edit_choice_name" class="form-label">選擇項名稱</label>
                            <input type="text" class="form-control" id="edit_choice_name" name="choice_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price_adjustment" class="form-label">價格調整</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price_adjustment" name="price_adjustment">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_choice" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 新增分類 Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">新增餐點分類</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">分類名稱</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_category" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯分類 Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="menu.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">編輯餐點分類</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="category_id" id="edit_category_id">
                        <div class="mb-3">
                            <label for="edit_category_name" class="form-label">分類名稱</label>
                            <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_category" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 新增餐點 Modal -->
    <div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-labelledby="addMenuItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="menu.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMenuItemModalLabel">新增餐點</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_name" class="form-label">餐點名稱</label>
                            <input type="text" class="form-control" id="add_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label">描述</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="add_price" class="form-label">價格</label>
                            <input type="number" step="0.01" class="form-control" id="add_price" name="price" required min="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="add_category_id" class="form-label">分類</label>
                            <select class="form-select" id="add_category_id" name="category_id" required>
                                <option value="">請選擇分類</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add_image" class="form-label">餐點圖片</label>
                            <input type="file" class="form-control" id="add_image" name="image" accept="image/*">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="add_is_available" name="is_available" checked>
                            <label class="form-check-label" for="add_is_available">
                                上架 (是否可點)
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">客製化選項</label>
                            <?php if (!empty($customization_options)): ?>
                                <?php foreach ($customization_options as $option): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="customization_options[]" value="<?php echo $option['id']; ?>" id="add_custom_option_<?php echo $option['id']; ?>">
                                        <label class="form-check-label" for="add_custom_option_<?php echo $option['id']; ?>">
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">請先新增客製化選項。</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_menu_item" class="btn btn-primary">新增餐點</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯餐點 Modal -->
    <div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-labelledby="editMenuItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="menu.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMenuItemModalLabel">編輯餐點</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="edit_item_id">
                        <input type="hidden" name="current_image_url" id="edit_current_image_url">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">餐點名稱</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">描述</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">價格</label>
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required min="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">分類</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">請選擇分類</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">餐點圖片 (留空則不更改)</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div id="current_image_preview" class="mt-2"></div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_is_available" name="is_available">
                            <label class="form-check-label" for="edit_is_available">
                                上架 (是否可點)
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">客製化選項</label>
                            <?php if (!empty($customization_options)): ?>
                                <?php foreach ($customization_options as $option): ?>
                                    <div class="form-check">
                                        <input class="form-check-input edit-custom-option-checkbox" type="checkbox" name="customization_options[]" value="<?php echo $option['id']; ?>" id="edit_custom_option_<?php echo $option['id']; ?>">
                                        <label class="form-check-label" for="edit_custom_option_<?php echo $option['id']; ?>">
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">請先新增客製化選項。</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_menu_item" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 編輯分類 Modal 數據填充
        document.addEventListener('DOMContentLoaded', function() {
            var editCategoryModal = document.getElementById('editCategoryModal');
            editCategoryModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var modalIdInput = editCategoryModal.querySelector('#edit_category_id');
                var modalNameInput = editCategoryModal.querySelector('#edit_category_name');

                modalIdInput.value = id;
                modalNameInput.value = name;
            });

            // 編輯餐點 Modal 數據填充
            var editMenuItemModal = document.getElementById('editMenuItemModal');
            editMenuItemModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var description = button.getAttribute('data-description');
                var price = button.getAttribute('data-price');
                var categoryId = button.getAttribute('data-category-id');
                var imageUrl = button.getAttribute('data-image-url');
                var isAvailable = button.getAttribute('data-is-available');
                var customizationOptions = JSON.parse(button.getAttribute('data-customization-options') || '[]');

                editMenuItemModal.querySelector('#edit_item_id').value = id;
                editMenuItemModal.querySelector('#edit_name').value = name;
                editMenuItemModal.querySelector('#edit_description').value = description;
                editMenuItemModal.querySelector('#edit_price').value = price;
                editMenuItemModal.querySelector('#edit_category_id').value = categoryId;
                editMenuItemModal.querySelector('#edit_current_image_url').value = imageUrl;

                var isAvailableCheckbox = editMenuItemModal.querySelector('#edit_is_available');
                isAvailableCheckbox.checked = (isAvailable == '1');

                var imagePreviewDiv = editMenuItemModal.querySelector('#current_image_preview');
                imagePreviewDiv.innerHTML = ''; // 清空之前的預覽
                if (imageUrl) {
                    var img = document.createElement('img');
                    img.src = '../uploads/' + imageUrl;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.alt = '當前圖片';
                    imagePreviewDiv.appendChild(img);
                } else {
                    imagePreviewDiv.innerHTML = '無圖片';
                }

                // 勾選已關聯的客製化選項
                editMenuItemModal.querySelectorAll('.edit-custom-option-checkbox').forEach(checkbox => {
                    checkbox.checked = customizationOptions.includes(parseInt(checkbox.value));
                });
            });

            // 新增選擇項 Modal 數據填充 (從客製化選項按鈕觸發)
            var addChoiceModal = document.getElementById('addChoiceModal');
            addChoiceModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var optionId = button.getAttribute('data-option-id');
                addChoiceModal.querySelector('#add_choice_option_id').value = optionId;
            });

            // 編輯選擇項 Modal 數據填充
            var editChoiceModal = document.getElementById('editChoiceModal');
            editChoiceModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var optionId = button.getAttribute('data-option-id');
                var name = button.getAttribute('data-name');
                var priceAdjustment = button.getAttribute('data-price-adjustment');

                editChoiceModal.querySelector('#edit_choice_id').value = id;
                editChoiceModal.querySelector('#edit_choice_option_id').value = optionId;
                editChoiceModal.querySelector('#edit_choice_name').value = name;
                editChoiceModal.querySelector('#edit_price_adjustment').value = priceAdjustment;
            });

            // 編輯客製化選項 Modal 數據填充
            var editOptionModal = document.getElementById('editOptionModal');
            editOptionModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var type = button.getAttribute('data-type');
                var isRequired = button.getAttribute('data-is-required');

                editOptionModal.querySelector('#edit_option_id').value = id;
                editOptionModal.querySelector('#edit_option_name').value = name;
                editOptionModal.querySelector('#edit_option_type').value = type;
                editOptionModal.querySelector('#edit_option_is_required').checked = (isRequired == '1');
            });
        });
    </script>
</body>
</html>