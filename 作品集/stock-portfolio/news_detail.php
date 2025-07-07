<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 獲取新聞ID
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header('Location: news.php');
    exit();
}

// 獲取新聞詳情
$news = $db->fetchOne('SELECT * FROM news WHERE id = ? AND status = ?', [$news_id, 'active']);

if (empty($news)) {
    header('Location: news.php');
    exit();
}

// 獲取相關新聞
$related_news = $db->fetchAll('
    SELECT * FROM news 
    WHERE id != ? AND status = ? 
    ORDER BY published_at DESC 
    LIMIT 5
', [$news_id, 'active']);

$page_title = $news['title'];
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- 新聞內容 -->
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <?php if ($news['source']): ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-newspaper"></i> 
                                    <?php echo htmlspecialchars($news['source']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            <?php echo date('Y-m-d H:i', strtotime($news['published_at'])); ?>
                        </small>
                    </div>
                    
                    <?php if ($news['summary']): ?>
                        <div class="alert alert-info">
                            <strong>摘要：</strong><?php echo htmlspecialchars($news['summary']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                    </div>
                    
                    <?php if ($news['url']): ?>
                        <div class="mt-4">
                            <a href="<?php echo htmlspecialchars($news['url']); ?>" 
                               target="_blank" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> 查看原文
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 導航按鈕 -->
            <div class="mt-4">
                <a href="news.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> 返回新聞列表
                </a>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- 相關新聞 -->
            <?php if (!empty($related_news)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">相關新聞</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($related_news as $key => $item): ?>
                    <div class="mb-3">
                        <h6>
                            <a href="news_detail.php?id=<?php echo $item['id']; ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars(mb_substr($item['title'], 0, 50)); ?>...
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            <?php echo date('Y-m-d', strtotime($item['published_at'])); ?>
                        </small>
                    </div>
                    <?php if ($key !== array_key_last($related_news)): ?>
                        <hr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 市場摘要 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">市場摘要</h5>
                </div>
                <div class="card-body">
                    <?php
                    // 獲取市場指數
                    $indices = $db->fetchAll('
                        SELECT * FROM market_indices 
                        WHERE status = ? 
                        ORDER BY display_order 
                        LIMIT 5
                    ', ['active']);
                    ?>
                    
                    <?php if (!empty($indices)): ?>
                        <?php foreach ($indices as $key => $index): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($index['name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($index['code']); ?></small>
                            </div>
                            <div class="text-end">
                                <div><?php echo number_format($index['current_value'], 2); ?></div>
                                <small class="<?php echo $index['change_percent'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($index['change_percent'] >= 0 ? '+' : '') . number_format($index['change_percent'], 2); ?>%
                                </small>
                            </div>
                        </div>
                        <?php if ($key !== array_key_last($indices)): ?>
                            <hr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">暫無市場數據</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
