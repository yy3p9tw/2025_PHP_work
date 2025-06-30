<?php
// D:\YU\church_website\backend\admin\media.php

session_start();
require_once '../includes/db.php';

// --- 安全性檢查：確保使用者已登入 ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'] ?? '訪客';
$role = $_SESSION['role'] ?? '未知';

// --- 檔案上傳設定 ---
$upload_dir = '../../assets/uploads/'; // 上傳目錄，相對於此檔案的路徑
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // 如果目錄不存在則建立
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'audio/mpeg', 'video/mp4'];
$max_file_size = 50 * 1024 * 1024; // 50MB

$error_message = '';
$success_message = '';

// 處理檔案上傳
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = '檔案上傳失敗，錯誤碼: ' . $file['error'];
    } else if ($file['size'] > $max_file_size) {
        $error_message = '檔案大小超過限制 (50MB)。';
    } else if (!in_array($file['type'], $allowed_types)) {
        $error_message = '不允許的檔案類型。只允許 JPG, PNG, GIF, PDF, MP3, MP4。';
    } else {
        $file_name = basename($file['name']);
        $file_path = $upload_dir . $file_name;
        $file_type = $file['type'];
        $uploaded_by = $_SESSION['user_id'];

        // 檢查檔案是否已存在，避免覆蓋
        if (file_exists($file_path)) {
            $file_info = pathinfo($file_name);
            $file_name = $file_info['filename'] . '_' . time() . '.' . $file_info['extension'];
            $file_path = $upload_dir . $file_name;
        }

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // 將檔案資訊存入資料庫
            $stmt = $conn->prepare("INSERT INTO media_library (file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $file_name, $file_path, $file_type, $uploaded_by);
            if ($stmt->execute()) {
                $success_message = '檔案上傳成功！';
            } else {
                $error_message = '檔案資訊存入資料庫失敗: ' . $stmt->error;
                unlink($file_path); // 如果資料庫寫入失敗，刪除已上傳的檔案
            }
            $stmt->close();
        } else {
            $error_message = '移動上傳檔案失敗。';
        }
    }
}

// 處理刪除操作
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $media_id = $_GET['id'];

    // 從資料庫取得檔案路徑
    $stmt = $conn->prepare("SELECT file_path FROM media_library WHERE id = ?");
    $stmt->bind_param('i', $media_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $media_item = $result->fetch_assoc();
        $file_to_delete = $media_item['file_path'];

        // 從資料庫刪除紀錄
        $stmt_delete_db = $conn->prepare("DELETE FROM media_library WHERE id = ?");
        $stmt_delete_db->bind_param('i', $media_id);
        if ($stmt_delete_db->execute()) {
            // 從伺服器刪除檔案
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
                $success_message = '檔案及資料庫紀錄刪除成功！';
            } else {
                $success_message = '資料庫紀錄刪除成功，但檔案不存在於伺服器。';
            }
        } else {
            $error_message = '資料庫紀錄刪除失敗: ' . $stmt_delete_db->error;
        }
        $stmt_delete_db->close();
    } else {
        $error_message = '找不到要刪除的媒體紀錄。';
    }
    $stmt->close();
}

// --- 讀取所有媒體檔案列表 ---
$media_items = [];
$result = $conn->query("SELECT * FROM media_library ORDER BY uploaded_at DESC");
if ($result) {
    $media_items = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>媒體庫 - 教會網站管理系統</title>
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
        input[type="file"] { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-actions { margin-top: 1.5rem; }
        button { padding: .6rem 1.2rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #f2f2f2; }
        .media-thumbnail { max-width: 100px; max-height: 100px; object-fit: contain; }
        .actions a { margin-right: 10px; text-decoration: none; color: #dc3545; }
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
            <li><a href="media.php" class="active">媒體庫</a></li>
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
            <h1>媒體庫</h1>
        </div>

        <?php if ($success_message): ?><div class="message success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- 檔案上傳表單 -->
        <div class="form-container">
            <h2>上傳新檔案</h2>
            <form action="media.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="media_file">選擇檔案</label>
                    <input type="file" id="media_file" name="media_file" required>
                    <small>支援類型: JPG, PNG, GIF, PDF, MP3, MP4 (最大 50MB)</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">上傳</button>
                </div>
            </form>
        </div>

        <!-- 已上傳檔案列表 -->
        <div class="list-container">
            <h2>已上傳檔案</h2>
            <table>
                <thead>
                    <tr>
                        <th>預覽</th>
                        <th>檔案名稱</th>
                        <th>類型</th>
                        <th>上傳時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($media_items)): ?>
                        <tr><td colspan="5">目前沒有任何媒體檔案。</td></tr>
                    <?php else: ?>
                        <?php foreach ($media_items as $item): ?>
                        <tr>
                            <td>
                                <?php if (strpos($item['file_type'], 'image/') === 0): ?>
                                    <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="<?php echo htmlspecialchars($item['file_name']); ?>" class="media-thumbnail">
                                <?php elseif ($item['file_type'] === 'application/pdf'): ?>
                                    <a href="<?php echo htmlspecialchars($item['file_path']); ?>" target="_blank">PDF 檔案</a>
                                <?php elseif (strpos($item['file_type'], 'audio/') === 0): ?>
                                    <audio controls src="<?php echo htmlspecialchars($item['file_path']); ?>"></audio>
                                <?php elseif (strpos($item['file_type'], 'video/') === 0): ?>
                                    <video controls width="100" src="<?php echo htmlspecialchars($item['file_path']); ?>"></video>
                                <?php else: ?>
                                    檔案
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['file_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['file_type']); ?></td>
                            <td><?php echo htmlspecialchars($item['uploaded_at']); ?></td>
                            <td class="actions">
                                <a href="media.php?action=delete&id=<?php echo $item['id']; ?>" onclick="return confirm('您確定要刪除這個檔案嗎？');">刪除</a>
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
