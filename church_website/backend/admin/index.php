<?php
// D:\YU\church_website\backend\admin\index.php

session_start(); // 啟動 Session

// 檢查使用者是否登入，若未登入則導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 從 Session 取得使用者資訊
$username = $_SESSION['username'] ?? '訪客';
$role = $_SESSION['role'] ?? '未知';

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>儀表板 - 教會網站管理系統</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            height: 100%;
            padding-top: 1rem;
        }
        .sidebar h2 { text-align: center; color: #fff; margin-bottom: 2rem; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li a {
            display: block;
            color: #adb5bd;
            padding: 1rem 1.5rem;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #495057;
            color: #fff;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
            margin-left: 250px;
        }
        .header .user-info { font-weight: bold; }
        .header .logout a { color: #007bff; text-decoration: none; }
        .header .logout a:hover { text-decoration: underline; }
        .content-header { margin-bottom: 2rem; }
        .card-deck { display: flex; flex-wrap: wrap; gap: 1.5rem; }
        .card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            flex: 1 1 250px; /* Flexbox for responsive cards */
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card h3 { margin-top: 0; color: #007bff; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>管理系統</h2>
        <ul>
            <li><a href="index.php" class="active">儀表板</a></li>
            <li><a href="sermons.php">講道管理</a></li>
            <li><a href="events.php">活動管理</a></li>
            <li><a href="news.php">新聞/公告管理</a></li>
            <li><a href="pages.php">靜態頁面管理</a></li>
            <li><a href="media.php">媒體庫</a></li>
            <?php if ($role === 'admin'): // 只有管理員能看到使用者管理和系統設定 ?>
                <li><a href="users.php">使用者管理</a></li>
                <li><a href="settings.php">系統設定</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <header class="header">
        <div class="user-info">
            歡迎您，<?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($role); ?>)
        </div>
        <div class="logout">
            <a href="../logout.php">登出</a>
        </div>
    </header>

    <main class="main-content">
        <div class="content-header">
            <h1>儀表板</h1>
            <p>您可以在這裡查看網站的整體概況和數據。</p>
        </div>

        <div class="card-deck">
            <div class="card">
                <h3>講道總數</h3>
                <p>TODO: 從資料庫讀取講道數量</p>
            </div>
            <div class="card">
                <h3>最新活動</h3>
                <p>TODO: 顯示即將舉行的活動</p>
            </div>
            <div class="card">
                <h3>待發佈新聞</h3>
                <p>TODO: 顯示草稿狀態的新聞數量</p>
            </div>
        </div>
    </main>

</body>
</html>
