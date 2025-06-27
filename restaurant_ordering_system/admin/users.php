<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php'; // 用於密碼雜湊

// 檢查是否已登入，並且必須是管理員角色才能訪問此頁面
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// --- 處理使用者相關操作 ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 新增/編輯使用者
    if (isset($_POST['add_user']) || isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'] ?? null;
        $username = trim($_POST['username']);
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'];

        if (empty($username) || (empty($password) && !$user_id)) { // 新增時密碼必填
            $message = "使用者名稱和密碼不能為空。";
            $message_type = "danger";
        } else {
            try {
                if ($user_id) { // 編輯
                    $sql = 'UPDATE users SET username = :username, role = :role';
                    if (!empty($password)) { // 如果有輸入新密碼，則更新密碼
                        $hashed_password = hashPassword($password);
                        $sql .= ', password = :password';
                    }
                    $sql .= ' WHERE id = :id';
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $user_id);
                    $message = "使用者帳號已更新。";
                    $message_type = "success";
                } else { // 新增
                    $hashed_password = hashPassword($password);
                    $sql = 'INSERT INTO users (username, password, role) VALUES (:username, :password, :role)';
                    $stmt = $conn->prepare($sql);
                    $message = "使用者帳號已新增。";
                    $message_type = "success";
                }

                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':role', $role);
                if (!empty($password) || !$user_id) { // 新增時或編輯時有新密碼才綁定
                    $stmt->bindParam(':password', $hashed_password);
                }
                $stmt->execute();

            } catch (PDOException $e) {
                if ($e->getCode() == '23000') { // 唯一約束錯誤 (例如使用者名稱重複)
                    $message = "使用者名稱已存在，請選擇其他名稱。";
                    $message_type = "danger";
                } else {
                    $message = "操作失敗: " . $e->getMessage();
                    $message_type = "danger";
                }
            }
        }
    }

    // 刪除使用者
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        // 防止刪除自己的帳號
        if ($user_id == $_SESSION['user_id']) {
            $message = "您不能刪除自己的帳號。";
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $message = "使用者帳號已刪除。";
            $message_type = "success";
        }
    }

    // 重定向以防止表單重複提交
    header('Location: users.php?msg=' . urlencode($message) . '&type=' . urlencode($message_type));
    exit();
}

// 獲取所有使用者
$users_stmt = $conn->query('SELECT id, username, role FROM users ORDER BY id');
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>使用者管理</title>
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
                            <a class="nav-link" href="menu.php">
                                餐點管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="users.php">
                                使用者管理
                            </a>
                        </li>
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
                    <h1 class="h2">使用者管理</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header">
                        使用者列表
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            新增使用者
                        </button>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>使用者名稱</th>
                                        <th>角色</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users): ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-user-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                        data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                                        編輯
                                                    </button>
                                                    <form action="users.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此使用者帳號嗎？');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm" <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>刪除</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">目前沒有任何使用者。</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 新增使用者 Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="users.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">新增使用者</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_username" class="form-label">使用者名稱</label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_password" class="form-label">密碼</label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_role" class="form-label">角色</label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="staff">員工</option>
                                <option value="admin">管理員</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="add_user" class="btn btn-primary">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯使用者 Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="users.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">編輯使用者</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">使用者名稱</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">新密碼 (留空則不更改)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">角色</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="staff">員工</option>
                                <option value="admin">管理員</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 編輯使用者 Modal 數據填充
        document.addEventListener('DOMContentLoaded', function() {
            var editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var username = button.getAttribute('data-username');
                var role = button.getAttribute('data-role');

                var modalIdInput = editUserModal.querySelector('#edit_user_id');
                var modalUsernameInput = editUserModal.querySelector('#edit_username');
                var modalRoleSelect = editUserModal.querySelector('#edit_role');
                var modalPasswordInput = editUserModal.querySelector('#edit_password');

                modalIdInput.value = id;
                modalUsernameInput.value = username;
                modalRoleSelect.value = role;
                modalPasswordInput.value = ''; // 清空密碼欄位，避免洩露
            });
        });
    </script>
</body>
</html>