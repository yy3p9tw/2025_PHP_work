<?php // 依原檔案複製，如需優化請再告知。

// 除錯用：顯示 POST 與 FILES 內容，方便檢查表單資料與檔案資訊
// print_r($_POST) 會輸出表單欄位資料，print_r($_FILES) 會輸出上傳檔案資訊
// 上線時可註解掉這段
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";

// 取得表單送出的檔案類型（分類）
$type = $_POST['type'];
// 取得表單送出的檔案描述
$description = $_POST['description'];

// 判斷有無多檔案上傳
$names = $_FILES['name']['name'];
$tmp_names = $_FILES['name']['tmp_name'];

// 連接 MySQL 資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, 'root', '');

// 處理多檔案上傳
$success = [];
for ($i = 0; $i < count($names); $i++) {
    if ($names[$i] == '') continue;
    $name = $names[$i];
    $tmp = $tmp_names[$i];
    if (move_uploaded_file($tmp, './files/'.$name)) {
        $sql = "insert into uploads(`name`,`type`,`description`) values ('$name','$type','$description')";
        $pdo->exec($sql);
        $success[] = $name;
    }
}
// 上傳成功後導回 upload.php 並帶上成功檔名與描述
if ($success) {
    header('Location: upload.php?success=' . urlencode(json_encode($success)) . '&desc=' . urlencode($description));
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
