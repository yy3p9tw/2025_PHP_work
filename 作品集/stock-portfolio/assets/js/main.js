// 股票投資組合系統主要 JavaScript 檔案

// 全域變數
let stockDataCache = {};
let updateInterval = null;
let chartInstances = {};

// 初始化
$(document).ready(function() {
    initializeApp();
    setupEventListeners();
    startAutoUpdate();
});

// 初始化應用程式
function initializeApp() {
    // 初始化工具提示
    initTooltips();
    
    // 初始化彈出視窗
    initPopovers();
    
    // 初始化資料表格
    initDataTables();
    
    // 載入初始資料
    loadInitialData();
    
    // 設定 CSRF Token
    setupCSRFToken();
}

// 初始化工具提示
function initTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip();
}

// 初始化彈出視窗
function initPopovers() {
    $('[data-bs-toggle="popover"]').popover();
}

// 初始化資料表格
function initDataTables() {
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "表格中沒有資料",
                "info": "顯示 _START_ 至 _END_ 項結果，共 _TOTAL_ 項",
                "infoEmpty": "顯示第 0 至 0 項結果，共 0 項",
                "infoFiltered": "(從 _MAX_ 項結果中過濾)",
                "lengthMenu": "顯示 _MENU_ 項結果",
                "loadingRecords": "載入中...",
                "processing": "處理中...",
                "search": "搜尋：",
                "zeroRecords": "沒有找到相符的結果",
                "paginate": {
                    "first": "第一頁",
                    "last": "最後一頁",
                    "next": "下一頁",
                    "previous": "上一頁"
                }
            },
            responsive: true,
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }
}

// 載入初始資料
function loadInitialData() {
    loadMarketIndices();
    loadStockData();
    loadNews();
}

// 設定 CSRF Token
function setupCSRFToken() {
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                xhr.setRequestHeader("X-CSRFToken", getCSRFToken());
            }
        }
    });
}

// 獲取 CSRF Token
function getCSRFToken() {
    return $('meta[name=csrf-token]').attr('content');
}

// 設定事件監聽器
function setupEventListeners() {
    // 股票搜尋
    $('#stockSearch').on('input', debounce(searchStocks, 300));
    
    // 投資組合管理
    $(document).on('click', '.add-to-portfolio', handleAddToPortfolio);
    $(document).on('click', '.remove-from-portfolio', handleRemoveFromPortfolio);
    $(document).on('click', '.update-portfolio', handleUpdatePortfolio);
    
    // 交易記錄
    $(document).on('click', '.record-transaction', handleRecordTransaction);
    
    // 模態框事件
    $(document).on('show.bs.modal', '.modal', handleModalShow);
    $(document).on('hidden.bs.modal', '.modal', handleModalHidden);
    
    // 表單提交
    $(document).on('submit', '.ajax-form', handleAjaxFormSubmit);
    
    // 刷新按鈕
    $(document).on('click', '.refresh-data', handleRefreshData);
    
    // 導出功能
    $(document).on('click', '.export-data', handleExportData);
}

// 防抖函數
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

// 股票搜尋功能
function searchStocks(event) {
    const query = event.target.value.trim();
    
    if (query.length < 2) {
        $('#searchResults').hide();
        return;
    }
    
    showLoading('#searchResults');
    
    $.ajax({
        url: 'api/stocks.php',
        method: 'GET',
        data: { action: 'search', query: query },
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.data);
            } else {
                showError('搜尋失敗：' + response.message);
            }
        },
        error: function() {
            showError('搜尋時發生錯誤');
        },
        complete: function() {
            hideLoading('#searchResults');
        }
    });
}

// 顯示搜尋結果
function displaySearchResults(stocks) {
    const resultsHtml = stocks.map(stock => `
        <div class="search-result-item" data-stock-code="${stock.code}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${stock.code}</strong> - ${stock.name}
                    <small class="text-muted d-block">${stock.industry}</small>
                </div>
                <div class="text-end">
                    <span class="price ${stock.change >= 0 ? 'text-success' : 'text-danger'}">
                        $${stock.price}
                    </span>
                    <small class="d-block ${stock.change >= 0 ? 'text-success' : 'text-danger'}">
                        ${stock.change >= 0 ? '+' : ''}${stock.change} (${stock.changePercent}%)
                    </small>
                </div>
            </div>
        </div>
    `).join('');
    
    $('#searchResults').html(resultsHtml).show();
}

// 載入市場指數
function loadMarketIndices() {
    $.ajax({
        url: 'api/market-indices.php',
        method: 'GET',
        success: function(response) {
            console.log('市場指數 API 響應:', response);
            if (response.success) {
                updateMarketIndices(response.indices);
            } else {
                console.error('市場指數 API 錯誤:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('載入市場指數失敗:', xhr.status, error);
            console.error('響應內容:', xhr.responseText);
        }
    });
}

// 更新市場指數
function updateMarketIndices(indices) {
    // 檢查 indices 是否為陣列
    if (!Array.isArray(indices)) {
        console.error('市場指數數據不是陣列:', indices);
        return;
    }
    
    indices.forEach(index => {
        const element = $(`#index-${index.code}`);
        if (element.length) {
            element.find('.value').text(formatNumber(index.value || index.current_value));
            element.find('.change').text(
                (index.change >= 0 ? '+' : '') + formatNumber(index.change || index.change_value)
            );
            element.find('.change-percent').text(
                (index.change_percent >= 0 ? '+' : '') + formatNumber(index.change_percent || index.changePercent) + '%'
            );
            
            // 更新顏色
            const change = index.change || index.change_value || 0;
            const colorClass = change >= 0 ? 'text-success' : 'text-danger';
            element.find('.change, .change-percent').attr('class', colorClass);
        }
    });
}

// 載入股票資料
function loadStockData() {
    $.ajax({
        url: 'api/stocks.php',
        method: 'GET',
        data: { action: 'portfolio' },
        success: function(response) {
            if (response.success) {
                updatePortfolioData(response.data);
            }
        },
        error: function() {
            console.error('載入股票資料失敗');
        }
    });
}

// 更新投資組合資料
function updatePortfolioData(portfolioData) {
    // 更新投資組合總覽
    $('#totalInvestment').text('$' + formatNumber(portfolioData.totalInvestment));
    $('#totalMarketValue').text('$' + formatNumber(portfolioData.totalMarketValue));
    $('#totalProfitLoss').text('$' + formatNumber(portfolioData.totalProfitLoss));
    $('#totalProfitLossPercent').text(formatNumber(portfolioData.totalProfitLossPercent) + '%');
    
    // 更新投資組合表格
    updatePortfolioTable(portfolioData.holdings);
    
    // 更新圖表
    updatePortfolioChart(portfolioData.holdings);
}

// 更新投資組合表格
function updatePortfolioTable(holdings) {
    const tableBody = $('#portfolioTable tbody');
    tableBody.empty();
    
    holdings.forEach(holding => {
        const profitLoss = holding.marketValue - holding.investment;
        const profitLossPercent = (profitLoss / holding.investment) * 100;
        
        const row = `
            <tr>
                <td><span class="stock-code">${holding.stockCode}</span></td>
                <td>${holding.stockName}</td>
                <td>${formatNumber(holding.quantity)}</td>
                <td>$${formatNumber(holding.avgPrice)}</td>
                <td>$${formatNumber(holding.currentPrice)}</td>
                <td class="${profitLoss >= 0 ? 'text-success' : 'text-danger'}">
                    ${profitLoss >= 0 ? '+' : ''}$${formatNumber(profitLoss)}
                    <small class="d-block">
                        ${profitLoss >= 0 ? '+' : ''}${formatNumber(profitLossPercent)}%
                    </small>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary update-portfolio" 
                            data-stock-code="${holding.stockCode}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger remove-from-portfolio" 
                            data-stock-code="${holding.stockCode}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tableBody.append(row);
    });
}

// 載入新聞
function loadNews() {
    $.ajax({
        url: 'api/news.php',
        method: 'GET',
        success: function(response) {
            console.log('新聞 API 響應:', response);
            if (response.success) {
                updateNewsSection(response.news);
            } else {
                console.error('新聞 API 錯誤:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('載入新聞失敗:', xhr.status, error);
            console.error('響應內容:', xhr.responseText);
        }
    });
}

// 更新新聞區塊
function updateNewsSection(news) {
    const newsContainer = $('#newsContainer');
    newsContainer.empty();
    
    // 檢查 news 是否為陣列
    if (!Array.isArray(news)) {
        console.error('新聞數據不是陣列:', news);
        newsContainer.html('<p class="text-muted">無法載入新聞</p>');
        return;
    }
    
    if (news.length === 0) {
        newsContainer.html('<p class="text-muted">暫無新聞</p>');
        return;
    }
    
    news.forEach(article => {
        const newsItem = `
            <div class="news-item">
                <h6><a href="${article.url || '#'}" target="_blank">${article.title || '未標題'}</a></h6>
                <small class="text-muted">${formatDate(article.published_at || article.publishedAt)}</small>
                <p class="mt-2">${article.summary || ''}</p>
            </div>
        `;
        newsContainer.append(newsItem);
    });
}

// 處理加入投資組合
function handleAddToPortfolio(event) {
    const stockCode = $(event.target).data('stock-code');
    showAddToPortfolioModal(stockCode);
}

// 顯示加入投資組合模態框
function showAddToPortfolioModal(stockCode) {
    $('#addToPortfolioModal').modal('show');
    $('#addToPortfolioForm input[name="stock_code"]').val(stockCode);
}

// 處理從投資組合移除
function handleRemoveFromPortfolio(event) {
    const stockCode = $(event.target).data('stock-code');
    
    if (confirm('確定要從投資組合中移除這檔股票嗎？')) {
        $.ajax({
            url: 'api/portfolio.php',
            method: 'DELETE',
            data: { stock_code: stockCode },
            success: function(response) {
                if (response.success) {
                    showSuccess('已從投資組合中移除');
                    loadStockData();
                } else {
                    showError('移除失敗：' + response.message);
                }
            },
            error: function() {
                showError('移除時發生錯誤');
            }
        });
    }
}

// 處理更新投資組合
function handleUpdatePortfolio(event) {
    const stockCode = $(event.target).data('stock-code');
    showUpdatePortfolioModal(stockCode);
}

// 顯示更新投資組合模態框
function showUpdatePortfolioModal(stockCode) {
    $('#updatePortfolioModal').modal('show');
    $('#updatePortfolioForm input[name="stock_code"]').val(stockCode);
    
    // 載入現有資料
    loadPortfolioData(stockCode);
}

// 載入投資組合資料
function loadPortfolioData(stockCode) {
    $.ajax({
        url: 'api/portfolio.php',
        method: 'GET',
        data: { stock_code: stockCode },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#updatePortfolioForm input[name="quantity"]').val(data.quantity);
                $('#updatePortfolioForm input[name="avg_price"]').val(data.avgPrice);
            }
        }
    });
}

// 處理記錄交易
function handleRecordTransaction(event) {
    const stockCode = $(event.target).data('stock-code');
    showRecordTransactionModal(stockCode);
}

// 顯示記錄交易模態框
function showRecordTransactionModal(stockCode) {
    $('#recordTransactionModal').modal('show');
    $('#recordTransactionForm input[name="stock_code"]').val(stockCode);
}

// 處理模態框顯示
function handleModalShow(event) {
    const modal = $(event.target);
    // 可以在這裡添加模態框顯示時的邏輯
}

// 處理模態框隱藏
function handleModalHidden(event) {
    const modal = $(event.target);
    modal.find('form')[0]?.reset();
}

// 處理 AJAX 表單提交
function handleAjaxFormSubmit(event) {
    event.preventDefault();
    
    const form = $(event.target);
    const url = form.attr('action');
    const method = form.attr('method') || 'POST';
    const data = form.serialize();
    
    const submitButton = form.find('button[type="submit"]');
    const originalText = submitButton.text();
    
    submitButton.prop('disabled', true).html('<span class="loading"></span> 處理中...');
    
    $.ajax({
        url: url,
        method: method,
        data: data,
        success: function(response) {
            if (response.success) {
                showSuccess(response.message || '操作成功');
                form.closest('.modal').modal('hide');
                loadStockData();
            } else {
                showError(response.message || '操作失敗');
            }
        },
        error: function() {
            showError('操作時發生錯誤');
        },
        complete: function() {
            submitButton.prop('disabled', false).text(originalText);
        }
    });
}

// 處理資料刷新
function handleRefreshData(event) {
    event.preventDefault();
    loadInitialData();
    showSuccess('資料已刷新');
}

// 處理資料導出
function handleExportData(event) {
    event.preventDefault();
    const format = $(event.target).data('format') || 'csv';
    window.open(`api/export.php?format=${format}`, '_blank');
}

// 開始自動更新
function startAutoUpdate() {
    updateInterval = setInterval(function() {
        loadMarketIndices();
        loadStockData();
    }, 30000); // 每30秒更新一次
}

// 停止自動更新
function stopAutoUpdate() {
    if (updateInterval) {
        clearInterval(updateInterval);
        updateInterval = null;
    }
}

// 顯示載入中
function showLoading(selector) {
    $(selector).html('<div class="text-center"><div class="loading"></div> 載入中...</div>');
}

// 隱藏載入中
function hideLoading(selector) {
    // 由其他函數處理內容更新
}

// 顯示成功訊息
function showSuccess(message) {
    showAlert(message, 'success');
}

// 顯示錯誤訊息
function showError(message) {
    showAlert(message, 'danger');
}

// 顯示警告訊息
function showWarning(message) {
    showAlert(message, 'warning');
}

// 顯示資訊訊息
function showInfo(message) {
    showAlert(message, 'info');
}

// 顯示警告框
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alertContainer').append(alertHtml);
    
    // 自動關閉
    setTimeout(function() {
        $('#alertContainer .alert').first().fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// 格式化數字
function formatNumber(num, decimals = 2) {
    return parseFloat(num).toLocaleString('zh-TW', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// 格式化日期
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// 格式化貨幣
function formatCurrency(amount) {
    return '$' + formatNumber(amount);
}

// 格式化百分比
function formatPercentage(percent) {
    return formatNumber(percent) + '%';
}

// 獲取股票顏色類別
function getStockColorClass(change) {
    if (change > 0) return 'text-success';
    if (change < 0) return 'text-danger';
    return 'text-secondary';
}

// 更新投資組合圖表
function updatePortfolioChart(holdings) {
    if (typeof Chart !== 'undefined' && holdings.length > 0) {
        const ctx = document.getElementById('portfolioChart');
        if (ctx) {
            const data = {
                labels: holdings.map(h => h.stockCode),
                datasets: [{
                    data: holdings.map(h => h.marketValue),
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                        '#ffecd2', '#fcb69f', '#a8edea', '#fed6e3'
                    ]
                }]
            };
            
            if (chartInstances.portfolio) {
                chartInstances.portfolio.destroy();
            }
            
            chartInstances.portfolio = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}

// 清理資源
function cleanup() {
    stopAutoUpdate();
    
    // 清理圖表
    Object.values(chartInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
    
    // 清理工具提示和彈出視窗
    $('[data-bs-toggle="tooltip"]').tooltip('dispose');
    $('[data-bs-toggle="popover"]').popover('dispose');
}

// 頁面卸載時清理
$(window).on('beforeunload', cleanup);

// 導出函數供其他腳本使用
window.StockPortfolio = {
    loadStockData,
    updateMarketIndices,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    formatNumber,
    formatDate,
    formatCurrency,
    formatPercentage
};
