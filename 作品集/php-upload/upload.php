<?php
/**
 * 1.建立表單
 * 2.建立處理檔案程式
 * 3.搬移檔案
 * 4.顯示檔案列表
 */

// 上傳成功訊息與預覽
$success = isset($_GET['success']) ? json_decode($_GET['success'], true) : [];
$description = isset($_GET['desc']) ? $_GET['desc'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>檔案上傳</title>
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
        .upload-form {
            background: #fff;
            max-width: 420px;
            margin: 40px auto;
            padding: 32px 28px 24px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 24px #bfa04633;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .upload-form label {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 6px;
        }
        .upload-form input[type="file"],
        .upload-form select,
        .upload-form textarea {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #b6c7e6;
            font-size: 1em;
            font-family: inherit;
            margin-bottom: 8px;
        }
        .upload-form textarea {
            min-height: 60px;
            resize: vertical;
        }
        .upload-form button {
            background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 12px 0;
            font-weight: bold;
            font-size: 1.08em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .upload-form button:hover {
            background: linear-gradient(90deg, #60a5fa 60%, #3b82f6 100%);
        }
        .upload-form .desc-label {
            margin-top: 8px;
        }
        .manage-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #3b82f6;
            text-decoration: underline;
            font-weight: bold;
            font-size: 1.1em;
        }
        .manage-link:hover {
            color: #1565c0;
        }
        .result-box {
            background: #fff;
            max-width: 800px;
            margin: 20px auto;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 24px #bfa04633;
        }
        .result-title {
            font-size: 1.5em;
            color: #3b82f6;
            margin-bottom: 16px;
            text-align: center;
        }
        .file-item {
            background: #f7f8fa;
            border-radius: 10px;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 16px 12px;
            text-align: center;
            width: 160px;
        }
        .filename {
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 4px;
        }
        .desc {
            color: #666;
            font-size: 0.98em;
        }
        .pagination-btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #3b82f6;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .pagination-btn:hover {
            background: #e0e7ff;
        }
        .pagination-btn.active {
            background: #3b82f6;
            color: #fff;
        }
    </style>
</head>
<body>
 <h1 class="header">檔案上傳練習</h1>
 <form class="upload-form" action="uploaded_files.php" method="post" enctype="multipart/form-data">
    <label for="name">選擇檔案上傳（可多選）：</label>
    <input type="file" name="name[]" id="name" multiple required>

    <label for="type">檔案類型：</label>
    <select name="type" id="type">
        <option value="image">影像</option>
        <option value="document">文件</option>
        <option value="video">影片</option>
        <option value="music">音樂</option>
    </select>

    <label for="description" class="desc-label">檔案描述：</label>
    <textarea name="description" id="description" placeholder="請輸入檔案描述..."></textarea>

    <button type="submit">上傳檔案</button>
</form>
<a href="manage.php" class="manage-link">查看所有上傳檔案</a>

<?php if (!empty($success)): ?>
<div id="overlay" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(30,30,30,0.45);z-index:9998;"></div>
<div id="popup-preview" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;max-width:90vw;max-height:90vh;overflow:auto;box-shadow:0 8px 32px #2228;min-width:calc(800px + 50px);min-height:calc(300px + 50px);">
  <button id="close-popup" style="position:absolute;top:10px;right:16px;background:#fff;border:none;border-radius:50%;width:32px;height:32px;box-shadow:0 2px 8px #bbb;cursor:pointer;font-size:1.3em;line-height:32px;color:#3b82f6;z-index:10001;">&times;</button>
  <div class="result-box" style="margin:0;box-shadow:none;max-width:900px;min-width:800px;min-height:300px;">
    <div class="result-title" style="font-size:2em;">上傳成功！預覽如下</div>
    <div class="file-list" style="display:flex;flex-wrap:wrap;gap:18px;justify-content:center;">
      <?php foreach ($success as $name): ?>
        <div class="file-item" style="width:210px;min-height:180px;">
          <?php 
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $name)) {
              echo '<img src="./files/'.htmlspecialchars($name).'" alt="圖片預覽" style="max-width:180px;max-height:120px;border-radius:8px;margin-bottom:8px;">';
            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $name)) {
              echo '<video src="./files/'.htmlspecialchars($name).'" controls style="max-width:180px;max-height:120px;border-radius:8px;margin-bottom:8px;"></video>';
            } elseif (preg_match('/\.(mp3|wav|ogg)$/i', $name)) {
              echo '<audio src="./files/'.htmlspecialchars($name).'" controls style="width:180px;margin-bottom:8px;"></audio>';
            } elseif (preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|txt)$/i', $name)) {
              echo '<a href="./files/'.htmlspecialchars($name).'" target="_blank" style="color:#3b82f6;font-weight:bold;">文件下載</a>';
            } else {
              echo '<span title="無預覽" style="display:inline-block;width:50px;height:50px;vertical-align:middle;"><svg width="50" height="50" viewBox="0 0 24 24" fill="#bdbdbd" xmlns="http://www.w3.org/2000/svg"><rect x="4" y="4" width="16" height="16" rx="3" fill="#e0e0e0"/><path d="M8 8h8v2H8V8zm0 4h8v2H8v-2zm0 4h5v2H8v-2z" fill="#bdbdbd"/></svg></span>';
            }
          ?>
          <div class="filename" style="font-size:1.15em; margin-bottom:6px;"><?= htmlspecialchars($name) ?></div>
          <div class="desc" style="font-size:1em;"><?= htmlspecialchars($description) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
  var closeBtn = document.getElementById('close-popup');
  var overlay = document.getElementById('overlay');
  closeBtn.onclick = function() {
    document.getElementById('popup-preview').style.display = 'none';
    overlay.style.display = 'none';
    window.location.href = 'upload.php';
  };
  overlay.onclick = function() {
    document.getElementById('popup-preview').style.display = 'none';
    overlay.style.display = 'none';
    window.location.href = 'upload.php';
  };
  setTimeout(function(){
    window.location.href = 'upload.php';
  }, 3000);
</script>
<?php endif; ?>

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
