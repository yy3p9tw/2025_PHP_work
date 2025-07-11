<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

// 檢查管理員權限
checkAdminAccess();

$db = new Database();
$conn = $db->getConnection();

$page_title = "分類管理";
$current_page = "categories";

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            // 新增分類
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $status = ($_POST['status'] ?? 'active') === 'active' ? 1 : 0;
            
            if (empty($name)) {
                throw new Exception('分類名稱不能為空');
            }
            
            $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, sort_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $parent_id, $sort_order, $status]);
            
            $success_message = "分類新增成功";
            
        } elseif ($action === 'edit') {
            // 編輯分類
            $id = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            
            if (empty($name)) {
                throw new Exception('分類名稱不能為空');
            }
            
            // 檢查是否會造成循環引用
            if ($parent_id && $parent_id == $id) {
                throw new Exception('分類不能設定自己為父分類');
            }
            
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $description, $parent_id, $sort_order, $id]);
            
            $success_message = "分類更新成功";
            
        } elseif ($action === 'delete') {
            // 刪除分類
            $id = (int)$_POST['id'];
            
            // 檢查是否有子分類
            $check_children = $conn->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
            $check_children->execute([$id]);
            $children_count = $check_children->fetchColumn();
            
            if ($children_count > 0) {
                throw new Exception('此分類有子分類，無法刪除');
            }
            
            // 檢查是否有商品使用此分類
            $check_products = $conn->prepare("SELECT COUNT(*) FROM product_category WHERE category_id = ?");
            $check_products->execute([$id]);
            $products_count = $check_products->fetchColumn();
            
            if ($products_count > 0) {
                throw new Exception('此分類有商品使用，無法刪除');
            }
            
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            $success_message = "分類刪除成功";
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// 獲取所有分類（以樹狀結構）
function getCategoriesTree($conn, $parent_id = null, $level = 0) {
    $sql = "SELECT c.*, COUNT(pc.product_id) as product_count
            FROM categories c
            LEFT JOIN product_category pc ON c.id = pc.category_id
            WHERE " . ($parent_id ? "c.parent_id = ?" : "c.parent_id IS NULL") . "
            GROUP BY c.id
            ORDER BY c.sort_order, c.name";
    $stmt = $conn->prepare($sql);
    
    if ($parent_id) {
        $stmt->execute([$parent_id]);
    } else {
        $stmt->execute();
    }
    
    $categories = [];
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category['level'] = $level;
        $category['children'] = getCategoriesTree($conn, $category['id'], $level + 1);
        $categories[] = $category;
    }
    
    return $categories;
}

$categories_tree = getCategoriesTree($conn);

// 獲取所有分類（用於下拉選單）
$all_categories_stmt = $conn->query("SELECT id, name, parent_id FROM categories WHERE status = 1 ORDER BY parent_id, sort_order, name");
$all_categories = $all_categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 公仔天堂管理後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 側邊欄 -->
            <?php include 'sidebar.php'; ?>
            
            <!-- 主要內容 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-lg"></i> 新增分類
                    </button>
                </div>

                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 分類樹狀顯示 -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">分類結構</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="toggleExpandAll()">
                                <i class="bi bi-arrows-expand" id="expandIcon"></i> 
                                <span id="expandText">展開全部</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCategories()">
                                <i class="bi bi-arrow-clockwise"></i> 重新載入
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories_tree)): ?>
                            <p class="text-muted">尚無分類資料</p>
                        <?php else: ?>
                            <div class="category-tree" id="categoryTree">
                                <?php
                                function renderCategoryTree($categories) {
                                    foreach ($categories as $category) {
                                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $category['level']);
                                        $status_badge = ($category['status'] == 1) ? 
                                            '<span class="badge bg-success status-badge" data-category-id="' . $category['id'] . '" data-new-status="inactive" title="點擊停用">啟用</span>' : 
                                            '<span class="badge bg-secondary status-badge" data-category-id="' . $category['id'] . '" data-new-status="active" title="點擊啟用">停用</span>';
                                        
                                        $has_children = !empty($category['children']);
                                        $collapse_icon = $has_children ? 
                                            '<i class="bi bi-chevron-down toggle-icon me-1" style="cursor: pointer;" onclick="toggleCategory(this)"></i>' : 
                                            '<i class="bi bi-file-earmark me-1 text-muted"></i>';
                                        
                                        echo '<div class="category-item border-bottom py-2" data-category-id="' . $category['id'] . '" data-level="' . $category['level'] . '">';
                                        echo '<div class="d-flex justify-content-between align-items-center">';
                                        echo '<div class="category-content d-flex align-items-center">';
                                        echo $indent . $collapse_icon;
                                        echo '<i class="bi bi-folder me-1"></i> ';
                                        echo '<strong>' . htmlspecialchars($category['name']) . '</strong> ';
                                        echo $status_badge;
                                        echo '<div class="category-stats ms-2">';
                                        echo '<span class="badge bg-light text-dark">排序: ' . $category['sort_order'] . '</span>';
                                        
                                        // 產品數量徽章 - 根據數量使用不同顏色
                                        $product_count = (int)$category['product_count'];
                                        $count_color = $product_count > 0 ? 'product-count-badge' : 'bg-secondary';
                                        echo '<span class="badge ' . $count_color . '" title="此分類的產品數量">';
                                        echo '<i class="bi bi-box"></i> ' . $product_count . ' 個產品</span>';
                                        echo '</div>';
                                        
                                        if ($category['description']) {
                                            echo '<br>' . $indent . '<small class="text-muted ms-4">' . htmlspecialchars($category['description']) . '</small>';
                                        }
                                        echo '</div>';
                                        echo '<div class="category-actions">';
                                        echo '<button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(' . $category['id'] . ')" title="編輯">';
                                        echo '<i class="bi bi-pencil"></i>';
                                        echo '</button>';
                                        echo '<button class="btn btn-sm btn-outline-success me-1" onclick="addSubCategory(' . $category['id'] . ')" title="新增子分類">';
                                        echo '<i class="bi bi-plus"></i>';
                                        echo '</button>';
                                        
                                        // 如果有產品，顯示產品管理按鈕
                                        if ($category['product_count'] > 0) {
                                            echo '<button class="btn btn-sm btn-outline-warning me-1" onclick="manageProducts(' . $category['id'] . ', \'' . htmlspecialchars($category['name']) . '\', ' . $category['product_count'] . ')" title="管理產品">';
                                            echo '<i class="bi bi-box-seam"></i>';
                                            echo '</button>';
                                        }
                                        
                                        echo '<button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(' . $category['id'] . ', \'' . htmlspecialchars($category['name']) . '\')" title="刪除">';
                                        echo '<i class="bi bi-trash"></i>';
                                        echo '</button>';
                                        echo '</div>';
                                        echo '</div>';
                                        
                                        if (!empty($category['children'])) {
                                            echo '<div class="category-children" style="display: block;">';
                                            renderCategoryTree($category['children']);
                                            echo '</div>';
                                        }
                                        
                                        echo '</div>';
                                    }
                                }
                                renderCategoryTree($categories_tree);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 載入指示器 -->
                        <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">載入中...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 新增分類 Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新增分類</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="add_name" class="form-label">分類名稱 *</label>
                            <input type="text" class="form-control" id="add_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_description" class="form-label">分類描述</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_parent_id" class="form-label">父分類</label>
                            <select class="form-select" id="add_parent_id" name="parent_id">
                                <option value="">無（頂層分類）</option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo $cat['parent_id'] ? '　├─ ' : ''; ?>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_sort_order" class="form-label">排序</label>
                                    <input type="number" class="form-control" id="add_sort_order" name="sort_order" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_status" class="form-label">狀態</label>
                                    <select class="form-select" id="add_status" name="status">
                                        <option value="active">啟用</option>
                                        <option value="inactive">停用</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯分類 Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">編輯分類</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">分類名稱 *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">分類描述</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_parent_id" class="form-label">父分類</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">無（頂層分類）</option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo $cat['parent_id'] ? '　├─ ' : ''; ?>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="edit_sort_order" class="form-label">排序</label>
                                    <input type="number" class="form-control" id="edit_sort_order" name="sort_order">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 刪除確認 Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">確認刪除</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>您確定要刪除分類「<span id="delete_category_name"></span>」嗎？</p>
                    <p class="text-danger small">注意：刪除後無法復原</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_category_id">
                        <button type="submit" class="btn btn-danger">確認刪除</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 產品管理 Modal -->
    <div class="modal fade" id="manageProductsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">管理分類產品</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> 分類：<span id="manage_category_name"></span></h6>
                        <p class="mb-0">此分類包含 <strong><span id="manage_product_count"></span> 個產品</strong></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>移動產品到其他分類</h6>
                            <div class="mb-3">
                                <label for="target_category" class="form-label">目標分類</label>
                                <select class="form-select" id="target_category">
                                    <option value="">選擇目標分類</option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo $cat['parent_id'] ? '　├─ ' : ''; ?>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm" onclick="moveProducts()">
                                <i class="bi bi-arrow-right"></i> 移動產品
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-danger">清除產品關聯</h6>
                            <p class="small text-muted">將產品從此分類中移除（產品本身不會被刪除）</p>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearProducts()">
                                <i class="bi bi-x-circle"></i> 清除關聯
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    <a id="view_products_link" href="#" class="btn btn-primary">
                        <i class="bi bi-box"></i> 查看產品管理
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
    <script>
        let isAllExpanded = false;
        
        // 編輯分類
        async function editCategory(id) {
            try {
                showLoading(true);
                const response = await fetch(`api/category_detail.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('edit_id').value = result.data.id;
                    document.getElementById('edit_name').value = result.data.name;
                    document.getElementById('edit_description').value = result.data.description || '';
                    document.getElementById('edit_parent_id').value = result.data.parent_id || '';
                    document.getElementById('edit_sort_order').value = result.data.sort_order;
                    
                    // 移除自己和子分類選項（避免循環引用）
                    const parentSelect = document.getElementById('edit_parent_id');
                    Array.from(parentSelect.options).forEach(option => {
                        if (option.value == id) {
                            option.disabled = true;
                            option.style.display = 'none';
                        } else {
                            option.disabled = false;
                            option.style.display = '';
                        }
                    });
                    
                    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                    modal.show();
                } else {
                    showAlert('無法載入分類資料', 'danger');
                }
            } catch (error) {
                console.error('載入分類資料失敗:', error);
                showAlert('載入分類資料失敗', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // 新增子分類
        function addSubCategory(parentId) {
            // 重置表單
            document.getElementById('addCategoryModal').querySelector('form').reset();
            document.getElementById('add_parent_id').value = parentId;
            
            const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
            modal.show();
        }
        
        // 刪除分類
        function deleteCategory(id, name) {
            document.getElementById('delete_category_id').value = id;
            document.getElementById('delete_category_name').textContent = name;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            modal.show();
        }
        
        // 管理產品
        function manageProducts(categoryId, categoryName, productCount) {
            document.getElementById('manage_category_name').textContent = categoryName;
            document.getElementById('manage_product_count').textContent = productCount;
            
            // 設置當前分類 ID
            window.currentManageCategoryId = categoryId;
            
            // 移除目標分類選項中的當前分類
            const targetSelect = document.getElementById('target_category');
            Array.from(targetSelect.options).forEach(option => {
                if (option.value == categoryId) {
                    option.disabled = true;
                    option.style.display = 'none';
                } else {
                    option.disabled = false;
                    option.style.display = '';
                }
            });
            
            // 設置查看產品管理的連結
            document.getElementById('view_products_link').href = `products.php?category=${categoryId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('manageProductsModal'));
            modal.show();
        }
        
        // 移動產品到其他分類
        async function moveProducts() {
            const targetCategoryId = document.getElementById('target_category').value;
            const sourceCategoryId = window.currentManageCategoryId;
            
            if (!targetCategoryId) {
                showAlert('請選擇目標分類', 'warning');
                return;
            }
            
            if (!confirm('確定要將此分類的所有產品移動到選定的分類嗎？')) {
                return;
            }
            
            try {
                showLoading(true);
                
                const response = await fetch('api/category_manage.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'move_products',
                        source_category_id: sourceCategoryId,
                        target_category_id: targetCategoryId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('manageProductsModal')).hide();
                    showAlert(result.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                console.error('移動產品失敗:', error);
                showAlert('移動產品失敗', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // 清除產品關聯
        async function clearProducts() {
            const categoryId = window.currentManageCategoryId;
            
            if (!confirm('確定要清除此分類的所有產品關聯嗎？產品本身不會被刪除，但會失去分類標籤。')) {
                return;
            }
            
            try {
                showLoading(true);
                
                const response = await fetch('api/category_manage.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'clear_products',
                        category_id: categoryId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('manageProductsModal')).hide();
                    showAlert(result.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                console.error('清除產品關聯失敗:', error);
                showAlert('清除產品關聯失敗', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // 切換分類展開/收合
        function toggleCategory(element) {
            const categoryItem = element.closest('.category-item');
            const children = categoryItem.querySelector('.category-children');
            
            if (children) {
                const isVisible = children.style.display !== 'none';
                children.style.display = isVisible ? 'none' : 'block';
                element.className = isVisible ? 
                    'bi bi-chevron-right toggle-icon me-1' : 
                    'bi bi-chevron-down toggle-icon me-1';
            }
        }
        
        // 切換展開全部/收合全部
        function toggleExpandAll() {
            const children = document.querySelectorAll('.category-children');
            const icons = document.querySelectorAll('.toggle-icon');
            const expandIcon = document.getElementById('expandIcon');
            const expandText = document.getElementById('expandText');
            
            isAllExpanded = !isAllExpanded;
            
            children.forEach(child => {
                child.style.display = isAllExpanded ? 'block' : 'none';
            });
            
            icons.forEach(icon => {
                icon.className = isAllExpanded ? 
                    'bi bi-chevron-down toggle-icon me-1' : 
                    'bi bi-chevron-right toggle-icon me-1';
            });
            
            expandIcon.className = isAllExpanded ? 'bi bi-arrows-collapse' : 'bi bi-arrows-expand';
            expandText.textContent = isAllExpanded ? '收合全部' : '展開全部';
        }
        
        // 重新載入分類
        function refreshCategories() {
            location.reload();
        }
        
        // 顯示載入指示器
        function showLoading(show) {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) {
                indicator.style.display = show ? 'block' : 'none';
            }
        }
        
        // 顯示提示訊息
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.querySelector('main .container-fluid') || document.querySelector('main');
            if (container) {
                const alertContainer = document.createElement('div');
                alertContainer.innerHTML = alertHtml;
                container.insertBefore(alertContainer.firstElementChild, container.querySelector('.card'));
                
                // 自動消失
                setTimeout(() => {
                    const alert = container.querySelector('.alert');
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            }
        }
        
        // 切換分類狀態
        async function toggleCategoryStatus(id, newStatus) {
            debugLog(`toggleCategoryStatus 被調用，ID: ${id}, 新狀態: ${newStatus}`);
            
            if (!confirm(`確定要${newStatus === 'active' ? '啟用' : '停用'}此分類嗎？`)) {
                debugLog('用戶取消操作');
                return;
            }
            
            try {
                debugLog('開始載入指示器');
                showLoading(true);
                
                // 先獲取當前分類資料
                debugLog(`正在獲取分類 ${id} 的詳細資料`);
                const response = await fetch(`api/category_detail.php?id=${id}`);
                const categoryResult = await response.json();
                
                debugLog(`API 響應: ${JSON.stringify(categoryResult)}`);
                
                if (!categoryResult.success) {
                    throw new Error(categoryResult.error || '無法載入分類資料');
                }
                
                // 更新狀態
                const updateData = {
                    id: id,
                    name: categoryResult.data.name,
                    description: categoryResult.data.description,
                    parent_id: categoryResult.data.parent_id,
                    sort_order: categoryResult.data.sort_order,
                    status: newStatus
                };
                
                debugLog(`準備更新資料: ${JSON.stringify(updateData)}`);
                
                const updateResponse = await fetch('api/category_manage.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(updateData)
                });
                
                const result = await updateResponse.json();
                
                debugLog(`更新響應: ${JSON.stringify(result)}`);
                
                if (result.success) {
                    showAlert(`分類已${newStatus === 'active' ? '啟用' : '停用'}`, 'success');
                    
                    // 更新頁面上的狀態徽章
                    const statusBadge = document.querySelector(`.status-badge[data-category-id="${id}"]`);
                    debugLog(`找到狀態徽章: ${statusBadge ? '是' : '否'}`);
                    
                    if (statusBadge) {
                        if (newStatus === 'active') {
                            statusBadge.className = 'badge bg-success status-badge';
                            statusBadge.textContent = '啟用';
                            statusBadge.title = '點擊停用';
                            statusBadge.setAttribute('data-new-status', 'inactive');
                        } else {
                            statusBadge.className = 'badge bg-secondary status-badge';
                            statusBadge.textContent = '停用';
                            statusBadge.title = '點擊啟用';
                            statusBadge.setAttribute('data-new-status', 'active');
                        }
                        debugLog('狀態徽章已更新');
                    }
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                debugLog(`錯誤: ${error.message}`);
                console.error('切換分類狀態失敗:', error);
                showAlert('切換分類狀態失敗', 'danger');
            } finally {
                showLoading(false);
            }
        }

        
        // 調試函數
        function debugLog(message) {
            console.log(`[分類管理] ${message}`);
        }
        
        // 表單提交事件處理
        document.addEventListener('DOMContentLoaded', function() {
            // 狀態徽章點擊事件委託
            document.addEventListener('click', function(e) {
                debugLog(`點擊事件，目標: ${e.target.tagName}.${e.target.className}`);
                
                if (e.target.classList.contains('status-badge')) {
                    debugLog('檢測到狀態徽章點擊');
                    const categoryId = e.target.getAttribute('data-category-id');
                    const newStatus = e.target.getAttribute('data-new-status');
                    
                    debugLog(`分類ID: ${categoryId}, 新狀態: ${newStatus}`);
                    
                    if (categoryId && newStatus) {
                        toggleCategoryStatus(categoryId, newStatus);
                    } else {
                        debugLog('錯誤：缺少分類ID或新狀態');
                    }
                } else {
                    debugLog('點擊的不是狀態徽章');
                }
            });
            
            // 新增分類表單
            const addForm = document.querySelector('#addCategoryModal form');
            if (addForm) {
                addForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    try {
                        showLoading(true);
                        const response = await fetch('api/category_manage.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                            showAlert(result.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(result.error, 'danger');
                        }
                    } catch (error) {
                        console.error('新增分類失敗:', error);
                        showAlert('新增分類失敗', 'danger');
                    } finally {
                        showLoading(false);
                    }
                });
            }
            
            // 編輯分類表單
            const editForm = document.querySelector('#editCategoryModal form');
            if (editForm) {
                editForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    try {
                        showLoading(true);
                        const response = await fetch('api/category_manage.php', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
                            showAlert(result.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(result.error, 'danger');
                        }
                    } catch (error) {
                        console.error('更新分類失敗:', error);
                        showAlert('更新分類失敗', 'danger');
                    } finally {
                        showLoading(false);
                    }
                });
            }
            
            // 刪除分類表單
            const deleteForm = document.querySelector('#deleteCategoryModal form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const id = document.getElementById('delete_category_id').value;
                    
                    try {
                        showLoading(true);
                        const response = await fetch(`api/category_manage.php?id=${id}`, {
                            method: 'DELETE'
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            bootstrap.Modal.getInstance(document.getElementById('deleteCategoryModal')).hide();
                            showAlert(result.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert(result.error, 'danger');
                        }
                    } catch (error) {
                        console.error('刪除分類失敗:', error);
                        showAlert('刪除分類失敗', 'danger');
                    } finally {
                        showLoading(false);
                    }
                });
            }
        });
    </script>
</body>
</html>
