<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>股票查詢 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-graph-up"></i> 股票投資組合
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
                        <a class="nav-link active" href="stocks.php">股票查詢</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">財經新聞</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">交易記錄</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">個人資料</a></li>
                            <li><a class="dropdown-item" href="settings.php">設定</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php">管理後台</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- 警告訊息容器 -->
        <div id="alertContainer"></div>

        <!-- 搜尋區域 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-search"></i> 股票搜尋</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="search-box">
                                    <input type="text" id="stockSearch" class="form-control form-control-lg" 
                                           placeholder="輸入股票代碼或公司名稱...">
                                    <i class="bi bi-search search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="industryFilter" class="form-select form-select-lg">
                                    <option value="">所有產業</option>
                                    <option value="半導體">半導體</option>
                                    <option value="電子">電子</option>
                                    <option value="金融">金融</option>
                                    <option value="傳產">傳產</option>
                                    <option value="生技">生技</option>
                                    <option value="航運">航運</option>
                                </select>
                            </div>
                        </div>
                        <div id="searchResults" class="mt-3" style="display: none;">
                            <!-- 搜尋結果將在這裡顯示 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 快速導覽 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-lightning"></i> 快速導覽</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-outline-primary w-100 mb-2" id="showPopularStocks">
                                    <i class="bi bi-fire"></i> 熱門股票
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-success w-100 mb-2" id="showTopGainers">
                                    <i class="bi bi-arrow-up-circle"></i> 今日漲幅榜
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-danger w-100 mb-2" id="showTopLosers">
                                    <i class="bi bi-arrow-down-circle"></i> 今日跌幅榜
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-info w-100 mb-2" id="showMyWatchlist">
                                    <i class="bi bi-star"></i> 我的關注
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 股票列表 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-list-ul"></i> 股票列表</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary refresh-data">
                                <i class="bi bi-arrow-clockwise"></i> 刷新
                            </button>
                            <button class="btn btn-sm btn-outline-success" id="updatePrices">
                                <i class="bi bi-cloud-download"></i> 更新股價
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="stocksTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>產業</th>
                                        <th>現價</th>
                                        <th>漲跌</th>
                                        <th>漲跌幅</th>
                                        <th>成交量</th>
                                        <th>市值</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 股票資料將透過 AJAX 載入 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 股票詳細資訊模態框 -->
    <div class="modal fade" id="stockDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">股票詳細資訊</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="stockDetailContent">
                        <!-- 股票詳細資訊將在這裡顯示 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    <button type="button" class="btn btn-primary" id="addToPortfolioBtn">加入投資組合</button>
                    <button type="button" class="btn btn-outline-warning" id="addToWatchlistBtn">加入關注</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 加入投資組合模態框 -->
    <div class="modal fade" id="addToPortfolioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">加入投資組合</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addToPortfolioForm" class="ajax-form" action="api/portfolio.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="stock_code" id="portfolioStockCode">
                        
                        <div class="mb-3">
                            <label class="form-label">股票資訊</label>
                            <div id="portfolioStockInfo" class="form-control-plaintext">
                                <!-- 股票資訊將在這裡顯示 -->
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">持股數量</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="avgPrice" class="form-label">平均成本</label>
                            <input type="number" class="form-control" id="avgPrice" name="avg_price" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">總投資金額</label>
                            <div id="totalInvestment" class="form-control-plaintext fw-bold">$0</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" form="addToPortfolioForm" class="btn btn-primary">確認加入</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            loadStocksList();
            setupStockSearchEvents();
            setupQuickNavigation();
            setupPortfolioCalculation();
        });

        function loadStocksList() {
            $('#stocksTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'api/stocks.php',
                    type: 'GET'
                },
                columns: [
                    { data: 'code', render: function(data) { return '<span class="stock-code">' + data + '</span>'; } },
                    { data: 'name' },
                    { data: 'industry' },
                    { data: 'current_price', render: function(data) { return '$' + parseFloat(data).toFixed(2); } },
                    { 
                        data: 'price_change',
                        render: function(data, type, row) {
                            const change = parseFloat(data);
                            const colorClass = change >= 0 ? 'text-success' : 'text-danger';
                            return '<span class="' + colorClass + '">' + (change >= 0 ? '+' : '') + change.toFixed(2) + '</span>';
                        }
                    },
                    { 
                        data: 'change_percent',
                        render: function(data, type, row) {
                            const percent = parseFloat(data);
                            const colorClass = percent >= 0 ? 'text-success' : 'text-danger';
                            return '<span class="' + colorClass + '">' + (percent >= 0 ? '+' : '') + percent.toFixed(2) + '%</span>';
                        }
                    },
                    { data: 'volume', render: function(data) { return parseInt(data).toLocaleString(); } },
                    { data: 'market_cap', render: function(data) { return '$' + (parseFloat(data) / 1000000).toFixed(0) + 'M'; } },
                    { 
                        data: 'code',
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-outline-primary view-stock" data-code="${data}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success add-to-portfolio" data-code="${data}">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning add-to-watchlist" data-code="${data}">
                                    <i class="bi bi-star"></i>
                                </button>
                            `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/zh-HANT.json'
                },
                responsive: true,
                pageLength: 25
            });
        }

        function setupStockSearchEvents() {
            $('#stockSearch').on('input', debounce(function() {
                const query = $(this).val();
                if (query.length >= 2) {
                    searchStocks(query);
                } else {
                    $('#searchResults').hide();
                }
            }, 300));

            $('#industryFilter').on('change', function() {
                const industry = $(this).val();
                const table = $('#stocksTable').DataTable();
                table.column(2).search(industry).draw();
            });
        }

        function searchStocks(query) {
            $.ajax({
                url: 'api/stocks.php',
                method: 'GET',
                data: { action: 'search', query: query },
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    }
                }
            });
        }

        function displaySearchResults(stocks) {
            const resultsHtml = stocks.map(stock => `
                <div class="search-result-item p-3 mb-2 border rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <span class="stock-code">${stock.code}</span> - ${stock.name}
                            </h6>
                            <small class="text-muted">${stock.industry}</small>
                        </div>
                        <div class="text-end">
                            <span class="price ${stock.change >= 0 ? 'text-success' : 'text-danger'} fw-bold">
                                $${stock.price.toFixed(2)}
                            </span>
                            <small class="d-block ${stock.change >= 0 ? 'text-success' : 'text-danger'}">
                                ${stock.change >= 0 ? '+' : ''}${stock.change.toFixed(2)} (${stock.changePercent.toFixed(2)}%)
                            </small>
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-sm btn-outline-primary view-stock" data-code="${stock.code}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success add-to-portfolio" data-code="${stock.code}">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            
            $('#searchResults').html(resultsHtml).show();
        }

        function setupQuickNavigation() {
            $('#showPopularStocks').on('click', function() {
                loadStocksByCategory('popular');
            });

            $('#showTopGainers').on('click', function() {
                loadStocksByCategory('gainers');
            });

            $('#showTopLosers').on('click', function() {
                loadStocksByCategory('losers');
            });

            $('#showMyWatchlist').on('click', function() {
                loadWatchlist();
            });
        }

        function loadStocksByCategory(category) {
            $.ajax({
                url: 'api/stocks.php',
                method: 'GET',
                data: { action: category },
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    }
                }
            });
        }

        function loadWatchlist() {
            $.ajax({
                url: 'api/watchlist.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    }
                }
            });
        }

        function setupPortfolioCalculation() {
            $('#quantity, #avgPrice').on('input', function() {
                const quantity = parseFloat($('#quantity').val()) || 0;
                const avgPrice = parseFloat($('#avgPrice').val()) || 0;
                const total = quantity * avgPrice;
                $('#totalInvestment').text('$' + total.toFixed(2));
            });
        }

        // 查看股票詳細資訊
        $(document).on('click', '.view-stock', function() {
            const stockCode = $(this).data('code');
            loadStockDetail(stockCode);
        });

        function loadStockDetail(stockCode) {
            $.ajax({
                url: 'api/stocks.php',
                method: 'GET',
                data: { action: 'quote', code: stockCode },
                success: function(response) {
                    if (response.success) {
                        displayStockDetail(response.data);
                        $('#stockDetailModal').modal('show');
                    }
                }
            });
        }

        function displayStockDetail(data) {
            const stock = data.stock;
            const history = data.history;
            
            const detailHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <h4>${stock.code} - ${stock.name}</h4>
                        <p class="text-muted">${stock.industry}</p>
                        <div class="d-flex align-items-center mb-3">
                            <span class="display-6 me-3">$${parseFloat(stock.current_price).toFixed(2)}</span>
                            <span class="${parseFloat(stock.price_change) >= 0 ? 'text-success' : 'text-danger'} fs-5">
                                ${parseFloat(stock.price_change) >= 0 ? '+' : ''}${parseFloat(stock.price_change).toFixed(2)}
                                (${parseFloat(stock.change_percent).toFixed(2)}%)
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><td>成交量</td><td>${parseInt(stock.volume).toLocaleString()}</td></tr>
                            <tr><td>市值</td><td>$${(parseFloat(stock.market_cap) / 1000000).toFixed(0)}M</td></tr>
                            <tr><td>更新時間</td><td>${stock.updated_at}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            $('#stockDetailContent').html(detailHtml);
            $('#addToPortfolioBtn').data('code', stock.code);
            $('#addToWatchlistBtn').data('code', stock.code);
        }

        // 加入投資組合
        $(document).on('click', '.add-to-portfolio', function() {
            const stockCode = $(this).data('code');
            showAddToPortfolioModal(stockCode);
        });

        $(document).on('click', '#addToPortfolioBtn', function() {
            const stockCode = $(this).data('code');
            showAddToPortfolioModal(stockCode);
        });

        function showAddToPortfolioModal(stockCode) {
            $('#portfolioStockCode').val(stockCode);
            $('#portfolioStockInfo').text(`${stockCode} - 股票資訊載入中...`);
            $('#addToPortfolioModal').modal('show');
            
            // 載入股票資訊
            $.ajax({
                url: 'api/stocks.php',
                method: 'GET',
                data: { action: 'quote', code: stockCode },
                success: function(response) {
                    if (response.success) {
                        const stock = response.data.stock;
                        $('#portfolioStockInfo').text(`${stock.code} - ${stock.name} (現價: $${parseFloat(stock.current_price).toFixed(2)})`);
                        $('#avgPrice').val(parseFloat(stock.current_price).toFixed(2));
                    }
                }
            });
        }

        // 加入關注清單
        $(document).on('click', '.add-to-watchlist', function() {
            const stockCode = $(this).data('code');
            addToWatchlist(stockCode);
        });

        $(document).on('click', '#addToWatchlistBtn', function() {
            const stockCode = $(this).data('code');
            addToWatchlist(stockCode);
        });

        function addToWatchlist(stockCode) {
            $.ajax({
                url: 'api/stocks.php',
                method: 'POST',
                data: { action: 'add_to_watchlist', stock_code: stockCode },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                    } else {
                        showError(response.message);
                    }
                }
            });
        }

        // 更新股價
        $('#updatePrices').on('click', function() {
            const button = $(this);
            const originalText = button.html();
            
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> 更新中...');
            
            $.ajax({
                url: 'api/stocks.php',
                method: 'POST',
                data: { action: 'update_prices' },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        $('#stocksTable').DataTable().ajax.reload();
                    } else {
                        showError(response.message);
                    }
                },
                complete: function() {
                    button.prop('disabled', false).html(originalText);
                }
            });
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>
