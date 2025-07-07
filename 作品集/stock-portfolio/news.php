<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 分頁設定
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 獲取新聞總數
$total_result = $db->fetchOne('SELECT COUNT(*) as count FROM news WHERE status = ?', ['active']);
$total_news = $total_result['count'];
$total_pages = ceil($total_news / $per_page);

// 獲取新聞列表
$news = $db->fetchAll('
    SELECT * FROM news 
    WHERE status = ? 
    ORDER BY published_at DESC 
    LIMIT ? OFFSET ?
', ['active', $per_page, $offset]);

$page_title = '新聞中心';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">新聞中心</h1>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">最新財經新聞</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($news)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <p class="text-muted">暫無新聞</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($news as $item): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="news_detail.php?id=<?php echo $item['id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </a>
                                        </h5>
                                        <?php if ($item['summary']): ?>
                                            <p class="card-text text-muted">
                                                <?php echo htmlspecialchars(mb_substr($item['summary'], 0, 100)); ?>...
                                            </p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?php if ($item['source']): ?>
                                                    <i class="fas fa-newspaper"></i> 
                                                    <?php echo htmlspecialchars($item['source']); ?>
                                                <?php endif; ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                <?php echo date('Y-m-d H:i', strtotime($item['published_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="news_detail.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">閱讀更多</a>
                                        <?php if ($item['url']): ?>
                                            <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-secondary">原文連結</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 分頁導航 -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="新聞分頁">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">上一頁</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">下一頁</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
