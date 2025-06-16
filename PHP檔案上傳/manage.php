<?php
/**
 * 1.建立資料庫及資料表來儲存檔案資訊
 * 2.建立上傳表單頁面
 * 3.取得檔案資訊並寫入資料表
 * 4.製作檔案管理功能頁面
 */
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>檔案管理功能</title>
    <style>
        body {
            font-family: 'Noto Sans TC', Arial, sans-serif;
            background: #f7f8fa;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            font-size: 2em;
            color: #3b82f6;
            margin: 32px 0 24px 0;
            letter-spacing: 2px;
        }
        .table {
            width: 90%;
            margin: 32px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px #bfa04633;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        .table th {
            background: #e0e7ff;
            color: #1a237e;
            font-size: 1.1em;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .file-img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #e0e0e0;
        }
        .btn {
            padding: 6px 18px;
            border-radius: 20px;
            background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%);
            color: #fff;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin: 0 4px;
        }
        .btn:hover {
            background: linear-gradient(90deg, #60a5fa 60%, #3b82f6 100%);
        }
        .pagination-btn {
            text-decoration: none !important;
            background: none !important;
            color: #1a237e !important;
            border: none;
            font-weight: bold;
            padding: 6px 12px;
            margin: 0 2px;
        }
        .pagination-btn.active {
            color: #ef4444 !important;
            text-decoration: none !important;
        }
    </style>
</head>
<body>
<h1 class="header">檔案管理練習</h1>
<div style="text-align:center; margin: 24px 0;">
  <a href="upload.php" class="btn" style="background:linear-gradient(90deg,#3b82f6 60%,#60a5fa 100%);color:#fff;">回上傳表單</a>
</div>
<?php
// 連接 MySQL 資料庫，設定資料來源名稱（DSN）、帳號、密碼
$dns = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dns, "root", ""); // 建立 PDO 連線物件

// 取得目前頁數與每頁顯示數量
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

// 搜尋條件
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '';
$params = [];
if ($q !== '') {
  $where = 'WHERE name LIKE ? OR description LIKE ?';
  $params = ['%'.$q.'%', '%'.$q.'%'];
}
// 取得總筆數
$countSql = "SELECT COUNT(*) FROM uploads $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $pageSize);
// 取得分頁資料
$sql = "SELECT * FROM uploads $where ORDER BY id DESC LIMIT $pageSize OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 搜尋表單 -->
<form method="get" style="text-align:center; margin-bottom:18px;">
  <input type="text" name="q" placeholder="輸入檔名或描述關鍵字" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" style="padding:8px 12px; border-radius:8px; border:1px solid #b6c7e6; width:220px;">
  <button type="submit" class="btn" style="padding:8px 22px;">搜尋</button>
</form>
<form method="post" action="batch_action.php">
<table class="table">
    <tr>
        <th><input type="checkbox" id="checkAll" onclick="toggleAll(this)"></th>
        <th>ID</th> <!-- 資料表主鍵 -->
        <th>檔案預覽</th> <!-- 根據副檔名顯示圖片、影片、音樂、文件或 icon -->
        <th>檔名</th> <!-- 上傳時的原始檔名 -->
        <th>類型</th> <!-- 使用者自訂分類（image/document/video/music） -->
        <th>描述</th> <!-- 使用者自訂描述文字 -->
        <th>操作</th> <!-- 下載、編輯、刪除功能 -->
    </tr>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><input type="checkbox" name="ids[]" value="<?= $row['id'] ?>"></td>
            <td><?= $row['id'] ?></td>
            <td>
                <?php
                // 取得副檔名，判斷檔案型態以決定預覽方式
                $ext = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
                // 圖片類型預覽
                if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $row['name'])): ?>
                    <img src="./files/<?= htmlspecialchars($row['name']) ?>" class="file-img" alt="圖片預覽">
                <?php // 影片類型預覽
                elseif (preg_match('/\.(mp4|webm|ogg)$/i', $row['name'])): ?>
                    <video src="./files/<?= htmlspecialchars($row['name']) ?>" class="file-img" controls style="max-width:120px;max-height:80px;"></video>
                <?php // 音樂類型預覽
                elseif (preg_match('/\.(mp3|wav|ogg)$/i', $row['name'])): ?>
                    <audio src="./files/<?= htmlspecialchars($row['name']) ?>" controls style="width:100px;"></audio>
                <?php // 文件類型提供下載連結
                elseif (preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|txt)$/i', $row['name'])): ?>
                    <a href="./files/<?= htmlspecialchars($row['name']) ?>" target="_blank" style="color:#3b82f6;font-weight:bold;">文件下載</a>
                <?php // 其他未知類型顯示 icon
                else: ?>
                    <span title="無預覽" style="display:inline-block;width:40px;height:40px;vertical-align:middle;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="#bdbdbd" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="4" width="16" height="16" rx="3" fill="#e0e0e0"/>
            <path d="M8 8h8v2H8V8zm0 4h8v2H8v-2zm0 4h5v2H8v-2z" fill="#bdbdbd"/>
          </svg>
        </span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td> <!-- 顯示檔名，避免 XSS -->
            <td><?= htmlspecialchars($row['type']) ?></td> <!-- 顯示分類 -->
            <td><?= htmlspecialchars($row['description']) ?></td> <!-- 顯示描述 -->
            <td>
                <!-- 下載：直接下載檔案 -->
                <a href="./files/<?= htmlspecialchars($row['name']) ?>" class="btn" download>下載</a>
                <!-- 編輯：跳轉到編輯頁，帶入 id 參數 -->
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn" style="background:linear-gradient(90deg,#fbbf24 60%,#fde68a 100%);color:#222;">編輯</a>
                <!-- 刪除：跳轉到刪除頁，帶入 id 參數，點擊時彈出確認視窗 -->
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn" style="background:linear-gradient(90deg,#ef4444 60%,#fca5a5 100%);color:#fff;" onclick="return confirm('確定要刪除這個檔案嗎？');">刪除</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<div style="text-align:center; margin:18px 0;">
  <button type="submit" name="download" class="btn" style="background:linear-gradient(90deg,#4caf50 60%,#a5d6a7 100%);color:#fff;">批次下載</button>
  <button type="submit" name="delete" class="btn" style="background:linear-gradient(90deg,#ef4444 60%,#fca5a5 100%);color:#fff;" onclick="return confirm('確定要批次刪除選取檔案嗎？');">批次刪除</button>
</div>
</form>
<!-- 分頁導覽 -->
<div style="display:flex;justify-content:center;align-items:center;margin:24px 0;position:relative;">
  <?php if ($totalPages > 1): ?>
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
<script>
function toggleAll(box) {
  const cbs = document.querySelectorAll('input[name="ids[]"]');
  cbs.forEach(cb => cb.checked = box.checked);
}
</script>
</body>
</html>