// categories.js - 分類頁面功能
// 與管理端產品和分類系統完全串聯

// 全域變數
let currentFilters = {
    category_id: null,
    price_min: null,
    price_max: null,
    search: null,
    sort: 'newest',
    page: 1
};

let currentView = 'grid'; // 'grid' 或 'list'
let allCategories = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('分類頁面載入完成');
    
    // 解析URL參數
    parseUrlParams();
    
    // 載入分類列表
    loadCategories();
    
    // 載入商品
    loadProducts();
    
    // 初始化事件監聽器
    initializeEventListeners();
});

/**
 * 解析URL參數
 */
function parseUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // 支援多種參數格式以向後兼容
    const categoryId = urlParams.get('category_id') || urlParams.get('category') || urlParams.get('cat');
    const categoryName = urlParams.get('name') || urlParams.get('category_name');
    const search = urlParams.get('search') || urlParams.get('q');
    const sort = urlParams.get('sort');
    const priceMin = urlParams.get('price_min');
    const priceMax = urlParams.get('price_max');
    
    // 設置篩選條件
    if (categoryId) {
        currentFilters.category_id = categoryId;
    }
    
    if (search) {
        currentFilters.search = search;
        document.getElementById('search-input').value = search;
    }
    
    if (sort) {
        currentFilters.sort = sort;
        document.getElementById('sort-select').value = sort;
    }
    
    if (priceMin) {
        currentFilters.price_min = parseFloat(priceMin);
        document.getElementById('price-min').value = priceMin;
    }
    
    if (priceMax) {
        currentFilters.price_max = parseFloat(priceMax);
        document.getElementById('price-max').value = priceMax;
    }
    
    // 更新頁面標題
    if (categoryName) {
        updatePageTitle(categoryName);
    }
}

/**
 * 載入分類列表
 */
function loadCategories() {
    fetch('api/categories.php')
        .then(response => response.json())
        .then(data => {
            console.log('分類 API 回應:', data); // 偵錯用
            
            if (data.success && data.categories) {
                allCategories = data.categories;
                renderCategoryFilters(data.categories);
                
                // 如果有指定分類，更新頁面標題
                if (currentFilters.category_id) {
                    const category = findCategoryById(currentFilters.category_id);
                    if (category) {
                        updatePageTitle(category.name, category.description);
                    }
                }
            } else {
                console.error('分類 API 失敗:', data);
                document.getElementById('category-filters').innerHTML = 
                    '<div class="text-danger">載入分類失敗：' + (data.error || '未知錯誤') + '</div>';
            }
        })
        .catch(error => {
            console.error('載入分類失敗:', error);
            document.getElementById('category-filters').innerHTML = 
                '<div class="text-danger">載入分類失敗</div>';
        });
}

/**
 * 渲染分類篩選器
 */
function renderCategoryFilters(categories) {
    const container = document.getElementById('category-filters');
    
    let html = `
        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="category" id="cat-all" value="all" 
                   ${!currentFilters.category_id ? 'checked' : ''} 
                   onchange="selectCategory(null)">
            <label class="form-check-label" for="cat-all">
                所有商品
            </label>
        </div>
    `;
    
    function renderCategoryTree(cats, level = 0) {
        return cats.map(category => {
            const isSelected = currentFilters.category_id == category.id;
            const indent = level > 0 ? 'style="margin-left: ' + (level * 15) + 'px;"' : '';
            
            let categoryHtml = `
                <div class="form-check mb-1" ${indent}>
                    <input class="form-check-input" type="radio" name="category" 
                           id="cat-${category.id}" value="${category.id}" 
                           ${isSelected ? 'checked' : ''} 
                           onchange="selectCategory(${category.id}, '${category.name.replace(/'/g, '\\\'')}')" >
                    <label class="form-check-label" for="cat-${category.id}">
                        ${category.name}
                    </label>
                </div>
            `;
            
            // 如果有子分類，遞迴渲染
            if (category.children && category.children.length > 0) {
                categoryHtml += renderCategoryTree(category.children, level + 1);
            }
            
            return categoryHtml;
        }).join('');
    }
    
    html += renderCategoryTree(categories);
    container.innerHTML = html;
}

/**
 * 選擇分類
 */
function selectCategory(categoryId, categoryName = null) {
    currentFilters.category_id = categoryId;
    currentFilters.page = 1;
    
    if (categoryId) {
        const category = findCategoryById(categoryId);
        if (category) {
            updatePageTitle(category.name, category.description);
        } else if (categoryName) {
            updatePageTitle(categoryName);
        }
    } else {
        updatePageTitle('所有商品', '探索我們精選的公仔收藏');
    }
    
    updateUrl();
    updateFilterTags();
    loadProducts();
}

/**
 * 根據ID查找分類
 */
function findCategoryById(id) {
    function search(categories) {
        for (let category of categories) {
            if (category.id == id) return category;
            if (category.children) {
                const found = search(category.children);
                if (found) return found;
            }
        }
        return null;
    }
    return search(allCategories);
}

/**
 * 載入商品
 */
function loadProducts() {
    showLoading();
    
    // 建立查詢參數
    const params = new URLSearchParams({
        page: currentFilters.page,
        limit: 12
    });
    
    if (currentFilters.category_id) {
        params.append('category_id', currentFilters.category_id);
    }
    if (currentFilters.price_min) {
        params.append('price_min', currentFilters.price_min);
    }
    if (currentFilters.price_max) {
        params.append('price_max', currentFilters.price_max);
    }
    if (currentFilters.search) {
        params.append('search', currentFilters.search);
    }
    if (currentFilters.sort) {
        params.append('sort', currentFilters.sort);
    }
    
    fetch(`api/products.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            console.log('商品 API 回應:', data); // 偵錯用
            hideLoading();
            
            if (data.success && data.products) {
                renderProducts(data.products);
                renderPagination(data.pagination);
                updateResultCount(data.pagination);
            } else {
                console.error('商品 API 失敗:', data);
                showNoProducts(data.error || '載入失敗');
            }
        })
        .catch(error => {
            console.error('載入商品失敗:', error);
            hideLoading();
            showNoProducts('網路錯誤，請稍後再試');
        });
}

/**
 * 渲染商品列表
 */
function renderProducts(products) {
    const container = document.getElementById('products-container');
    
    if (currentView === 'grid') {
        container.className = 'product-grid';
        container.innerHTML = products.map(product => `
            <div class="product-card card h-100">
                <img src="${product.image_url}" 
                     alt="${product.name}" 
                     class="product-image"
                     onerror="this.src='assets/images/placeholder_figure.jpg'"
                     loading="lazy">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-text text-muted small">${product.description || '暫無描述'}</p>
                    
                    ${renderCategoryTags(product.categories)}
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="h5 mb-0 text-primary">$${parseFloat(product.price).toLocaleString()}</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-primary btn-sm flex-fill" onclick="addToCartQuick(${product.id})">
                                <i class="bi bi-cart-plus"></i> 加入購物車
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewProduct(${product.id})" title="查看詳情">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } else {
        // 列表檢視
        container.className = 'product-list';
        container.innerHTML = products.map(product => `
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-3">
                        <img src="${product.image_url}" 
                             alt="${product.name}" 
                             class="img-fluid rounded-start h-100"
                             style="object-fit: cover;"
                             onerror="this.src='assets/images/placeholder_figure.jpg'"
                             loading="lazy">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="card-text">${product.description || '暫無描述'}</p>
                            ${renderCategoryTags(product.categories)}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="h4 mb-0 text-primary">$${parseFloat(product.price).toLocaleString()}</span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" onclick="addToCartQuick(${product.id})">
                                        <i class="bi bi-cart-plus"></i> 加入購物車
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="viewProduct(${product.id})">
                                        查看詳情
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

/**
 * 渲染分類標籤
 */
function renderCategoryTags(categories) {
    if (!categories || categories.length === 0) return '';
    
    return `
        <div class="category-tags mb-2">
            ${categories.map(cat => 
                `<a href="categories.html?category_id=${cat.id}&name=${encodeURIComponent(cat.name)}" 
                   class="badge bg-light text-primary text-decoration-none me-1 category-tag">${cat.name}</a>`
            ).join('')}
        </div>
    `;
}

/**
 * 套用價格篩選
 */
function applyPriceFilter() {
    const priceMin = document.getElementById('price-min').value;
    const priceMax = document.getElementById('price-max').value;
    
    currentFilters.price_min = priceMin ? parseFloat(priceMin) : null;
    currentFilters.price_max = priceMax ? parseFloat(priceMax) : null;
    currentFilters.page = 1;
    
    updateUrl();
    updateFilterTags();
    loadProducts();
}

/**
 * 套用搜尋
 */
function applySearch() {
    const search = document.getElementById('search-input').value.trim();
    currentFilters.search = search || null;
    currentFilters.page = 1;
    
    updateUrl();
    updateFilterTags();
    loadProducts();
}

/**
 * 套用排序
 */
function applySorting() {
    const sort = document.getElementById('sort-select').value;
    currentFilters.sort = sort;
    currentFilters.page = 1;
    
    updateUrl();
    loadProducts();
}

/**
 * 重置篩選條件
 */
function resetFilters() {
    currentFilters = {
        category_id: null,
        price_min: null,
        price_max: null,
        search: null,
        sort: 'newest',
        page: 1
    };
    
    // 重置表單
    document.getElementById('cat-all').checked = true;
    document.getElementById('price-min').value = '';
    document.getElementById('price-max').value = '';
    document.getElementById('search-input').value = '';
    document.getElementById('sort-select').value = 'newest';
    
    updatePageTitle('所有商品', '探索我們精選的公仔收藏');
    updateUrl();
    updateFilterTags();
    loadProducts();
}

/**
 * 切換檢視模式
 */
function switchView(viewMode) {
    currentView = viewMode;
    
    // 更新按鈕狀態
    document.getElementById('grid-view').classList.toggle('active', viewMode === 'grid');
    document.getElementById('list-view').classList.toggle('active', viewMode === 'list');
    
    // 如果已有產品，重新渲染
    const container = document.getElementById('products-container');
    if (container.children.length > 0) {
        loadProducts(); // 重新載入以使用新的檢視模式
    }
}

/**
 * 更新頁面標題
 */
function updatePageTitle(title = '所有商品', description = '探索我們精選的公仔收藏') {
    document.getElementById('page-title').textContent = `${title} - 公仔天堂`;
    document.getElementById('category-title').textContent = title;
    document.getElementById('category-description').textContent = description;
    document.getElementById('current-category').textContent = title;
}

/**
 * 更新URL
 */
function updateUrl() {
    const params = new URLSearchParams();
    
    if (currentFilters.category_id) {
        params.append('category_id', currentFilters.category_id);
        const category = findCategoryById(currentFilters.category_id);
        if (category) {
            params.append('name', category.name);
        }
    }
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.sort !== 'newest') params.append('sort', currentFilters.sort);
    if (currentFilters.price_min) params.append('price_min', currentFilters.price_min);
    if (currentFilters.price_max) params.append('price_max', currentFilters.price_max);
    
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.replaceState({}, '', newUrl);
}

/**
 * 更新篩選標籤
 */
function updateFilterTags() {
    const tags = [];
    
    if (currentFilters.category_id) {
        const category = findCategoryById(currentFilters.category_id);
        if (category) {
            tags.push(`分類：${category.name}`);
        }
    }
    
    if (currentFilters.price_min || currentFilters.price_max) {
        const min = currentFilters.price_min || '0';
        const max = currentFilters.price_max || '∞';
        tags.push(`價格：$${min} - $${max}`);
    }
    
    if (currentFilters.search) {
        tags.push(`搜尋：${currentFilters.search}`);
    }
    
    const activeFiltersDiv = document.getElementById('active-filters');
    const filterTagsDiv = document.getElementById('filter-tags');
    
    if (tags.length > 0) {
        const tagsHtml = tags.map(tag => 
            `<span class="badge bg-secondary me-1 mb-1">${tag}</span>`
        ).join('');
        filterTagsDiv.innerHTML = tagsHtml;
        activeFiltersDiv.style.display = 'block';
    } else {
        activeFiltersDiv.style.display = 'none';
    }
}

/**
 * 渲染分頁
 */
function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    
    if (!pagination || pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    const { current_page, total_pages } = pagination;
    let html = '<nav><ul class="pagination">';
    
    // 上一頁
    html += `
        <li class="page-item ${current_page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${current_page - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // 頁碼
    for (let i = 1; i <= total_pages; i++) {
        if (i === current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === total_pages || Math.abs(i - current_page) <= 2) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
        } else if (i === current_page - 3 || i === current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // 下一頁
    html += `
        <li class="page-item ${current_page >= total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${current_page + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

/**
 * 更換頁面
 */
function changePage(page) {
    currentFilters.page = page;
    loadProducts();
    
    // 滾動到頂部
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * 更新結果計數
 */
function updateResultCount(pagination) {
    const countElement = document.getElementById('result-count');
    if (pagination) {
        const start = (pagination.current_page - 1) * pagination.limit + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_count);
        countElement.textContent = `顯示 ${start}-${end} 項，共 ${pagination.total_count} 項商品`;
    } else {
        countElement.textContent = '';
    }
}

/**
 * 查看商品詳情
 */
function viewProduct(productId) {
    window.location.href = `product_detail.html?id=${productId}`;
}

/**
 * 顯示載入中
 */
function showLoading() {
    document.getElementById('loading-spinner').style.display = 'flex';
    document.getElementById('products-container').style.display = 'none';
}

/**
 * 隱藏載入中
 */
function hideLoading() {
    document.getElementById('loading-spinner').style.display = 'none';
    document.getElementById('products-container').style.display = 'block';
}

/**
 * 顯示無商品訊息
 */
function showNoProducts(message = '沒有找到符合條件的商品') {
    const container = document.getElementById('products-container');
    container.className = '';
    container.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-box-seam display-1 text-muted"></i>
            <h3 class="mt-3">${message}</h3>
            <p class="text-muted">請嘗試調整篩選條件或搜尋其他關鍵字</p>
            <button class="btn btn-primary" onclick="resetFilters()">重置篩選</button>
        </div>
    `;
}

/**
 * 快速加入購物車（預設數量為1）
 */
function addToCartQuick(productId) {
    fetch('api/cart_add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 顯示成功訊息
            showSuccessToast(`${data.product_name || '商品'} 已加入購物車！`);
            
            // 更新購物車數量
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount();
            }
        } else {
            showErrorToast('加入購物車失敗：' + (data.error || '未知錯誤'));
        }
    })
    .catch(error => {
        console.error('加入購物車錯誤:', error);
        showErrorToast('加入購物車失敗，請稍後再試');
    });
}

/**
 * 顯示成功提示
 */
function showSuccessToast(message) {
    // 創建 toast 元素
    const toastHtml = `
        <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // 找到或創建 toast 容器
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1080';
        document.body.appendChild(toastContainer);
    }
    
    // 添加 toast
    toastContainer.innerHTML = toastHtml;
    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // 3秒後自動移除
    setTimeout(() => {
        if (toastElement && toastElement.parentNode) {
            toastElement.remove();
        }
    }, 3000);
}

/**
 * 顯示錯誤提示
 */
function showErrorToast(message) {
    // 創建 toast 元素
    const toastHtml = `
        <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // 找到或創建 toast 容器
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1080';
        document.body.appendChild(toastContainer);
    }
    
    // 添加 toast
    toastContainer.innerHTML = toastHtml;
    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // 5秒後自動移除
    setTimeout(() => {
        if (toastElement && toastElement.parentNode) {
            toastElement.remove();
        }
    }, 5000);
}

/**
 * 初始化事件監聽器
 */
function initializeEventListeners() {
    // 搜尋框回車事件
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applySearch();
        }
    });
    
    // 價格輸入框回車事件
    document.getElementById('price-min').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyPriceFilter();
        }
    });
    
    document.getElementById('price-max').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyPriceFilter();
        }
    });
}
