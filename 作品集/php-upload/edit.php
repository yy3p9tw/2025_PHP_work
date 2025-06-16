<?php
// 1. 檢查網址是否有帶入 id 參數，若無則導回管理頁並顯示錯誤訊息，避免未指定編輯目標
if (!isset($_GET['id'])) {
    header('Location: manage.php?msg=未指定編輯項目'); // 重新導向並帶錯誤訊息
    exit;
}
$id = intval($_GET['id']); // 2. 取得 id 並轉為整數型態，防止 SQL injection

// 3. 連接 MySQL 資料庫，設定資料來源名稱（DSN）、帳號、密碼
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, "root", ""); // 建立 PDO 連線物件

// 4. 取得該 id 對應的檔案資料（包含檔名、類型、描述等）
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    // 若找不到該檔案記錄，導回管理頁並顯示錯誤
    header('Location: manage.php?msg=找不到該檔案');
    exit;
}

// 5. 若使用者送出表單（即 HTTP 請求為 POST），則更新資料庫內容
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    // 執行 UPDATE 語法，更新該檔案的 type 與 description 欄位
    $update = $pdo->prepare("UPDATE uploads SET type=?, description=? WHERE id=?");
    $update->execute([$type, $description, $id]);
    // 更新後導回管理頁並顯示成功訊息
    header('Location: manage.php?msg=編輯成功');
    exit;
}

// 分頁設定
$page = $_GET['page'] ?? 1;
$page = max(1, intval($page)); // 確保頁碼為正整數
$limit = 10; // 每頁顯示的項目數
$offset = ($page - 1) * $limit;

// 取得總資料數
$totalStmt = $pdo->query("SELECT COUNT(*) FROM uploads");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// 取得當前頁面的檔案資料
$stmt = $pdo->prepare("SELECT * FROM uploads LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯檔案資訊</title>
    <style>
        body { font-family: 'Noto Sans TC', Arial, sans-serif; background: #f7f8fa; }
        .edit-form {
            background: #fff; max-width: 420px; margin: 40px auto; padding: 32px 28px 24px 28px;
            border-radius: 16px; box-shadow: 0 4px 24px #bfa04633; display: flex; flex-direction: column; gap: 18px;
        }
        .edit-form label { font-weight: bold; color: #1a237e; margin-bottom: 6px; }
        .edit-form input, .edit-form select, .edit-form textarea {
            padding: 10px 12px; border-radius: 8px; border: 1px solid #b6c7e6; font-size: 1em; font-family: inherit; margin-bottom: 8px;
        }
        .edit-form textarea { min-height: 60px; resize: vertical; }
        .edit-form button {
            background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%); color: #fff; border: none; border-radius: 20px;
            padding: 12px 0; font-weight: bold; font-size: 1.08em; cursor: pointer; transition: background 0.2s;
        }
        .edit-form button:hover { background: linear-gradient(90deg, #60a5fa 60%, #3b82f6 100%); }
        .back-link { text-align: center; margin-top: 18px; display: block; color: #3b82f6; text-decoration: underline; }
        .pagination-btn {
            display: inline-block; padding: 8px 12px; border-radius: 12px;
            background: #f1f5f9; color: #1a202c; font-weight: bold; text-align: center;
            transition: background 0.2s, transform 0.2s;
        }
        .pagination-btn:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
        .pagination-btn.active {
            background: #3b82f6; color: #fff;
        }
    </style>
</head>
<body>
    <form class="edit-form" method="post">
        <h2>編輯檔案資訊</h2>
        <div style="text-align:center; margin-bottom:18px;">
        <?php
        $ext = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
        if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $row['name'])): ?>
            <img src="./files/<?= htmlspecialchars($row['name']) ?>" alt="圖片預覽" style="max-width:120px;max-height:80px;border-radius:8px;box-shadow:0 2px 8px #e0e0e0;">
        <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $row['name'])): ?>
            <video src="./files/<?= htmlspecialchars($row['name']) ?>" controls style="max-width:120px;max-height:80px;border-radius:8px;"></video>
        <?php elseif (preg_match('/\.(mp3|wav|ogg)$/i', $row['name'])): ?>
            <audio src="./files/<?= htmlspecialchars($row['name']) ?>" controls style="width:100px;"></audio>
        <?php elseif (preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|txt)$/i', $row['name'])): ?>
            <a href="./files/<?= htmlspecialchars($row['name']) ?>" target="_blank" style="color:#3b82f6;font-weight:bold;">文件下載</a>
        <?php else: ?>
            <span title="無預覽" style="display:inline-block;width:40px;height:40px;vertical-align:middle;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="#bdbdbd" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="4" width="16" height="16" rx="3" fill="#e0e0e0"/>
                <path d="M8 8h8v2H8V8zm0 4h8v2H8v-2zm0 4h5v2H8v-2z" fill="#bdbdbd"/>
              </svg>
            </span>
        <?php endif; ?>
        </div>
        <!-- 顯示檔名（唯讀） -->
        <label>檔名：</label>
        <input type="text" value="<?= htmlspecialchars($row['name']) ?>" disabled>
        <!-- 檔案類型下拉選單，預設選取原本的類型 -->
        <label for="type">檔案類型：</label>
        <select name="type" id="type">
            <option value="image" <?php if($row['type']==='image') echo 'selected'; ?>>影像</option>
            <option value="document" <?php if($row['type']==='document') echo 'selected'; ?>>文件</option>
            <option value="video" <?php if($row['type']==='video') echo 'selected'; ?>>影片</option>
            <option value="music" <?php if($row['type']==='music') echo 'selected'; ?>>音樂</option>
        </select>
        <!-- 檔案描述輸入框，預設帶入原本的描述內容 -->
        <label for="description">檔案描述：</label>
        <textarea name="description" id="description" placeholder="請輸入檔案描述..."><?= htmlspecialchars($row['description']) ?></textarea>
        <!-- 儲存按鈕 -->
        <button type="submit">儲存</button>
        <a href="manage.php" class="back-link">回檔案管理</a>
    </form>

    <!-- 分頁導覽 -->
    <div style="display:flex;justify-content:center;align-items:center;margin:24px 0;position:relative;">
      <?php if (
        $totalPages > 1): ?>
        <div style="margin:0 auto;display:flex;align-items:center;gap:2px;">
        <?php
        $adjacents = 2; // 目前頁碼左右各顯示幾頁
        $showPages = [];
        $showPages[] = 1;
        if ($page - $adjacents > 2) $showPages[] = '...';
        for ($i = max(2, $page - $adjacents); $i <= min($totalPages - 1, $page + $adjacents); $i++) {
          $showPages[] = $i;
        }
        if ($page + $adjacents < $totalPages - 1) $showPages[] = '...';
        if ($totalPages > 1) $showPages[] = $totalPages;
        ?>
        <?php foreach ($showPages as $p): ?>
          <?php if ($p === '...'): ?>
            <span class="pagination-btn" style="pointer-events:none;color:#bbb;">...</span>
          <?php elseif ($p == $page): ?>
            <a href="?q=<?= urlencode($q) ?>&page=<?= $p ?>" class="pagination-btn active"> <?= $p ?> </a>
          <?php else: ?>
            <a href="?q=<?= urlencode($q) ?>&page=<?= $p ?>" class="pagination-btn"> <?= $p ?> </a>
          <?php endif; ?>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
</body>
</html>
