<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php'; // Ensure functions.php is included

if (!isLoggedIn() || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    header("location: index.php");
    exit;
}

// Fetch all data for display
$menu_items = [];
$categories = [];
$customization_options = [];
$choices_by_option = [];
$menu_item_customization_map = [];

try {
    // Fetch categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();

    // Fetch customization options
    $stmt = $pdo->query("SELECT * FROM customization_options ORDER BY name");
    $customization_options = $stmt->fetchAll();

    // Fetch customization choices and map them to options
    $stmt = $pdo->query("SELECT * FROM customization_choices ORDER BY option_id, name");
    foreach ($stmt->fetchAll() as $choice) {
        $choices_by_option[$choice['option_id']][] = $choice;
    }

    // Fetch menu items with category name
    $stmt = $pdo->query("SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id ORDER BY c.name, mi.name");
    $menu_items = $stmt->fetchAll();

    // Fetch menu item customization links
    $stmt = $pdo->query("SELECT menu_item_id, option_id FROM menu_item_customizations");
    foreach ($stmt->fetchAll() as $link) {
        $menu_item_customization_map[$link['menu_item_id']][] = $link['option_id'];
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error_message = "無法載入餐點資料。";
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>餐點管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">儀表板</a></li>
                        <li class="nav-item"><a class="nav-link" href="orders.php">訂單管理</a></li>
                        <li class="nav-item"><a class="nav-link active" href="menu.php">餐點管理</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="users.php">使用者管理</a></li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <ul class="nav flex-column mb-2"><li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li></ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">餐點管理</h1>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Customization Options Management -->
                <div class="card mb-4">
                    <div class="card-header">客製化選項管理</div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOptionModal">新增客製化選項</button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr><th>ID</th><th>選項名稱</th><th>類型</th><th>必選</th><th>選擇項</th><th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customization_options)): ?>
                                        <tr><td colspan="6" class="text-center">目前沒有任何客製化選項。</td></tr>
                                    <?php else: ?>
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
                                                                <button type="button" class="btn btn-link btn-sm text-white p-0 delete-choice-btn" data-id="<?php echo $choice['id']; ?>">x</button>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        無
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-success btn-sm ms-2 add-choice-btn" data-bs-toggle="modal" data-bs-target="#addChoiceModal" data-option-id="<?php echo $option['id']; ?>">新增選擇項</button>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-option-btn" data-bs-toggle="modal" data-bs-target="#editOptionModal" data-id="<?php echo $option['id']; ?>" data-name="<?php echo htmlspecialchars($option['name']); ?>" data-type="<?php echo htmlspecialchars($option['type']); ?>" data-is-required="<?php echo htmlspecialchars($option['is_required']); ?>">編輯</button>
                                                    <button type="button" class="btn btn-danger btn-sm delete-option-btn" data-id="<?php echo $option['id']; ?>">刪除</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Category Management -->
                <div class="card mb-4">
                    <div class="card-header">餐點分類管理</div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">新增分類</button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr><th>ID</th><th>分類名稱</th><th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr><td colspan="3" class="text-center">目前沒有任何分類。</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-category-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>">編輯</button>
                                                    <button type="button" class="btn btn-danger btn-sm delete-category-btn" data-id="<?php echo $category['id']; ?>">刪除</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Management -->
                <div class="card mb-4">
                    <div class="card-header">餐點列表</div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">新增餐點</button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr><th>ID</th><th>圖片</th><th>名稱</th><th>分類</th><th>價格</th><th>描述</th><th>狀態</th><th>客製化選項</th><th>操作</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($menu_items)): ?>
                                        <tr><td colspan="9" class="text-center">目前沒有任何餐點。</td></tr>
                                    <?php else: ?>
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
                                                <td><?php echo getAvailabilityText($item['is_available']); ?></td>
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
                                                    <button type="button" class="btn btn-warning btn-sm edit-menu-item-btn" data-bs-toggle="modal" data-bs-target="#editMenuItemModal" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-description="<?php echo htmlspecialchars($item['description']); ?>" data-price="<?php echo htmlspecialchars($item['price']); ?>" data-category-id="<?php echo htmlspecialchars($item['category_id']); ?>" data-image-url="<?php echo htmlspecialchars($item['image_url']); ?>" data-is-available="<?php echo htmlspecialchars($item['is_available']); ?>" data-customization-options="<?php echo htmlspecialchars(json_encode($item_options)); ?>">編輯</button>
                                                    <button type="button" class="btn btn-danger btn-sm delete-menu-item-btn" data-id="<?php echo $item['id']; ?>">刪除</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals for Add/Edit Operations -->
    <!-- Add Customization Option Modal -->
    <div class="modal fade" id="addOptionModal" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addOptionForm">
                    <div class="modal-header"><h5 class="modal-title" id="addOptionModalLabel">新增客製化選項</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label for="option_name" class="form-label">選項名稱</label><input type="text" class="form-control" id="option_name" name="name" required></div>
                        <div class="mb-3"><label for="option_type" class="form-label">選項類型</label><select class="form-select" id="option_type" name="type" required><option value="radio">單選 (Radio)</option><option value="checkbox">多選 (Checkbox)</option></select></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="option_is_required" name="is_required"><label class="form-check-label" for="option_is_required">必選</label></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">新增</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customization Option Modal -->
    <div class="modal fade" id="editOptionModal" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editOptionForm">
                    <div class="modal-header"><h5 class="modal-title" id="editOptionModalLabel">編輯客製化選項</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_option_id">
                        <div class="mb-3"><label for="edit_option_name" class="form-label">選項名稱</label><input type="text" class="form-control" id="edit_option_name" name="name" required></div>
                        <div class="mb-3"><label for="edit_option_type" class="form-label">選項類型</label><select class="form-select" id="edit_option_type" name="type" required><option value="radio">單選 (Radio)</option><option value="checkbox">多選 (Checkbox)</option></select></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="edit_option_is_required" name="is_required"><label class="form-check-label" for="edit_option_is_required">必選</label></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">儲存變更</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Customization Choice Modal -->
    <div class="modal fade" id="addChoiceModal" tabindex="-1" aria-labelledby="addChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addChoiceForm">
                    <div class="modal-header"><h5 class="modal-title" id="addChoiceModalLabel">新增客製化選擇項</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="option_id" id="add_choice_option_id">
                        <div class="mb-3"><label for="add_choice_name" class="form-label">選擇項名稱</label><input type="text" class="form-control" id="add_choice_name" name="name" required></div>
                        <div class="mb-3"><label for="add_choice_price_adjustment" class="form-label">價格調整</label><input type="number" step="0.01" class="form-control" id="add_choice_price_adjustment" name="price_adjustment" value="0.00"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">新增</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customization Choice Modal -->
    <div class="modal fade" id="editChoiceModal" tabindex="-1" aria-labelledby="editChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editChoiceForm">
                    <div class="modal-header"><h5 class="modal-title" id="editChoiceModalLabel">編輯客製化選擇項</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_choice_id">
                        <input type="hidden" name="option_id" id="edit_choice_option_id">
                        <div class="mb-3"><label for="edit_choice_name" class="form-label">選擇項名稱</label><input type="text" class="form-control" id="edit_choice_name" name="name" required></div>
                        <div class="mb-3"><label for="edit_choice_price_adjustment" class="form-label">價格調整</label><input type="number" step="0.01" class="form-control" id="edit_choice_price_adjustment" name="price_adjustment"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">儲存變更</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addCategoryForm">
                    <div class="modal-header"><h5 class="modal-title" id="addCategoryModalLabel">新增餐點分類</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label for="add_category_name" class="form-label">分類名稱</label><input type="text" class="form-control" id="add_category_name" name="name" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">新增</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCategoryForm">
                    <div class="modal-header"><h5 class="modal-title" id="editCategoryModalLabel">編輯餐點分類</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_category_id">
                        <div class="mb-3"><label for="edit_category_name" class="form-label">分類名稱</label><input type="text" class="form-control" id="edit_category_name" name="name" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">儲存變更</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Menu Item Modal -->
    <div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-labelledby="addMenuItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addMenuItemForm" enctype="multipart/form-data">
                    <div class="modal-header"><h5 class="modal-title" id="addMenuItemModalLabel">新增餐點</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label for="add_name" class="form-label">餐點名稱</label><input type="text" class="form-control" id="add_name" name="name" required></div>
                        <div class="mb-3"><label for="add_description" class="form-label">描述</label><textarea class="form-control" id="add_description" name="description" rows="3"></textarea></div>
                        <div class="mb-3"><label for="add_price" class="form-label">價格</label><input type="number" step="0.01" class="form-control" id="add_price" name="price" required min="0.01"></div>
                        <div class="mb-3"><label for="add_category_id" class="form-label">分類</label><select class="form-select" id="add_category_id" name="category_id" required><option value="">請選擇分類</option><?php foreach ($categories as $category): ?><option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label for="add_image" class="form-label">餐點圖片</label><input type="file" class="form-control" id="add_image" name="image" accept="image/*"></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="add_is_available" name="is_available" checked><label class="form-check-label" for="add_is_available">上架 (是否可點)</label></div>
                        <div class="mb-3"><label class="form-label">客製化選項</label><div id="add_custom_options_checkboxes"><?php if (!empty($customization_options)): ?><?php foreach ($customization_options as $option): ?><div class="form-check"><input class="form-check-input" type="checkbox" name="customization_options[]" value="<?php echo $option['id']; ?>" id="add_custom_option_<?php echo $option['id']; ?>"><label class="form-check-label" for="add_custom_option_<?php echo $option['id']; ?>"><?php echo htmlspecialchars($option['name']); ?></label></div><?php endforeach; ?><?php else: ?><p class="text-muted">請先新增客製化選項。</p><?php endif; ?></div></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">新增餐點</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Menu Item Modal -->
    <div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-labelledby="editMenuItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editMenuItemForm" enctype="multipart/form-data">
                    <div class="modal-header"><h5 class="modal-title" id="editMenuItemModalLabel">編輯餐點</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_item_id">
                        <input type="hidden" name="current_image_url" id="edit_current_image_url">
                        <div class="mb-3"><label for="edit_name" class="form-label">餐點名稱</label><input type="text" class="form-control" id="edit_name" name="name" required></div>
                        <div class="mb-3"><label for="edit_description" class="form-label">描述</label><textarea class="form-control" id="edit_description" name="description" rows="3"></textarea></div>
                        <div class="mb-3"><label for="edit_price" class="form-label">價格</label><input type="number" step="0.01" class="form-control" id="edit_price" name="price" required min="0.01"></div>
                        <div class="mb-3"><label for="edit_category_id" class="form-label">分類</label><select class="form-select" id="edit_category_id" name="category_id" required><option value="">請選擇分類</option><?php foreach ($categories as $category): ?><option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label for="edit_image" class="form-label">餐點圖片 (留空則不更改)</label><input type="file" class="form-control" id="edit_image" name="image" accept="image/*"><div id="current_image_preview" class="mt-2"></div></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" id="edit_is_available" name="is_available"><label class="form-check-label" for="edit_is_available">上架 (是否可點)</label></div>
                        <div class="mb-3"><label class="form-label">客製化選項</label><div id="edit_custom_options_checkboxes"><?php if (!empty($customization_options)): ?><?php foreach ($customization_options as $option): ?><div class="form-check"><input class="form-check-input edit-custom-option-checkbox" type="checkbox" name="customization_options[]" value="<?php echo $option['id']; ?>" id="edit_custom_option_<?php echo $option['id']; ?>"><label class="form-check-label" for="edit_custom_option_<?php echo $option['id']; ?>"><?php echo htmlspecialchars($option['name']); ?></label></div><?php endforeach; ?><?php else: ?><p class="text-muted">請先新增客製化選項。</p><?php endif; ?></div></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button><button type="submit" class="btn btn-primary">儲存變更</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper function to refresh the page after an AJAX operation
            function refreshPage() {
                location.reload();
            }

            // --- Customization Option Modals and AJAX --- //
            const addOptionForm = document.getElementById('addOptionForm');
            if (addOptionForm) {
                addOptionForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'add');
                    fetch('api_custom_option_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error adding option:', error));
                });
            }

            const editOptionModal = document.getElementById('editOptionModal');
            if (editOptionModal) {
                editOptionModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('edit_option_id').value = button.dataset.id;
                    document.getElementById('edit_option_name').value = button.dataset.name;
                    document.getElementById('edit_option_type').value = button.dataset.type;
                    document.getElementById('edit_option_is_required').checked = (button.dataset.isRequired == '1');
                });
                const editOptionForm = document.getElementById('editOptionForm');
                editOptionForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'edit');
                    fetch('api_custom_option_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error editing option:', error));
                });
            }

            document.querySelectorAll('.delete-option-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const optionId = this.dataset.id;
                    if (confirm('確定要刪除此客製化選項嗎？這將會刪除所有相關的選擇項！')) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', optionId);
                        fetch('api_custom_option_crud.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) refreshPage();
                        })
                        .catch(error => console.error('Error deleting option:', error));
                    }
                });
            });

            // --- Customization Choice Modals and AJAX --- //
            const addChoiceModal = document.getElementById('addChoiceModal');
            if (addChoiceModal) {
                addChoiceModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('add_choice_option_id').value = button.dataset.optionId;
                });
                const addChoiceForm = document.getElementById('addChoiceForm');
                addChoiceForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'add');
                    fetch('api_custom_choice_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error adding choice:', error));
                });
            }

            const editChoiceModal = document.getElementById('editChoiceModal');
            if (editChoiceModal) {
                editChoiceModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('edit_choice_id').value = button.dataset.id;
                    document.getElementById('edit_choice_option_id').value = button.dataset.optionId;
                    document.getElementById('edit_choice_name').value = button.dataset.name;
                    document.getElementById('edit_choice_price_adjustment').value = button.dataset.priceAdjustment;
                });
                const editChoiceForm = document.getElementById('editChoiceForm');
                editChoiceForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'edit');
                    fetch('api_custom_choice_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error editing choice:', error));
                });
            }

            document.querySelectorAll('.delete-choice-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const choiceId = this.dataset.id;
                    if (confirm('確定要刪除此客製化選擇項嗎？')) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', choiceId);
                        fetch('api_custom_choice_crud.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) refreshPage();
                        })
                        .catch(error => console.error('Error deleting choice:', error));
                    }
                });
            });

            // --- Category Modals and AJAX --- //
            const addCategoryForm = document.getElementById('addCategoryForm');
            if (addCategoryForm) {
                addCategoryForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'add');
                    fetch('api_category_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error adding category:', error));
                });
            }

            const editCategoryModal = document.getElementById('editCategoryModal');
            if (editCategoryModal) {
                editCategoryModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('edit_category_id').value = button.dataset.id;
                    document.getElementById('edit_category_name').value = button.dataset.name;
                });
                const editCategoryForm = document.getElementById('editCategoryForm');
                editCategoryForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'edit');
                    fetch('api_category_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error editing category:', error));
                });
            }

            document.querySelectorAll('.delete-category-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.dataset.id;
                    if (confirm('確定要刪除此分類嗎？這將會刪除所有屬於此分類的餐點！')) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', categoryId);
                        fetch('api_category_crud.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) refreshPage();
                        })
                        .catch(error => console.error('Error deleting category:', error));
                    }
                });
            });

            // --- Menu Item Modals and AJAX --- //
            const addMenuItemForm = document.getElementById('addMenuItemForm');
            if (addMenuItemForm) {
                addMenuItemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'add');
                    fetch('api_menu_item_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error adding menu item:', error));
                });
            }

            const editMenuItemModal = document.getElementById('editMenuItemModal');
            if (editMenuItemModal) {
                editMenuItemModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('edit_item_id').value = button.dataset.id;
                    document.getElementById('edit_name').value = button.dataset.name;
                    document.getElementById('edit_description').value = button.dataset.description;
                    document.getElementById('edit_price').value = button.dataset.price;
                    document.getElementById('edit_category_id').value = button.dataset.categoryId;
                    document.getElementById('edit_current_image_url').value = button.dataset.imageUrl;
                    document.getElementById('edit_is_available').checked = (button.dataset.isAvailable == '1');

                    const imagePreviewDiv = document.getElementById('current_image_preview');
                    imagePreviewDiv.innerHTML = '';
                    if (button.dataset.imageUrl) {
                        const img = document.createElement('img');
                        img.src = '../uploads/' + button.dataset.imageUrl;
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';
                        img.alt = 'Current Image';
                        imagePreviewDiv.appendChild(img);
                    } else {
                        imagePreviewDiv.innerHTML = '無圖片';
                    }

                    // Check associated customization options
                    const customizationOptions = JSON.parse(button.dataset.customizationOptions || '[]');
                    document.querySelectorAll('.edit-custom-option-checkbox').forEach(checkbox => {
                        checkbox.checked = customizationOptions.includes(parseInt(checkbox.value));
                    });
                });
                const editMenuItemForm = document.getElementById('editMenuItemForm');
                editMenuItemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'edit');
                    fetch('api_menu_item_crud.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) refreshPage();
                    })
                    .catch(error => console.error('Error editing menu item:', error));
                });
            }

            document.querySelectorAll('.delete-menu-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    if (confirm('確定要刪除此餐點嗎？')) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', itemId);
                        fetch('api_menu_item_crud.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) refreshPage();
                        })
                        .catch(error => console.error('Error deleting menu item:', error));
                    }
                });
            });
        });
    </script>
</body>
</html>