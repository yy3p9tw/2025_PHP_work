<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin(); // 只有管理員才能管理使用者

$db = new Database();
$conn = $db->getConnection();

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// 處理使用者新增、編輯、刪除
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if (empty($username) || empty($password)) {
            $_SESSION['flash_message'] = '使用者名稱和密碼不能為空！';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $hashed_password = hashPassword($password);
            $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            try {
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
                $_SESSION['flash_message'] = '使用者已新增！';
                $_SESSION['flash_type'] = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') { // Duplicate entry
                    $_SESSION['flash_message'] = '使用者名稱已存在！';
                    $_SESSION['flash_type'] = 'danger';
                } else {
                    $_SESSION['flash_message'] = '新增使用者失敗：' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }
        header('Location: users.php');
        exit();
    }

    if (isset($_POST['edit_user'])) {
        $id = $_POST['id'] ?? 0;
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? ''; // 密碼可能為空，表示不修改
        $role = $_POST['role'] ?? 'staff';

        $sql = 'UPDATE users SET username = :username, role = :role';
        if (!empty($password)) {
            $hashed_password = hashPassword($password);
            $sql .= ', password = :password';
        }
        $sql .= ' WHERE id = :id';

        $stmt = $conn->prepare($sql);
        try {
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':role', $role);
            if (!empty($password)) {
                $stmt->bindParam(':password', $hashed_password);
            }
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $_SESSION['flash_message'] = '使用者已更新！';
            $_SESSION['flash_type'] = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Duplicate entry
                $_SESSION['flash_message'] = '使用者名稱已存在！';
                $_SESSION['flash_type'] = 'danger';
            } else {
                $_SESSION['flash_message'] = '更新使用者失敗：' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: users.php');
        exit();
    }

    if (isset($_POST['delete_user'])) {
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['flash_message'] = '使用者已刪除！';
        $_SESSION['flash_type'] = 'success';
        header('Location: users.php');
        exit();
    }
}

// 獲取所有使用者
$users_stmt = $conn->query("SELECT id, username, role FROM users ORDER BY id DESC");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>使用者管理 - 公仔銷售網站後台</title>
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
                            <a class="nav-link" href="products.php">
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
                            <a class="nav-link active" aria-current="page" href="users.php">
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
                    <h1 class="h2">使用者管理</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle"></i> 新增使用者
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
                                            <button type="button" class="btn btn-info btn-sm edit-user-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                                編輯
                                            </button>
                                            <form action="users.php" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除此使用者嗎？');">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">刪除</button>
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
                            <label for="addUsername" class="form-label">使用者名稱</label>
                            <input type="text" class="form-control" id="addUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="addPassword" class="form-label">密碼</label>
                            <input type="password" class="form-control" id="addPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="addRole" class="form-label">角色</label>
                            <select class="form-select" id="addRole" name="role">
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
                        <input type="hidden" name="id" id="editUserId">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">使用者名稱</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">密碼 (留空則不修改)</label>
                            <input type="password" class="form-control" id="editPassword" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">角色</label>
                            <select class="form-select" id="editRole" name="role">
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
    <script src="assets/js/admin_script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addUserModal = document.getElementById('addUserModal');
            var editUserModal = document.getElementById('editUserModal');

            if (editUserModal) {
                editUserModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget; // Button that triggered the modal
                    var id = button.getAttribute('data-id');
                    var username = button.getAttribute('data-username');
                    var role = button.getAttribute('data-role');

                    var userIdInput = editUserModal.querySelector('#editUserId');
                    var usernameInput = editUserModal.querySelector('#editUsername');
                    var roleSelect = editUserModal.querySelector('#editRole');

                    userIdInput.value = id;
                    usernameInput.value = username;
                    roleSelect.value = role;
                });
            }
        });
    </script>
</body>
</html>