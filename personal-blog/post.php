<?php
// 前台：單篇文章頁
require_once __DIR__.'/includes/db.php';
$id = intval($_GET['id'] ?? 0);
// 自動累加 view_count
if ($id) {
    db()->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id=?")->execute([$id]);
}
$post = get_post($id);
if (!$post) die('找不到文章');
$tags = get_post_tags($id);
$categories = get_all_categories();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?> | 我的部落格</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/blog.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">MyBlog</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/">首頁</a></li>
        <li class="nav-item"><a class="nav-link" href="#">分類</a></li>
        <li class="nav-item"><a class="nav-link" href="admin/login.php">管理</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card mb-4 shadow-sm blog-card">
        <?php if(!empty($post['cover_img'])): ?>
        <img src="<?=htmlspecialchars($post['cover_img'])?>" class="card-img-top" alt="cover">
        <?php endif; ?>
        <div class="card-body">
          <h1 class="card-title mb-3"> <?=htmlspecialchars($post['title'])?> </h1>
          <div class="mb-2 text-muted" style="font-size:0.95em;">
            <?=date('Y-m-d', strtotime($post['created_at']))?>
            <?php if($post['category_name']): ?>
              · <span class="badge bg-info text-dark"> <?=htmlspecialchars($post['category_name'])?> </span>
            <?php endif; ?>
            · <span class="badge bg-light text-dark">👁️ <?=$post['view_count']??0?></span>
          </div>
          <?php if($tags): ?>
          <div class="mb-2">
            <?php foreach($tags as $t): ?>
              <a href="index.php?tag=<?=urlencode($t['name'])?>" class="badge bg-secondary text-light me-1">#<?=htmlspecialchars($t['name'])?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="card-text fs-5"> <?=nl2br(htmlspecialchars($post['content']))?> </div>
        </div>
      </div>
      <a href="index.php" class="btn btn-outline-secondary">← 返回列表</a>
    </div>
    <div class="col-lg-4">
      <div class="p-3 mb-4 bg-light rounded-3 shadow-sm">
        <h5 class="mb-3">分類</h5>
        <ul class="list-unstyled">
          <?php foreach($categories as $cat): ?>
          <li><a href="index.php?cat=<?=$cat['id']?>" class="text-decoration-none text-secondary"> <?=htmlspecialchars($cat['name'])?> </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<footer class="text-center py-4 mt-5 text-muted">© <?=date('Y')?> MyBlog</footer>
</body>
</html>
