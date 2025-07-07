<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/auth.php';

// 檢查是否已登入且為管理員
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();

// 處理股票操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF 驗證失敗';
    } else {
        switch ($_POST['action']) {
            case 'add_stock':
                $code = trim($_POST['code'] ?? '');
                $name = trim($_POST['name'] ?? '');
                $industry = trim($_POST['industry'] ?? '');
                $description = trim($_POST['description'] ?? '');
                
                if (empty($code) || empty($name)) {
                    $error = '股票代碼和名稱不能為空';
                } else {
                    // 檢查代碼是否已存在
                    $existing = $db->fetchOne('SELECT id FROM stocks WHERE code = ?', [$code]);
                    if ($existing) {
                        $error = '股票代碼已存在';
                    } else {
                        $result = $db->execute('
                            INSERT INTO stocks (code, name, industry, status) 
                            VALUES (?, ?, ?, ?)
                        ', [$code, $name, $industry, 'active']);
                        
                        if ($result) {
                            $success = '股票已新增';
                        } else {
                            $error = '新增失敗';
                        }
                    }
                }
                break;
                
            case 'update_stock':
                $id = (int)$_POST['id'];
                $name = trim($_POST['name'] ?? '');
                $industry = trim($_POST['industry'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $status = $_POST['status'] ?? 'active';
                
                if (empty($name)) {
                    $error = '股票名稱不能為空';
                } else {
                    $result = $db->execute('
                        UPDATE stocks 
                        SET name = ?, industry = ?, status = ? 
                        WHERE id = ?
                    ', [$name, $industry, $status, $id]);
                    
                    if ($result) {
                        $success = '股票資料已更新';
                    } else {
                        $error = '更新失敗';
                    }
                }
                break;
                
            case 'delete_stock':
                $id = (int)$_POST['id'];
                
                // 檢查是否有相關交易記錄
                $has_transactions = $db->fetchOne('SELECT COUNT(*) as count FROM transactions WHERE stock_code = (SELECT code FROM stocks WHERE id = ?)', [$id]);
                if ($has_transactions['count'] > 0) {
                    $error = '此股票有交易記錄，不能刪除';
                } else {
                    $result = $db->execute('DELETE FROM stocks WHERE id = ?', [$id]);
                    if ($result) {
                        $success = '股票已刪除';
                    } else {
                        $error = '刪除失敗';
                    }
                }
                break;
        }
    }
}

// 獲取搜尋和篩選參數
$search = $_GET['search'] ?? '';
$industry_filter = $_GET['industry'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 構建查詢條件
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = '(code LIKE ? OR name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($industry_filter)) {
    $where_conditions[] = 'industry = ?';
    $params[] = $industry_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = 'status = ?';
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 獲取股票列表
$stocks = $db->fetchAll("
    SELECT * FROM stocks 
    {$where_clause}
    ORDER BY code ASC 
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

// 獲取總數
$total_result = $db->fetchOne("SELECT COUNT(*) as count FROM stocks {$where_clause}", $params);
$total = $total_result['count'];
$total_pages = ceil($total / $limit);

// 獲取所有行業
$industries = $db->fetchAll('SELECT DISTINCT industry FROM stocks WHERE industry IS NOT NULL ORDER BY industry');

$page_title = '股票管理';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>股票管理</h1>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="fas fa-plus"></i> 新增股票
                    </button>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- 搜尋和篩選 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="搜尋股票代碼或名稱" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="industry">
                                <option value="">所有行業</option>
                                <?php foreach ($industries as $ind): ?>
                                <option value="<?php echo htmlspecialchars($ind['industry']); ?>" 
                                        <?php echo $industry_filter === $ind['industry'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ind['industry']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">所有狀態</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>啟用</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>停用</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">搜尋</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 股票列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>股票代碼</th>
                                    <th>名稱</th>
                                    <th>行業</th>
                                    <th>目前價格</th>
                                    <th>狀態</th>
                                    <th>建立時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stocks as $stock): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($stock['code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($stock['name']); ?></td>
                                    <td><?php echo htmlspecialchars($stock['industry'] ?? '未分類'); ?></td>
                                    <td><?php echo number_format($stock['current_price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $stock['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $stock['status'] === 'active' ? '啟用' : '停用'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($stock['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editStockModal"
                                                    data-stock='<?php echo json_encode($stock); ?>'>
                                                編輯
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="delete_stock">
                                                <input type="hidden" name="id" value="<?php echo $stock['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('確定要刪除此股票嗎？')">
                                                    刪除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="股票列表分頁">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry_filter); ?>&status=<?php echo urlencode($status_filter); ?>">上一頁</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry_filter); ?>&status=<?php echo urlencode($status_filter); ?>">下一頁</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新增股票模態框 -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增股票</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_stock">
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">股票代碼 *</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">股票名稱 *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="industry" class="form-label">行業</label>
                        <input type="text" class="form-control" id="industry" name="industry" 
                               placeholder="例如：科技、金融、傳產">
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

<!-- 編輯股票模態框 -->
<div class="modal fade" id="editStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯股票</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_code" class="form-label">股票代碼</label>
                        <input type="text" class="form-control" id="edit_code" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">股票名稱 *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_industry" class="form-label">行業</label>
                        <input type="text" class="form-control" id="edit_industry" name="industry">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">狀態</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">啟用</option>
                            <option value="inactive">停用</option>
                        </select>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 編輯股票模態框
    const editModal = document.getElementById('editStockModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const stock = JSON.parse(button.getAttribute('data-stock'));
        
        document.getElementById('edit_id').value = stock.id;
        document.getElementById('edit_code').value = stock.code;
        document.getElementById('edit_name').value = stock.name;
        document.getElementById('edit_industry').value = stock.industry || '';
        document.getElementById('edit_status').value = stock.status;
    });
});
</script>

<?php include '../includes/footer.php'; ?>
