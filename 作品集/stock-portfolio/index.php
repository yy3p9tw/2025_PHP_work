<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

$db = new Database();

// 取得市場指數
$indices = $db->fetchAll("SELECT * FROM market_indices WHERE status = 'active' ORDER BY display_order");

// 取得熱門股票
$hotStocks = $db->fetchAll("SELECT * FROM stocks WHERE status = 'active' ORDER BY volume DESC LIMIT 6");

// 取得最新新聞
$latestNews = $db->fetchAll("SELECT * FROM news WHERE status = 'active' ORDER BY published_at DESC LIMIT 5");
?>

<?php include 'includes/header.php'; ?>

<!-- 主要內容 -->
<section class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">智慧投資，輕鬆管理</h1>
                    <p class="lead mb-4">專業的股票投資組合管理系統，幫助您追蹤投資、分析績效、掌握市場動態。</p>
                    <?php if (!isLoggedIn()): ?>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">立即註冊</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">會員登入</a>
                    </div>
                    <?php else: ?>
                    <div class="d-flex gap-3">
                        <a href="portfolio.php" class="btn btn-light btn-lg">我的投資組合</a>
                        <a href="watchlist.php" class="btn btn-outline-light btn-lg">關注清單</a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-chart-line" style="font-size: 12rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 市場指數 -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">市場概況</h2>
            <div class="row market-indices-container">
                <?php foreach ($indices as $index): ?>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="card-title text-muted"><?php echo htmlspecialchars($index['name']); ?></h6>
                            <h5 class="mb-1"><?php echo number_format($index['current_value'], 2); ?></h5>
                            <small class="<?php echo $index['change_value'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $index['change_value'] >= 0 ? '+' : ''; ?><?php echo number_format($index['change_value'], 2); ?>
                                (<?php echo $index['change_percent'] >= 0 ? '+' : ''; ?><?php echo number_format($index['change_percent'], 2); ?>%)
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 熱門股票 -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">熱門股票</h2>
            <div class="row hot-stocks">
                <?php foreach ($hotStocks as $stock): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($stock['code']); ?></h6>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($stock['name']); ?></p>
                                </div>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($stock['industry']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <h5 class="mb-0">$<?php echo number_format($stock['current_price'], 2); ?></h5>
                                    <small class="<?php echo $stock['price_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $stock['price_change'] >= 0 ? '+' : ''; ?><?php echo number_format($stock['price_change'], 2); ?>
                                        (<?php echo $stock['change_percent'] >= 0 ? '+' : ''; ?><?php echo number_format($stock['change_percent'], 2); ?>%)
                                    </small>
                                </div>
                                <small class="text-muted">成交量: <?php echo number_format($stock['volume']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 最新新聞 -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">財經新聞</h2>
                    <div class="news-list">
                    <?php foreach ($latestNews as $news): ?>
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="#" class="text-decoration-none text-dark"><?php echo htmlspecialchars($news['title']); ?></a>
                            </h5>
                            <?php if ($news['summary']): ?>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($news['summary']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo $news['source'] ? htmlspecialchars($news['source']) : '本站'; ?> • 
                                    <?php echo date('Y-m-d H:i', strtotime($news['published_at'])); ?>
                                </small>
                                <?php if ($news['url']): ?>
                                <a href="<?php echo htmlspecialchars($news['url']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    閱讀更多
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">系統功能</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-chart-pie text-primary me-2"></i>
                                    <strong>投資組合管理</strong><br>
                                    <small class="text-muted">追蹤持股、計算績效</small>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    <strong>關注清單</strong><br>
                                    <small class="text-muted">收藏感興趣的股票</small>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-newspaper text-info me-2"></i>
                                    <strong>財經新聞</strong><br>
                                    <small class="text-muted">掌握最新市場動態</small>
                                </li>
                                <li>
                                    <i class="fas fa-mobile-alt text-success me-2"></i>
                                    <strong>響應式設計</strong><br>
                                    <small class="text-muted">手機、平板、電腦都能使用</small>
                                </li>
                            </ul>
                            <?php if (!isLoggedIn()): ?>
                            <hr>
                            <div class="text-center">
                                <a href="register.php" class="btn btn-primary w-100">立即開始使用</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
