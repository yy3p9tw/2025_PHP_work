<?php
// D:\YU\church_website\backend\admin\news.php

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
$news_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';

// 處理表單提交 (新增 & 更新)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $published_at = $_POST['published_at'] ?? '';

    // 簡單驗證 (根據規格書)
    if (empty($title) || empty($content) || empty($published_at)) {
        $error_message = '標題、內容和發佈時間為必填欄位。';
    } else if (strlen($content) < 10) {
        $error_message = '內容至少需要 10 個字元。';
    } else {
        if (!empty($id)) {
            // 更新操作
            $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, published_at = ? WHERE id = ?");
            $stmt->bind_param('sssi', $title, $content, $published_at, $id);
            if ($stmt->execute()) {
                $success_message = '新聞/公告紀錄更新成功！';
            } else {
                $error_message = '更新失敗: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            // 新增操作
            $stmt = $conn->prepare("INSERT INTO news (title, content, published_at) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $title, $content, $published_at);
            if ($stmt->execute()) {
                $success_message = '新聞/公告紀錄新增成功！';
            } else {
                $error_message = '新增失敗: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// 處理刪除操作
if ($action === 'delete' && !empty($news_id)) {
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param('i', $news_id);
    if ($stmt->execute()) {
        $success_message = '新聞/公告紀錄刪除成功！';
    } else {
        $error_message = '刪除失敗: ' . $stmt->error;
    }
    $stmt->close();
    // 刪除後重置 action, 避免重新整理時再次刪除
    $action = 'list'; 
}

// --- 讀取資料以供顯示 ---
$all_news = [];
$news_to_edit = null;

// 讀取所有新聞/公告列表
$result = $conn->query("SELECT * FROM news ORDER BY published_at DESC");
if ($result) {
    $all_news = $result->fetch_all(MYSQLI_ASSOC);
}

// 如果是編輯模式，讀取單筆新聞/公告資料
if ($action === 'edit' && !empty($news_id)) {
    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->bind_param('i', $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $news_to_edit = $result->fetch_assoc();
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
    <title>新聞/公告管理 - 教會網站管理系統</title>
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
        input[type="text"], input[type="datetime-local"], textarea { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 120px; }
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
            <li><a href="news.php" class="active">新聞/公告管理</a></li>
            <li><a href="pages.php">靜態頁面管理</a></li>
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
            <h1>新聞/公告管理</h1>
        </div>

        <?php if ($success_message): ?><div class="message success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- 新增/編輯表單 -->
        <div class="form-container">
            <h2><?php echo $news_to_edit ? '編輯新聞/公告' : '新增新聞/公告'; ?></h2>
            <form action="news.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $news_to_edit['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="title">標題</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($news_to_edit['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="content">內容</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($news_to_edit['content'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="published_at">發佈時間</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="<?php echo htmlspecialchars(str_replace(' ', 'T', $news_to_edit['published_at'] ?? date('Y-m-d H:i:s'))); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><?php echo $news_to_edit ? '更新' : '新增'; ?></button>
                    <?php if ($news_to_edit): ?>
                        <a href="news.php" class="btn-secondary" style="text-decoration:none;">取消編輯</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 新聞/公告列表 -->
        <div class="list-container">
            <h2>新聞/公告列表</h2>
            <table>
                <thead>
                    <tr>
                        <th>標題</th>
                        <th>發佈時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_news)): ?>
                        <tr><td colspan="3">目前沒有任何新聞/公告紀錄。</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_news as $news_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($news_item['title']); ?></td>
                            <td><?php echo htmlspecialchars($news_item['published_at']); ?></td>
                            <td class="actions">
                                <a href="news.php?action=edit&id=<?php echo $news_item['id']; ?>" class="edit">編輯</a>
                                <a href="news.php?action=delete&id=<?php echo $news_item['id']; ?>" class="delete" onclick="return confirm('您確定要刪除這筆紀錄嗎？');">刪除</a>
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
