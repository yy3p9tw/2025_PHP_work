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

// 處理用戶操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF 驗證失敗';
    } else {
        switch ($_POST['action']) {
            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                $current_status = (int)$_POST['current_status'];
                $new_status = $current_status ? 0 : 1;
                
                $result = $db->execute('UPDATE users SET is_active = ? WHERE id = ?', [$new_status, $user_id]);
                if ($result) {
                    $success = '用戶狀態已更新';
                } else {
                    $error = '更新失敗';
                }
                break;
                
            case 'update_role':
                $user_id = (int)$_POST['user_id'];
                $new_role = $_POST['role'];
                
                if (in_array($new_role, ['user', 'admin'])) {
                    $result = $db->execute('UPDATE users SET role = ? WHERE id = ?', [$new_role, $user_id]);
                    if ($result) {
                        $success = '用戶角色已更新';
                    } else {
                        $error = '更新失敗';
                    }
                } else {
                    $error = '無效的角色';
                }
                break;
                
            case 'delete_user':
                $user_id = (int)$_POST['user_id'];
                
                // 不能刪除自己
                if ($user_id == $_SESSION['user_id']) {
                    $error = '不能刪除自己的帳戶';
                } else {
                    $result = $db->execute('DELETE FROM users WHERE id = ?', [$user_id]);
                    if ($result) {
                        $success = '用戶已刪除';
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
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 構建查詢條件
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = '(username LIKE ? OR email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($role_filter)) {
    $where_conditions[] = 'role = ?';
    $params[] = $role_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = 'is_active = ?';
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 獲取用戶列表
$users = $db->fetchAll("
    SELECT * FROM users 
    {$where_clause}
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

// 獲取總數
$total_result = $db->fetchOne("SELECT COUNT(*) as count FROM users {$where_clause}", $params);
$total = $total_result['count'];
$total_pages = ceil($total / $limit);

$page_title = '用戶管理';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>用戶管理</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> 新增用戶
                </button>
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
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="搜尋用戶名或信箱" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">所有角色</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>一般用戶</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>管理員</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">所有狀態</option>
                                <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>啟用</option>
                                <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>停用</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">搜尋</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 用戶列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用戶名</th>
                                    <th>信箱</th>
                                    <th>角色</th>
                                    <th>狀態</th>
                                    <th>註冊時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $user['role'] === 'admin' ? '管理員' : '一般用戶'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['is_active'] ? '啟用' : '停用'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- 切換狀態 -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>" 
                                                        onclick="return confirm('確定要<?php echo $user['is_active'] ? '停用' : '啟用'; ?>此用戶嗎？')">
                                                    <?php echo $user['is_active'] ? '停用' : '啟用'; ?>
                                                </button>
                                            </form>
                                            
                                            <!-- 角色切換 -->
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="update_role">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-info" 
                                                        onclick="return confirm('確定要更改此用戶的角色嗎？')">
                                                    設為<?php echo $user['role'] === 'admin' ? '一般用戶' : '管理員'; ?>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <!-- 刪除用戶 -->
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('確定要刪除此用戶嗎？此操作不可恢復！')">
                                                    刪除
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="用戶列表分頁">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">上一頁</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">下一頁</a>
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

<?php include '../includes/footer.php'; ?>
