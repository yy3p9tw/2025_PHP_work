<?php
// 前台首頁：文章列表（支援分類篩選、分頁、標題搜尋、標籤雲）
require_once __DIR__.'/includes/db.php';

$categories = get_all_categories();
$all_tags = get_all_tags();

// 標籤篩選
$tag = trim($_GET['tag'] ?? '');
$tag_id = 0;
if ($tag !== '') {
    $tag_row = get_tag_by_name($tag);
    if ($tag_row) $tag_id = $tag_row['id'];
}

// 分類篩選
$cat_id = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$where = $cat_id ? 'WHERE p.category_id=' . $cat_id : '';

// 搜尋
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $where .= ($where ? ' AND ' : 'WHERE ') . "p.title LIKE " . db()->quote('%'.$q.'%');
}

// 標籤篩選條件
if ($tag_id) {
    $where .= ($where ? ' AND ' : 'WHERE ') . "EXISTS (SELECT 1 FROM posts_tags pt WHERE pt.post_id=p.id AND pt.tag_id=".intval($tag_id).")";
}

// 分頁參數
$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 5;
$offset = ($page-1)*$pageSize;

// 取得文章總數
$sql_count = "SELECT COUNT(*) FROM posts p $where";
$total = db()->query($sql_count)->fetchColumn();
$totalPages = ceil($total/$pageSize);

// 取得分頁文章（精選文章優先，精選置頂）
$sql = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id=c.id $where ORDER BY p.is_featured DESC, p.created_at DESC LIMIT $offset, $pageSize";
$posts = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>我的部落格</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="這是我的個人筆記與心得部落格，分享學習與生活點滴。">
    <meta property="og:title" content="我的部落格">
    <meta property="og:description" content="這是我的個人筆記與心得部落格，分享學習與生活點滴。">
    <meta property="og:type" content="website">
    <meta property="og:image" content="assets/og-image.png">
    <link rel="icon" href="assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/blog.css" rel="stylesheet">
</head>
<body>
<button id="darkToggle" class="btn btn-sm btn-outline-secondary position-fixed end-0 bottom-0 m-3" style="z-index:9999;">🌙 夜間模式</button>
<style>
.blog-card .card-img-top {object-fit:cover;height:220px;}
.category-badge:hover {background:#0dcaf0;color:#fff;}
.tag-badge:hover {background:#6c757d;color:#fff;}
.blog-card {transition:box-shadow .2s,transform .2s;}
.blog-card:hover {box-shadow:0 8px 32px rgba(0,0,0,0.18);transform:translateY(-2px) scale(1.01);}
#toTopBtn {position:fixed;right:20px;bottom:70px;z-index:9999;display:none;}
@media (max-width: 768px) {
  .blog-card .card-img-top {height:140px;}
}
body.dark-mode {background:#181a1b;color:#e0e0e0;}
body.dark-mode .card,body.dark-mode .bg-light,body.dark-mode .bg-white{background:#23272b!important;color:#e0e0e0;}
body.dark-mode .navbar,.dark-mode .pagination .page-link{background:#23272b!important;}
body.dark-mode .card-title a,body.dark-mode .nav-link,body.dark-mode .text-dark{color:#e0e0e0!important;}
body.dark-mode .category-badge{background:#0dcaf0!important;color:#fff!important;}
body.dark-mode .tag-badge{background:#6c757d!important;color:#fff!important;}
.blog-card.featured {border:2px solid #ffc107;box-shadow:0 0 16px #ffe082;}
.post-title-link:hover {color:#0d6efd!important;text-decoration:underline;}
.accordion-button:focus {box-shadow:none;}
</style>
<script>
// 夜間模式切換
const btn=document.getElementById('darkToggle');
btn.onclick=function(){
  document.body.classList.toggle('dark-mode');
  localStorage.setItem('dark',document.body.classList.contains('dark-mode'));
};
if(localStorage.getItem('dark')==='true')document.body.classList.add('dark-mode');
// 捲動至頂
window.onscroll=function(){
  document.getElementById('toTopBtn').style.display=(window.scrollY>200)?'block':'none';
};
function toTop(){window.scrollTo({top:0,behavior:'smooth'});}
</script>
<button id="toTopBtn" class="btn btn-primary rounded-circle" onclick="toTop()">↑</button>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
      <img src="assets/logo.svg" alt="logo" style="height:32px;width:32px;object-fit:contain;margin-right:8px;">
      MyBlog
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">首頁</a></li>
        <li class="nav-item"><a class="nav-link" href="#">分類</a></li>
        <li class="nav-item"><a class="nav-link" href="admin/login.php">管理</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="container">
  <div class="row g-4">
    <div class="col-lg-8">
      <form class="mb-4" method="get">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="搜尋文章標題..." value="<?= htmlspecialchars($q) ?>">
          <?php if($cat_id): ?><input type="hidden" name="cat" value="<?= $cat_id ?>"><?php endif; ?>
          <?php if($tag): ?><input type="hidden" name="tag" value="<?= htmlspecialchars($tag) ?>"><?php endif; ?>
          <button class="btn btn-outline-secondary" type="submit">搜尋</button>
        </div>
      </form>
      <?php if(empty($posts)): ?>
        <div class="alert alert-warning">查無資料，請嘗試其他搜尋或篩選條件。</div>
      <?php endif; ?>
      <?php foreach($posts as $post): ?>
      <article class="card mb-4 shadow-sm blog-card<?=($post['is_featured']?' featured':'')?>
        <?php if(!empty($post['cover_img'])): ?>
        <img src="<?=htmlspecialchars($post['cover_img'])?>" class="card-img-top" alt="cover" style="object-fit:cover;height:220px;">
        <?php else: ?>
        <img src="https://undraw.co/api/illustrations/undraw_Blogging_re_kl0d.svg" class="card-img-top" alt="cover" style="object-fit:cover;height:220px;">
        <?php endif; ?>
        <div class="card-body">
          <h3 class="card-title"><a href="post.php?id=<?=$post['id']?>" class="text-decoration-none text-dark post-title-link"> <?=htmlspecialchars($post['title'])?> </a>
            <?php if($post['is_featured']): ?><span class="badge bg-warning text-dark ms-2">精選</span><?php endif; ?>
          </h3>
          <div class="mb-2 text-muted" style="font-size:0.95em;">
            <?=date('Y-m-d', strtotime($post['created_at']))?>
            <?php if($post['category_name']): ?>
              · <span class="badge bg-info text-dark category-badge"><?=htmlspecialchars($post['category_name'])?></span>
            <?php endif; ?>
            <?php if(!empty($post['user_id'])): ?>
              · <span class="badge bg-secondary">作者：<?=htmlspecialchars(get_user_by_id($post['user_id'])['username']??'')?></span>
            <?php endif; ?>
            · <span class="badge bg-light text-dark">👁️ <?=$post['view_count']??0?></span>
          </div>
          <p class="card-text">
            <?php if(!empty($post['summary'])): ?>
              <?=htmlspecialchars($post['summary'])?>
            <?php else: ?>
              <?=mb_substr(strip_tags($post['content']),0,100)?>...
            <?php endif; ?>
          </p>
          <?php $tags = get_post_tags($post['id']); if($tags): ?>
          <div class="mb-2">
            <?php foreach($tags as $t): ?>
              <a href="?tag=<?=urlencode($t['name'])?>" class="badge bg-secondary text-light me-1 tag-badge<?=($tag==$t['name']?' fw-bold':'')?>">#<?=htmlspecialchars($t['name'])?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between align-items-center">
            <a href="post.php?id=<?=$post['id']?>" class="btn btn-outline-primary btn-sm">閱讀全文</a>
            <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(location.origin+'/post.php?id=<?=$post['id']?>');alert('已複製文章連結！')">分享</button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <!-- 分頁 -->
      <nav>
        <ul class="pagination">
          <li class="page-item<?=($page==1?' disabled':'') ?>">
            <a class="page-link" href="?<?=($cat_id?('cat='.$cat_id.'&'):'')?><?=($tag!==''?'tag='.urlencode($tag).'&':'')?><?=($q!==''?'q='.urlencode($q).'&':'')?>page=<?=max(1,$page-1)?>">上一頁</a>
          </li>
          <?php for($i=1;$i<=$totalPages;$i++): ?>
            <li class="page-item <?=($i==$page)?'active':''?>">
              <a class="page-link" href="?<?=($cat_id?('cat='.$cat_id.'&'):'')?><?=($tag!==''?'tag='.urlencode($tag).'&':'')?><?=($q!==''?'q='.urlencode($q).'&':'')?>page=<?=$i?>"><?=$i?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item<?=($page==$totalPages?' disabled':'') ?>">
            <a class="page-link" href="?<?=($cat_id?('cat='.$cat_id.'&'):'')?><?=($tag!==''?'tag='.urlencode($tag).'&':'')?><?=($q!==''?'q='.urlencode($q).'&':'')?>page=<?=min($totalPages,$page+1)?>">下一頁</a>
          </li>
        </ul>
      </nav>
    </div>
    <div class="col-lg-4">
      <div class="p-3 mb-4 bg-light rounded-3 shadow-sm">
        <h5 class="mb-3">分類</h5>
        <div class="accordion mb-4" id="sideAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="catHeading">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#catCollapse" aria-expanded="true" aria-controls="catCollapse">分類</button>
            </h2>
            <div id="catCollapse" class="accordion-collapse collapse show" aria-labelledby="catHeading" data-bs-parent="#sideAccordion">
              <div class="accordion-body p-0">
                <ul class="list-unstyled mb-0">
                  <?php foreach($categories as $cat): ?>
                  <li><a href="?cat=<?=$cat['id']?><?=($tag!==''?'&tag='.urlencode($tag):'')?><?=($q!==''?'&q='.urlencode($q):'')?>" class="text-decoration-none text-secondary<?=($cat_id==$cat['id']?' fw-bold':'')?>"><?=htmlspecialchars($cat['name'])?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="tagHeading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tagCollapse" aria-expanded="false" aria-controls="tagCollapse">標籤雲</button>
            </h2>
            <div id="tagCollapse" class="accordion-collapse collapse" aria-labelledby="tagHeading" data-bs-parent="#sideAccordion">
              <div class="accordion-body p-0">
                <?php foreach($all_tags as $t): ?>
                  <a href="?tag=<?=urlencode($t['name'])?>" class="badge bg-secondary text-light mb-1 <?=($tag==$t['name']?'fw-bold':'')?>">#<?=htmlspecialchars($t['name'])?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="p-3 bg-white rounded-3 shadow-sm">
        <h5 class="mb-3">關於本站</h5>
        <img src="https://undraw.co/api/illustrations/undraw_Reading_re_29f8.svg" class="img-fluid mb-2" alt="about">
        <p>這是我的個人筆記與心得部落格，歡迎參觀！</p>
      </div>
    </div>
  </div>
</div>
<footer class="text-center py-4 mt-5 text-muted">© <?=date('Y')?> MyBlog</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
