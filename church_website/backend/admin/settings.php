<?php
// D:\YU\church_website\backend\admin\settings.php

session_start();
require_once '../includes/db.php';

// --- 安全性檢查：確保使用者已登入且為管理員 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); // 未登入或非管理員導向登入頁
    exit();
}

$username = $_SESSION['username'] ?? '訪客';
$role = $_SESSION['role'] ?? '未知';

$error_message = '';
$success_message = '';

// --- 讀取現有設定 ---
$settings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$stmt->close();

// --- 處理表單提交 (更新設定) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'] ?? '';
    $seo_description = $_POST['seo_description'] ?? '';

    // 儲存或更新設定的輔助函數
    function saveSetting($conn, $key, $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('sss', $key, $value, $value);
        return $stmt->execute();
    }

    $all_saved = true;
    if (!saveSetting($conn, 'site_title', $site_title)) {
        $all_saved = false;
        $error_message .= '網站標題儲存失敗。 ';
    }
    if (!saveSetting($conn, 'seo_description', $seo_description)) {
        $all_saved = false;
        $error_message .= 'SEO 描述儲存失敗。 ';
    }

    if ($all_saved) {
        $success_message = '系統設定已成功更新！';
        // 重新讀取設定以更新顯示
        $settings = [];
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $stmt->close();
    } else if (empty($error_message)) {
        $error_message = '設定更新失敗，請檢查資料庫連線或權限。';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統設定 - 教會網站管理系統</title>
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
        .form-container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 2rem; }
        .form-container h2 { margin-top: 0; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 100px; }
        .form-actions { margin-top: 1.5rem; }
        button { padding: .6rem 1.2rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: #fff; }
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
                <li><a href="users.php">使用者管理</a></li>
                <li><a href="settings.php" class="active">系統設定</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <header class="header">
        <div class="user-info">歡迎您，<?php echo htmlspecialchars($username); ?></div>
        <div class="logout"><a href="../logout.php">登出</a></div>
    </header>

    <main class="main-content">
        <div class="content-header">
            <h1>系統設定</h1>
        </div>

        <?php if ($success_message): ?><div class="message success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <div class="form-container">
            <h2>網站基本設定</h2>
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="site_title">網站標題</label>
                    <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="seo_description">SEO 描述 (Meta Description)</label>
                    <textarea id="seo_description" name="seo_description"><?php echo htmlspecialchars($settings['seo_description'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">儲存設定</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
