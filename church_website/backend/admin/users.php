<?php
// D:\YU\church_website\backend\admin\users.php

session_start();
require_once '../includes/db.php';

// --- 安全性檢查：確保使用者已登入且為管理員 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); // 未登入或非管理員導向登入頁
    exit();
}

$username = $_SESSION['username'] ?? '訪客';
$role = $_SESSION['role'] ?? '未知';

// --- CRUD 操作處理 ---
$action = $_GET['action'] ?? 'list'; // 預設為顯示列表
$user_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';

// 處理表單提交 (新增 & 更新)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $new_username = $_POST['username'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $new_role = $_POST['role'] ?? '';

    // 驗證輸入 (根據規格書)
    if (empty($new_username) || empty($new_role)) {
        $error_message = '使用者名稱和角色為必填欄位。';
    } else if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]{3,49}$/', $new_username)) {
        $error_message = '使用者名稱必須是4到50個英數字元，且首字不可為數字。';
    } else if (!in_array($new_role, ['admin', 'editor'])) {
        $error_message = '角色只能是 admin 或 editor。';
    } else {
        // 檢查使用者名稱是否重複 (新增時)
        if (empty($id)) {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt_check->bind_param('s', $new_username);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $error_message = '使用者名稱已存在，請使用不同的名稱。';
            }
            $stmt_check->close();
        } else {
            // 檢查使用者名稱是否重複 (更新時，排除自身)
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt_check->bind_param('si', $new_username, $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $error_message = '使用者名稱已存在，請使用不同的名稱。';
            }
            $stmt_check->close();
        }

        if (empty($error_message)) {
            if (!empty($id)) {
                // 更新操作
                $sql = "UPDATE users SET username = ?, role = ?";
                $params = ['ss', $new_username, $new_role];

                if (!empty($new_password)) {
                    // 驗證新密碼
                    if (strlen($new_password) < 8 || strlen($new_password) > 50 || !preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/', $new_password)) {
                        $error_message = '密碼必須是8到50個字元，且需包含英數字。';
                    } else {
                        // 實際應用中應使用 password_hash() 加密密碼
                        // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $hashed_password = $new_password; // 為了原型方便，暫時明文儲存
                        $sql .= ", password = ?";
                        $params[0] .= 's';
                        $params[] = $hashed_password;
                    }
                }
                
                if (empty($error_message)) {
                    $sql .= " WHERE id = ?";
                    $params[0] .= 'i';
                    $params[] = $id;

                    $stmt = $conn->prepare($sql);
                    call_user_func_array([$stmt, 'bind_param'], refValues($params));
                    
                    if ($stmt->execute()) {
                        $success_message = '使用者紀錄更新成功！';
                    } else {
                        $error_message = '更新失敗: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                // 新增操作
                if (empty($new_password)) {
                    $error_message = '新增使用者時密碼為必填。';
                } else if (strlen($new_password) < 8 || strlen($new_password) > 50 || !preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/', $new_password)) {
                    $error_message = '密碼必須是8到50個字元，且需包含英數字。';
                } else {
                    // 實際應用中應使用 password_hash() 加密密碼
                    // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $hashed_password = $new_password; // 為了原型方便，暫時明文儲存
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->bind_param('sss', $new_username, $hashed_password, $new_role);
                    if ($stmt->execute()) {
                        $success_message = '使用者紀錄新增成功！';
                    } else {
                        $error_message = '新增失敗: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// 處理刪除操作
if ($action === 'delete' && !empty($user_id)) {
    // 避免管理員刪除自己
    if ($user_id == $_SESSION['user_id']) {
        $error_message = '您不能刪除自己的帳號。';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            $success_message = '使用者紀錄刪除成功！';
        } else {
            $error_message = '刪除失敗: ' . $stmt->error;
        }
        $stmt->close();
    }
    // 刪除後重置 action, 避免重新整理時再次刪除
    $action = 'list'; 
}

// --- 讀取資料以供顯示 ---
$all_users = [];
$user_to_edit = null;

// 讀取所有使用者列表
$result = $conn->query("SELECT id, username, role, created_at, updated_at FROM users ORDER BY username ASC");
if ($result) {
    $all_users = $result->fetch_all(MYSQLI_ASSOC);
}

// 如果是編輯模式，讀取單筆使用者資料
if ($action === 'edit' && !empty($user_id)) {
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

// 輔助函數，用於 bind_param 的引用傳遞
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+ 
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = & $arr[$key];
        return $refs;
    }
    return $arr;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>使用者管理 - 教會網站管理系統</title>
    <style>
        /* 沿用儀表板樣式，並加上此頁專用樣式 */
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; position: fixed; height: 100%; padding-top: 1rem; }
        .sidebar h2 { text-align: center; color: #fff; margin-bottom: 2rem; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li a { display: block; color: #adb5bd; padding: 1rem 1.5rem; text-decoration: none; transition: background-color 0.3s, color 0.3s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: #495057; color: #fff; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; background-color: #fff; padding: 1rem 2rem; border-bottom: 1px solid #dee2e6; margin-left: 250px; }
        .header .user-info { font-weight: bold; }
        .header .logout a { color: #007bff; text-decoration: none; }
        .content-header { margin-bottom: 2rem; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-container, .list-container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 2rem; }
        .form-container h2, .list-container h2 { margin-top: 0; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-actions { margin-top: 1.5rem; }
        button { padding: .6rem 1.2rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: #fff; }
        .btn-secondary { background-color: #6c757d; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #f2f2f2; }
        .actions a { margin-right: 10px; text-decoration: none; }
        .actions a.edit { color: #28a745; }
        .actions a.delete { color: #dc3545; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>管理系統</h2>
        <ul>
            <li><a href="index.php">儀表板</a></li>
            <li><a href="sermons.php">講道管理</a></li>
            <li><a href="events.php">活動管理</a></li>
            <li><a href="news.php">新聞/公告管理</a></li>
            <li><a href="pages.php">靜態頁面管理</a></li>
            <li><a href="media.php">媒體庫</a></li>
            <?php if ($role === 'admin'): ?>
                <li><a href="users.php" class="active">使用者管理</a></li>
                <li><a href="settings.php">系統設定</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <header class="header">
        <div class="user-info">歡迎您，<?php echo htmlspecialchars($username); ?></div>
        <div class="logout"><a href="../logout.php">登出</a></div>
    </header>

    <main class="main-content">
        <div class="content-header">
            <h1>使用者管理</h1>
        </div>

        <?php if ($success_message): ?><div class="message success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- 新增/編輯表單 -->
        <div class="form-container">
            <h2><?php echo $user_to_edit ? '編輯使用者' : '新增使用者'; ?></h2>
            <form action="users.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $user_to_edit['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="username">使用者名稱</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_to_edit['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">密碼 <?php echo $user_to_edit ? '(留空則不修改)' : ''; ?></label>
                    <input type="password" id="password" name="password" <?php echo $user_to_edit ? '' : 'required'; ?> >
                    <small>8到50個字元，需包含英數字。</small>
                </div>
                <div class="form-group">
                    <label for="role">角色</label>
                    <select id="role" name="role" required>
                        <option value="admin" <?php echo (isset($user_to_edit['role']) && $user_to_edit['role'] === 'admin') ? 'selected' : ''; ?>>管理員</option>
                        <option value="editor" <?php echo (isset($user_to_edit['role']) && $user_to_edit['role'] === 'editor') ? 'selected' : ''; ?>>編輯者</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><?php echo $user_to_edit ? '更新' : '新增'; ?></button>
                    <?php if ($user_to_edit): ?>
                        <a href="users.php" class="btn-secondary" style="text-decoration:none;">取消編輯</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 使用者列表 -->
        <div class="list-container">
            <h2>使用者列表</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>使用者名稱</th>
                        <th>角色</th>
                        <th>建立時間</th>
                        <th>更新時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_users)): ?>
                        <tr><td colspan="6">目前沒有任何使用者紀錄。</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_users as $user_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user_item['id']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['role']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['updated_at']); ?></td>
                            <td class="actions">
                                <a href="users.php?action=edit&id=<?php echo $user_item['id']; ?>" class="edit">編輯</a>
                                <?php if ($user_item['id'] != $_SESSION['user_id']): // 不允許刪除自己 ?>
                                    <a href="users.php?action=delete&id=<?php echo $user_item['id']; ?>" class="delete" onclick="return confirm('您確定要刪除這筆紀錄嗎？');">刪除</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
