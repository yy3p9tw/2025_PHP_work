<?php
// 檢查是否有傳入 id 參數，若無則導回管理頁並顯示錯誤訊息
if (!isset($_GET['id'])) {
    header('Location: manage.php?msg=未指定刪除項目');
    exit;
}
$id = intval($_GET['id']);

// 連接 MySQL 資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, "root", "");

// 取得要刪除的檔案名稱
$stmt = $pdo->prepare("SELECT name FROM uploads WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
    $filename = $file['name'];
    // 刪除資料庫中的檔案記錄
    $pdo->prepare("DELETE FROM uploads WHERE id=?")->execute([$id]);
    // 刪除實體檔案（若存在）
    $filepath = __DIR__ . '/files/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    header('Location: manage.php?msg=刪除成功');
    exit;
} else {
    header('Location: manage.php?msg=找不到該檔案');
    exit;
}
?>
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
