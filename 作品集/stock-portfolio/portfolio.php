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
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投資組合 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="portfolio.php">投資組合</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stocks.php">股票查詢</a>
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

        <!-- 投資組合總覽 -->
        <div class="row mb-4" id="portfolioSummary">
            <div class="col-md-3">
                <div class="card text-white bg-primary stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><i class="bi bi-wallet2"></i> 總投資金額</h6>
                                <h3 id="totalInvestment">$0</h3>
                            </div>
                            <div class="display-6 opacity-50">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><i class="bi bi-currency-exchange"></i> 總市值</h6>
                                <h3 id="totalMarketValue">$0</h3>
                            </div>
                            <div class="display-6 opacity-50">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white stats-card" id="profitLossCard">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><i class="bi bi-graph-up"></i> 總損益</h6>
                                <h3 id="totalProfitLoss">$0</h3>
                            </div>
                            <div class="display-6 opacity-50">
                                <i class="bi bi-trophy"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white stats-card" id="profitLossPercentCard">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><i class="bi bi-percent"></i> 報酬率</h6>
                                <h3 id="totalProfitLossPercent">0%</h3>
                            </div>
                            <div class="display-6 opacity-50">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 投資組合分配圖 -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart"></i> 投資組合分配</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="portfolioChart" width="400" height="300"></canvas>
                        <div id="portfolioChartEmpty" class="text-center py-4" style="display: none;">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">尚未建立投資組合</p>
                            <a href="stocks.php" class="btn btn-primary">開始投資</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 產業分配圖 -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-bar-chart"></i> 產業分配</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="industryChart" width="400" height="300"></canvas>
                        <div id="industryChartEmpty" class="text-center py-4" style="display: none;">
                            <i class="bi bi-building display-4 text-muted"></i>
                            <p class="text-muted mt-2">產業分配資料不足</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 持股明細 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-briefcase"></i> 持股明細</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary refresh-data">
                                <i class="bi bi-arrow-clockwise"></i> 刷新
                            </button>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
                                <i class="bi bi-plus-circle"></i> 新增持股
                            </button>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> 匯出
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item export-data" href="#" data-format="csv">CSV 格式</a></li>
                                    <li><a class="dropdown-item export-data" href="#" data-format="excel">Excel 格式</a></li>
                                    <li><a class="dropdown-item export-data" href="#" data-format="pdf">PDF 格式</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="portfolioTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>產業</th>
                                        <th>持股數</th>
                                        <th>平均成本</th>
                                        <th>現價</th>
                                        <th>投資金額</th>
                                        <th>市值</th>
                                        <th>損益</th>
                                        <th>報酬率</th>
                                        <th>權重</th>
                                        <th>今日損益</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 資料將透過 AJAX 載入 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 新增持股模態框 -->
    <div class="modal fade" id="addStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新增持股</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStockForm" class="ajax-form" action="api/portfolio.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="stockCodeInput" class="form-label">股票代碼 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stockCodeInput" name="stock_code" required>
                            <div class="form-text">請輸入完整的股票代碼，例如：2330</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantityInput" class="form-label">持股數量 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="avgPriceInput" class="form-label">平均成本 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="avgPriceInput" name="avg_price" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">投資金額</label>
                            <div id="investmentAmount" class="form-control-plaintext fw-bold text-primary">$0</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" form="addStockForm" class="btn btn-primary">確認新增</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 編輯持股模態框 -->
    <div class="modal fade" id="editStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">編輯持股</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStockForm" class="ajax-form" action="api/portfolio.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="editStockCode" name="stock_code">
                        
                        <div class="mb-3">
                            <label class="form-label">股票資訊</label>
                            <div id="editStockInfo" class="form-control-plaintext">
                                <!-- 股票資訊將在這裡顯示 -->
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">持股數量 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editQuantity" name="quantity" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editAvgPrice" class="form-label">平均成本 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editAvgPrice" name="avg_price" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">投資金額</label>
                            <div id="editInvestmentAmount" class="form-control-plaintext fw-bold text-primary">$0</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" form="editStockForm" class="btn btn-primary">確認更新</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 買賣股票模態框 -->
    <div class="modal fade" id="tradeStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">股票交易</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="tradeStockForm" class="ajax-form" action="api/portfolio.php" method="POST">
                        <input type="hidden" id="tradeStockCode" name="stock_code">
                        <input type="hidden" id="tradeAction" name="action">
                        
                        <div class="mb-3">
                            <label class="form-label">股票資訊</label>
                            <div id="tradeStockInfo" class="form-control-plaintext">
                                <!-- 股票資訊將在這裡顯示 -->
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">交易類型</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="trade_type" id="buyOption" value="buy" checked>
                                <label class="btn btn-outline-success" for="buyOption">
                                    <i class="bi bi-plus-circle"></i> 買進
                                </label>
                                
                                <input type="radio" class="btn-check" name="trade_type" id="sellOption" value="sell">
                                <label class="btn btn-outline-danger" for="sellOption">
                                    <i class="bi bi-dash-circle"></i> 賣出
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tradeQuantity" class="form-label">數量 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tradeQuantity" name="quantity" min="1" required>
                            <div id="maxQuantityInfo" class="form-text"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tradePrice" class="form-label">價格 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tradePrice" name="price" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">交易金額</label>
                            <div id="tradeAmount" class="form-control-plaintext fw-bold text-primary">$0</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" form="tradeStockForm" class="btn btn-primary" id="tradeSubmitBtn">確認交易</button>
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
        let portfolioChart = null;
        let industryChart = null;

        $(document).ready(function() {
            loadPortfolioData();
            setupPortfolioEvents();
            initializeDataTable();
        });

        function loadPortfolioData() {
            $.ajax({
                url: 'api/portfolio.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        updatePortfolioSummary(response.data.summary);
                        updatePortfolioTable(response.data.holdings);
                        updatePortfolioCharts(response.data.holdings);
                    } else {
                        showError('載入投資組合資料失敗');
                    }
                },
                error: function() {
                    showError('載入投資組合資料時發生錯誤');
                }
            });
        }

        function updatePortfolioSummary(summary) {
            $('#totalInvestment').text('$' + formatNumber(summary.totalInvestment));
            $('#totalMarketValue').text('$' + formatNumber(summary.totalMarketValue));
            $('#totalProfitLoss').text('$' + formatNumber(summary.totalProfitLoss));
            $('#totalProfitLossPercent').text(formatNumber(summary.totalProfitLossPercent) + '%');

            // 更新損益卡片顏色
            const profitLossCard = $('#profitLossCard');
            const profitLossPercentCard = $('#profitLossPercentCard');
            
            if (summary.totalProfitLoss >= 0) {
                profitLossCard.removeClass('bg-danger').addClass('bg-success');
                profitLossPercentCard.removeClass('bg-danger').addClass('bg-success');
            } else {
                profitLossCard.removeClass('bg-success').addClass('bg-danger');
                profitLossPercentCard.removeClass('bg-success').addClass('bg-danger');
            }
        }

        function updatePortfolioTable(holdings) {
            const tableBody = $('#portfolioTable tbody');
            tableBody.empty();

            if (holdings.length === 0) {
                $('#portfolioTable').hide();
                return;
            }

            holdings.forEach(holding => {
                const profitLoss = holding.marketValue - holding.investment;
                const profitLossPercent = holding.investment > 0 ? (profitLoss / holding.investment) * 100 : 0;
                const todayProfitLoss = holding.quantity * (holding.priceChange || 0);

                const row = `
                    <tr>
                        <td><span class="stock-code">${holding.stockCode}</span></td>
                        <td>${holding.stockName}</td>
                        <td><span class="badge bg-secondary">${holding.industry || 'N/A'}</span></td>
                        <td>${formatNumber(holding.quantity, 0)}</td>
                        <td>$${formatNumber(holding.avgPrice)}</td>
                        <td>$${formatNumber(holding.currentPrice)}</td>
                        <td>$${formatNumber(holding.investment)}</td>
                        <td>$${formatNumber(holding.marketValue)}</td>
                        <td class="${profitLoss >= 0 ? 'text-success' : 'text-danger'}">
                            ${profitLoss >= 0 ? '+' : ''}$${formatNumber(Math.abs(profitLoss))}
                        </td>
                        <td class="${profitLossPercent >= 0 ? 'text-success' : 'text-danger'}">
                            ${profitLossPercent >= 0 ? '+' : ''}${formatNumber(profitLossPercent)}%
                        </td>
                        <td>${formatNumber(holding.weight)}%</td>
                        <td class="${todayProfitLoss >= 0 ? 'text-success' : 'text-danger'}">
                            ${todayProfitLoss >= 0 ? '+' : ''}$${formatNumber(Math.abs(todayProfitLoss))}
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary edit-stock" data-stock-code="${holding.stockCode}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success trade-stock" data-stock-code="${holding.stockCode}" data-action="buy">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning trade-stock" data-stock-code="${holding.stockCode}" data-action="sell">
                                    <i class="bi bi-dash-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger remove-stock" data-stock-code="${holding.stockCode}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tableBody.append(row);
            });

            $('#portfolioTable').show();
        }

        function updatePortfolioCharts(holdings) {
            if (holdings.length === 0) {
                $('#portfolioChart').hide();
                $('#portfolioChartEmpty').show();
                $('#industryChart').hide();
                $('#industryChartEmpty').show();
                return;
            }

            // 更新投資組合分配圖
            updatePortfolioChart(holdings);
            
            // 更新產業分配圖
            updateIndustryChart(holdings);
        }

        function updatePortfolioChart(holdings) {
            const ctx = document.getElementById('portfolioChart').getContext('2d');
            
            if (portfolioChart) {
                portfolioChart.destroy();
            }

            const data = {
                labels: holdings.map(h => h.stockCode),
                datasets: [{
                    data: holdings.map(h => h.marketValue),
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                        '#ffecd2', '#fcb69f', '#a8edea', '#fed6e3'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            };

            portfolioChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    return data.labels.map((label, i) => ({
                                        text: `${label} (${formatNumber((data.datasets[0].data[i] / data.datasets[0].data.reduce((a, b) => a + b, 0)) * 100)}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].backgroundColor[i],
                                        index: i
                                    }));
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: $${formatNumber(context.parsed)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            $('#portfolioChart').show();
            $('#portfolioChartEmpty').hide();
        }

        function updateIndustryChart(holdings) {
            const ctx = document.getElementById('industryChart').getContext('2d');
            
            if (industryChart) {
                industryChart.destroy();
            }

            // 按產業分組
            const industryData = {};
            holdings.forEach(holding => {
                const industry = holding.industry || '其他';
                if (!industryData[industry]) {
                    industryData[industry] = 0;
                }
                industryData[industry] += holding.marketValue;
            });

            const data = {
                labels: Object.keys(industryData),
                datasets: [{
                    data: Object.values(industryData),
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            };

            industryChart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: $${formatNumber(context.parsed)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            $('#industryChart').show();
            $('#industryChartEmpty').hide();
        }

        function setupPortfolioEvents() {
            // 計算投資金額
            $('#quantityInput, #avgPriceInput').on('input', function() {
                const quantity = parseFloat($('#quantityInput').val()) || 0;
                const avgPrice = parseFloat($('#avgPriceInput').val()) || 0;
                const total = quantity * avgPrice;
                $('#investmentAmount').text('$' + formatNumber(total));
            });

            $('#editQuantity, #editAvgPrice').on('input', function() {
                const quantity = parseFloat($('#editQuantity').val()) || 0;
                const avgPrice = parseFloat($('#editAvgPrice').val()) || 0;
                const total = quantity * avgPrice;
                $('#editInvestmentAmount').text('$' + formatNumber(total));
            });

            $('#tradeQuantity, #tradePrice').on('input', function() {
                const quantity = parseFloat($('#tradeQuantity').val()) || 0;
                const price = parseFloat($('#tradePrice').val()) || 0;
                const total = quantity * price;
                $('#tradeAmount').text('$' + formatNumber(total));
            });

            // 交易類型切換
            $('input[name="trade_type"]').on('change', function() {
                const action = $(this).val();
                $('#tradeAction').val(action);
                
                if (action === 'buy') {
                    $('#tradeSubmitBtn').removeClass('btn-danger').addClass('btn-success').html('<i class="bi bi-plus-circle"></i> 確認買進');
                } else {
                    $('#tradeSubmitBtn').removeClass('btn-success').addClass('btn-danger').html('<i class="bi bi-dash-circle"></i> 確認賣出');
                }
            });
        }

        function initializeDataTable() {
            $('#portfolioTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/zh-HANT.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[7, 'desc']], // 按市值排序
                columnDefs: [
                    { targets: [12], orderable: false } // 操作列不可排序
                ]
            });
        }

        // 編輯持股
        $(document).on('click', '.edit-stock', function() {
            const stockCode = $(this).data('stock-code');
            // 載入現有資料並顯示編輯模態框
            // 實作略...
            $('#editStockModal').modal('show');
        });

        // 交易股票
        $(document).on('click', '.trade-stock', function() {
            const stockCode = $(this).data('stock-code');
            const action = $(this).data('action');
            
            $('#tradeStockCode').val(stockCode);
            $('#tradeAction').val(action);
            
            if (action === 'buy') {
                $('#buyOption').prop('checked', true);
                $('#tradeSubmitBtn').removeClass('btn-danger').addClass('btn-success').html('<i class="bi bi-plus-circle"></i> 確認買進');
            } else {
                $('#sellOption').prop('checked', true);
                $('#tradeSubmitBtn').removeClass('btn-success').addClass('btn-danger').html('<i class="bi bi-dash-circle"></i> 確認賣出');
            }
            
            $('#tradeStockModal').modal('show');
        });

        // 移除持股
        $(document).on('click', '.remove-stock', function() {
            const stockCode = $(this).data('stock-code');
            
            if (confirm('確定要移除這檔股票嗎？此操作無法復原。')) {
                $.ajax({
                    url: 'api/portfolio.php',
                    method: 'DELETE',
                    data: { stock_code: stockCode },
                    success: function(response) {
                        if (response.success) {
                            showSuccess(response.message);
                            loadPortfolioData();
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function() {
                        showError('移除持股時發生錯誤');
                    }
                });
            }
        });

        // 刷新資料
        $('.refresh-data').on('click', function() {
            loadPortfolioData();
            showSuccess('資料已刷新');
        });

        // 匯出資料
        $('.export-data').on('click', function(e) {
            e.preventDefault();
            const format = $(this).data('format');
            window.open(`api/export.php?type=portfolio&format=${format}`, '_blank');
        });
    </script>
</body>
</html>
