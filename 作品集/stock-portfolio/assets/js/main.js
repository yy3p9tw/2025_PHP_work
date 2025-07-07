// 股票投資組合系統 JavaScript
$(document).ready(function() {
    // 初始化所有功能
    initializeApp();
});

// 初始化應用程式
function initializeApp() {
    console.log('初始化股票投資組合系統...');
    
    // 初始化提示工具
    initializeTooltips();
    
    // 初始化數據載入
    initializeDataLoading();
    
    // 初始化表單驗證
    initializeFormValidation();
    
    // 初始化數據表格
    initializeDataTables();
    
    // 初始化搜索功能
    initializeSearch();
    
    // 初始化動畫效果
    initializeAnimations();
    
    console.log('股票投資組合系統初始化完成');
}

// 初始化數據載入
function initializeDataLoading() {
    if ($('.market-indices-container').length > 0) {
        loadMarketIndices();
        setInterval(loadMarketIndices, 30000);
    }
    
    if ($('.hot-stocks').length > 0) {
        loadHotStocks();
    }
    
    if ($('.news-list').length > 0) {
        loadNews();
    }
}

// 初始化提示工具
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

// 初始化表單驗證
function initializeFormValidation() {
    // Bootstrap 表單驗證
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // 密碼確認驗證
    const passwordConfirm = $('#confirm_password');
    const password = $('#password');
    
    if (passwordConfirm.length && password.length) {
        passwordConfirm.on('input', function() {
            if (password.val() !== passwordConfirm.val()) {
                passwordConfirm[0].setCustomValidity('密碼不符合');
            } else {
                passwordConfirm[0].setCustomValidity('');
            }
        });
    }
}

// 初始化數據表格
function initializeDataTables() {
    $('.data-table').each(function() {
        const table = $(this);
        
        // 添加表格樣式
        table.addClass('table table-striped table-hover');
        
        // 添加排序功能
        table.find('th').css('cursor', 'pointer').on('click', function() {
            const columnIndex = $(this).index();
            sortTable(table[0], columnIndex);
        });
    });
}

// 表格排序功能
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const sortedRows = rows.sort(function(a, b) {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // 檢查是否為數字
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return parseFloat(aValue) - parseFloat(bValue);
        }
        
        return aValue.localeCompare(bValue);
    });
    
    // 重新排列行
    sortedRows.forEach(function(row) {
        tbody.appendChild(row);
    });
}

// 初始化搜索功能
function initializeSearch() {
    $('.search-input').on('input', debounce(function() {
        const searchTerm = $(this).val().toLowerCase();
        const targetTable = $($(this).data('target'));
        
        if (targetTable.length) {
            targetTable.find('tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }, 300));
}

// 初始化動畫效果
function initializeAnimations() {
    // 觀察元素進入視窗
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, {
            threshold: 0.1
        });
        
        // 觀察所有卡片
        document.querySelectorAll('.card').forEach(function(card) {
            observer.observe(card);
        });
    }
}

// 初始化即時更新
function initializeRealTimeUpdates() {
    // 每30秒更新一次價格（模擬）
    setInterval(function() {
        updateStockPrices();
    }, 30000);
}

// 更新股價（模擬）
function updateStockPrices() {
    $('.stock-price').each(function() {
        const element = $(this);
        const currentPrice = parseFloat(element.text().replace('$', '').replace(',', ''));
        
        if (!isNaN(currentPrice)) {
            // 模擬價格變化 (-2% 到 +2%)
            const changePercent = (Math.random() - 0.5) * 0.04;
            const newPrice = currentPrice * (1 + changePercent);
            
            // 更新價格
            element.text('$' + formatNumber(newPrice, 2));
            
            // 添加動畫效果
            if (changePercent > 0) {
                element.addClass('price-up');
            } else if (changePercent < 0) {
                element.addClass('price-down');
            }
            
            // 移除動畫類別
            setTimeout(function() {
                element.removeClass('price-up price-down');
            }, 1000);
        }
    });
}

// API 載入函數
function loadMarketIndices() {
    $.ajax({
        url: 'api/market-indices.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateMarketIndices(response.data);
            } else {
                console.error('載入市場指數失敗:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('載入市場指數失敗:', status, error);
            console.error('響應內容:', xhr.responseText);
        }
    });
}

function loadHotStocks() {
    $.ajax({
        url: 'api/stocks.php?action=hot',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateHotStocks(response.data);
            } else {
                console.error('載入熱門股票失敗:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('載入股票資料失敗');
        }
    });
}

function loadNews() {
    $.ajax({
        url: 'api/news.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateNews(response.data);
            } else {
                console.error('載入新聞失敗:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('載入新聞失敗:', status, error);
            console.error('響應內容:', xhr.responseText);
        }
    });
}

// 更新市場指數顯示
function updateMarketIndices(indices) {
    const container = $('.market-indices-container');
    if (container.length === 0) return;
    
    container.html('');
    indices.forEach(function(index) {
        const changeClass = index.change_value >= 0 ? 'text-success' : 'text-danger';
        const changeSign = index.change_value >= 0 ? '+' : '';
        
        const html = `
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-muted">${index.name}</h6>
                        <h5 class="mb-1">${formatNumber(index.current_value, 2)}</h5>
                        <small class="${changeClass}">
                            ${changeSign}${formatNumber(index.change_value, 2)}
                            (${changeSign}${formatNumber(index.change_percent, 2)}%)
                        </small>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });
}

// 更新熱門股票顯示
function updateHotStocks(stocks) {
    const container = $('.hot-stocks');
    if (container.length === 0) return;
    
    container.html('');
    stocks.forEach(function(stock) {
        const changeClass = stock.price_change >= 0 ? 'text-success' : 'text-danger';
        const changeSign = stock.price_change >= 0 ? '+' : '';
        
        const html = `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="card-title mb-1">${stock.code}</h6>
                                <p class="text-muted small mb-0">${stock.name}</p>
                            </div>
                            <span class="badge bg-secondary">${stock.industry}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h5 class="mb-0 stock-price">$${formatNumber(stock.current_price, 2)}</h5>
                                <small class="${changeClass}">
                                    ${changeSign}${formatNumber(stock.price_change, 2)}
                                    (${changeSign}${formatNumber(stock.change_percent, 2)}%)
                                </small>
                            </div>
                            <small class="text-muted">成交量: ${formatNumber(stock.volume)}</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });
}

// 更新新聞顯示
function updateNews(news) {
    const container = $('.news-list');
    if (container.length === 0) return;
    
    container.html('');
    news.forEach(function(item) {
        const publishedDate = new Date(item.published_at).toLocaleString('zh-TW');
        const readMoreBtn = item.url ? 
            `<a href="${item.url}" class="btn btn-sm btn-outline-primary" target="_blank">閱讀更多</a>` : '';
        
        const html = `
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="#" class="text-decoration-none text-dark">${item.title}</a>
                    </h5>
                    ${item.summary ? `<p class="card-text text-muted">${item.summary}</p>` : ''}
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            ${item.source || '本站'} • ${publishedDate}
                        </small>
                        ${readMoreBtn}
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });
}

// 工具函數
function formatNumber(num, decimals = 0) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return parseFloat(num).toLocaleString('zh-TW', { 
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals 
    });
}

function formatCurrency(amount, currency = '$') {
    return currency + formatNumber(amount);
}

function formatPercentage(value) {
    const sign = value >= 0 ? '+' : '';
    return sign + value.toFixed(2) + '%';
}

function getPriceChangeClass(change) {
    if (change > 0) return 'text-success';
    if (change < 0) return 'text-danger';
    return 'text-secondary';
}

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

function showNotification(message, type = 'info') {
    const alertClass = type === 'error' ? 'alert-danger' : 
                      type === 'success' ? 'alert-success' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // 自動消失
    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}

// 確認對話框
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// 載入狀態管理
function showLoading(element) {
    const $element = $(element);
    $element.data('original-text', $element.html());
    $element.html('<span class="loading"></span> 載入中...').prop('disabled', true);
}

function hideLoading(element) {
    const $element = $(element);
    const originalText = $element.data('original-text') || $element.html();
    $element.html(originalText).prop('disabled', false);
}

// 本地儲存管理
const Storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('無法儲存到 localStorage:', e);
        }
    },
    
    get: function(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('無法從 localStorage 讀取:', e);
            return defaultValue;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('無法從 localStorage 刪除:', e);
        }
    }
};

// 將功能導出到全域
window.StockPortfolio = {
    formatNumber,
    formatCurrency,
    formatPercentage,
    getPriceChangeClass,
    confirmAction,
    showNotification,
    showLoading,
    hideLoading,
    Storage
};
