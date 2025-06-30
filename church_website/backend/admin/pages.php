<?php
// D:\YU\church_website\backend\admin\pages.php

session_start();
require_once '../includes/db.php';

// --- 安全性檢查：確保使用者已登入 ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'] ?? '訪客';
$role = $_SESSION['role'] ?? '未知';

// --- CRUD 操作處理 ---
$action = $_GET['action'] ?? 'list'; // 預設為顯示列表
$page_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';

// 處理表單提交 (新增 & 更新)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $slug = $_POST['slug'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    // 簡單驗證
    if (empty($slug) || empty($title)) {
        $error_message = 'Slug 和標題為必填欄位。';
    } else {
        // 檢查 slug 是否重複 (新增時)
        if (empty($id)) {
            $stmt_check = $conn->prepare("SELECT id FROM pages WHERE slug = ?");
            $stmt_check->bind_param('s', $slug);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $error_message = 'Slug 已存在，請使用不同的 Slug。';
            }
            $stmt_check->close();
        } else {
            // 檢查 slug 是否重複 (更新時，排除自身)
            $stmt_check = $conn->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
            $stmt_check->bind_param('si', $slug, $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $error_message = 'Slug 已存在，請使用不同的 Slug。';
            }
            $stmt_check->close();
        }

        if (empty($error_message)) {
            if (!empty($id)) {
                // 更新操作
                $stmt = $conn->prepare("UPDATE pages SET slug = ?, title = ?, content = ? WHERE id = ?");
                $stmt->bind_param('sssi', $slug, $title, $content, $id);
                if ($stmt->execute()) {
                    $success_message = '靜態頁面更新成功！';
                } else {
                    $error_message = '更新失敗: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                // 新增操作
                $stmt = $conn->prepare("INSERT INTO pages (slug, title, content) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $slug, $title, $content);
                if ($stmt->execute()) {
                    $success_message = '靜態頁面新增成功！';
                } else {
                    $error_message = '新增失敗: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// 處理刪除操作
if ($action === 'delete' && !empty($page_id)) {
    $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->bind_param('i', $page_id);
    if ($stmt->execute()) {
        $success_message = '靜態頁面刪除成功！';
    } else {
        $error_message = '刪除失敗: ' . $stmt->error;
    }
    $stmt->close();
    // 刪除後重置 action, 避免重新整理時再次刪除
    $action = 'list'; 
}

// --- 讀取資料以供顯示 ---
$all_pages = [];
$page_to_edit = null;

// 讀取所有靜態頁面列表
$result = $conn->query("SELECT * FROM pages ORDER BY title ASC");
if ($result) {
    $all_pages = $result->fetch_all(MYSQLI_ASSOC);
}

// 如果是編輯模式，讀取單筆靜態頁面資料
if ($action === 'edit' && !empty($page_id)) {
    $stmt = $conn->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->bind_param('i', $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $page_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>靜態頁面管理 - 教會網站管理系統</title>
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
        input[type="text"], textarea { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 200px; }
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
            <li><a href="pages.php" class="active">靜態頁面管理</a></li>
            <li><a href="media.php">媒體庫</a></li>
            <?php if ($role === 'admin'): ?>
                <li><a href="users.php">使用者管理</a></li>
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
            <h1>靜態頁面管理</h1>
        </div>

        <?php if ($success_message): ?><div class="message success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- 新增/編輯表單 -->
        <div class="form-container">
            <h2><?php echo $page_to_edit ? '編輯靜態頁面' : '新增靜態頁面'; ?></h2>
            <form action="pages.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $page_to_edit['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="title">標題</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($page_to_edit['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug (網址識別碼)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($page_to_edit['slug'] ?? ''); ?>" required>
                    <small>例如: about-us, contact-us. 只能包含小寫字母、數字和連字號(-)。</small>
                </div>
                <div class="form-group">
                    <label for="content">內容</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($page_to_edit['content'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><?php echo $page_to_edit ? '更新' : '新增'; ?></button>
                    <?php if ($page_to_edit): ?>
                        <a href="pages.php" class="btn-secondary" style="text-decoration:none;">取消編輯</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 靜態頁面列表 -->
        <div class="list-container">
            <h2>靜態頁面列表</h2>
            <table>
                <thead>
                    <tr>
                        <th>標題</th>
                        <th>Slug</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_pages)): ?>
                        <tr><td colspan="3">目前沒有任何靜態頁面紀錄。</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_pages as $page_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($page_item['title']); ?></td>
                            <td><?php echo htmlspecialchars($page_item['slug']); ?></td>
                            <td class="actions">
                                <a href="pages.php?action=edit&id=<?php echo $page_item['id']; ?>" class="edit">編輯</a>
                                <a href="pages.php?action=delete&id=<?php echo $page_item['id']; ?>" class="delete" onclick="return confirm('您確定要刪除這筆紀錄嗎？');">刪除</a>
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
