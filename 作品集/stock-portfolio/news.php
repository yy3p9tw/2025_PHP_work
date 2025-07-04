<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// 獲取新聞列表
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$stmt = $conn->prepare("SELECT COUNT(*) FROM news WHERE status = 'active'");
$stmt->execute();
$total_news = $stmt->fetchColumn();
$total_pages = ceil($total_news / $per_page);

$stmt = $conn->prepare("
    SELECT * FROM news 
    WHERE status = 'active' 
    ORDER BY published_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>財經新聞 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line"></i> 股票投資組合
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">首頁</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="portfolio.php">投資組合</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stocks.php">股票查詢</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="news.php">財經新聞</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">交易記錄</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-newspaper"></i> 財經新聞</h1>
                    <button class="btn btn-primary" onclick="refreshNews()">
                        <i class="fas fa-sync-alt"></i> 重新整理
                    </button>
                </div>

                <!-- 新聞列表 -->
                <div class="row">
                    <?php foreach ($news_list as $news): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                                <p class="card-text">
                                    <?php 
                                    $summary = $news['summary'] ?? '';
                                    if (empty($summary) && !empty($news['content'])) {
                                        $summary = mb_substr(strip_tags($news['content']), 0, 200) . '...';
                                    }
                                    echo htmlspecialchars($summary);
                                    ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($news['published_at'])); ?>
                                        <?php if ($news['source']): ?>
                                        | <i class="fas fa-newspaper"></i> <?php echo htmlspecialchars($news['source']); ?>
                                        <?php endif; ?>
                                    </small>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="showNewsDetail(<?php echo $news['id']; ?>)">
                                            <i class="fas fa-eye"></i> 查看詳情
                                        </button>
                                        <?php if ($news['url']): ?>
                                        <a href="<?php echo htmlspecialchars($news['url']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-external-link-alt"></i> 原文連結
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 分頁 -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="新聞分頁">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 新聞詳情 Modal -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新聞詳情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="newsDetailContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">載入中...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showNewsDetail(newsId) {
            $('#newsDetailModal').modal('show');
            
            $.ajax({
                url: 'api/news.php',
                method: 'GET',
                data: { id: newsId },
                success: function(response) {
                    if (response.success) {
                        const news = response.data;
                        const content = `
                            <h5>${news.title}</h5>
                            <p class="text-muted">
                                <i class="fas fa-clock"></i> ${news.published_at}
                                ${news.source ? `| <i class="fas fa-newspaper"></i> ${news.source}` : ''}
                            </p>
                            <div class="mt-3">
                                ${news.content || news.summary || '暫無詳細內容'}
                            </div>
                            ${news.url ? `<div class="mt-3"><a href="${news.url}" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> 閱讀原文</a></div>` : ''}
                        `;
                        $('#newsDetailContent').html(content);
                    } else {
                        $('#newsDetailContent').html('<p class="text-danger">載入新聞內容時發生錯誤</p>');
                    }
                },
                error: function() {
                    $('#newsDetailContent').html('<p class="text-danger">載入新聞內容時發生錯誤</p>');
                }
            });
        }

        function refreshNews() {
            window.location.reload();
        }
    </script>
</body>
</html>
