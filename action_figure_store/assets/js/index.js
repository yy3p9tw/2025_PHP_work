// index.js
// 首頁動態功能

document.addEventListener('DOMContentLoaded', function() {
    console.log('首頁載入完成');
    
    // 載入輪播圖
    loadCarouselSlides();
    
    // 載入商品列表
    loadProducts();
    
    // 初始化分頁
    initPagination();
});

/**
 * 載入輪播圖
 */
function loadCarouselSlides() {
    fetch('api/carousel.php')
        .then(response => response.json())
        .then(slides => {
            if (slides && slides.length > 0) {
                renderCarousel(slides);
            } else {
                console.log('沒有輪播圖資料');
            }
        })
        .catch(error => {
            console.error('載入輪播圖失敗:', error);
        });
}

/**
 * 渲染輪播圖
 */
function renderCarousel(slides) {
    const carouselInner = document.getElementById('carousel-inner');
    const carouselIndicators = document.getElementById('carousel-indicators');
    
    if (!carouselInner || !carouselIndicators) {
        console.log('找不到輪播圖容器');
        return;
    }
    
    let innerHtml = '';
    let indicatorsHtml = '';
    
    slides.forEach((slide, index) => {
        const isActive = index === 0 ? 'active' : '';
        
        innerHtml += `
            <div class="carousel-item ${isActive}">
                <img src="${slide.image_url}" class="d-block w-100" alt="${slide.title}" style="height: 400px; object-fit: cover;"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyMCIgaGVpZ2h0PSI0MDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj7ovJXmkK3lnJbkuI08L3RleHQ+PC9zdmc+'">
                <div class="carousel-caption d-none d-md-block">
                    <h5>${slide.title}</h5>
                    ${slide.description ? `<p>${slide.description}</p>` : ''}
                </div>
            </div>
        `;
        
        indicatorsHtml += `
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="${index}" 
                    class="${isActive}" aria-current="${isActive ? 'true' : 'false'}" 
                    aria-label="Slide ${index + 1}"></button>
        `;
    });
    
    carouselInner.innerHTML = innerHtml;
    carouselIndicators.innerHTML = indicatorsHtml;
}

/**
 * 載入商品列表
 */
function loadProducts(page = 1, limit = 9) {
    const loadingElement = document.getElementById('products-loading');
    const productsContainer = document.getElementById('products-container');
    
    // 顯示載入中
    if (loadingElement) loadingElement.style.display = 'block';
    if (productsContainer) productsContainer.style.display = 'none';
    
    fetch(`api/products.php?page=${page}&limit=${limit}`)
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                renderProducts(data.products);
                updatePagination(data.pagination);
            } else {
                showNoProducts();
            }
        })
        .catch(error => {
            console.error('載入商品失敗:', error);
            showProductsError();
        })
        .finally(() => {
            // 隱藏載入中
            if (loadingElement) loadingElement.style.display = 'none';
            if (productsContainer) productsContainer.style.display = 'block';
        });
}

/**
 * 渲染商品列表
 */
function renderProducts(products) {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    let html = '<div class="row">';
    
    products.forEach(product => {
        html += `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <img src="${product.image_url}" class="card-img-top" alt="${product.name}" 
                         style="height: 250px; object-fit: cover;"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuWVhuWTgOWcluePnjwvdGV4dD48L3N2Zz4='">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text text-muted">${product.description || '暫無描述'}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-primary">$${parseFloat(product.price).toLocaleString()}</span>
                                <button class="btn btn-outline-primary btn-sm" onclick="viewProduct(${product.id})">
                                    查看詳情
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * 顯示無商品訊息
 */
function showNoProducts() {
    const container = document.getElementById('products-container');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-box-seam display-1 text-muted"></i>
                <h3 class="mt-3">暫無商品</h3>
                <p class="text-muted">目前沒有商品資料，請稍後再試。</p>
            </div>
        `;
    }
}

/**
 * 顯示載入錯誤訊息
 */
function showProductsError() {
    const container = document.getElementById('products-container');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                <h3 class="mt-3">載入失敗</h3>
                <p class="text-muted">無法載入商品資料，請重新整理頁面。</p>
                <button class="btn btn-primary" onclick="loadProducts()">重新載入</button>
            </div>
        `;
    }
}

/**
 * 初始化分頁
 */
function initPagination() {
    // 分頁功能將在 updatePagination 中實作
}

/**
 * 更新分頁資訊
 */
function updatePagination(pagination) {
    const paginationContainer = document.getElementById('pagination-container');
    if (!paginationContainer || !pagination) return;
    
    const { current_page, total_pages, total_products } = pagination;
    
    if (total_pages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let html = '<nav aria-label="商品分頁"><ul class="pagination justify-content-center">';
    
    // 上一頁
    html += `
        <li class="page-item ${current_page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadProducts(${current_page - 1}); return false;">上一頁</a>
        </li>
    `;
    
    // 頁碼
    for (let i = 1; i <= total_pages; i++) {
        if (i === current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === total_pages || Math.abs(i - current_page) <= 2) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadProducts(${i}); return false;">${i}</a></li>`;
        } else if (i === current_page - 3 || i === current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // 下一頁
    html += `
        <li class="page-item ${current_page >= total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadProducts(${current_page + 1}); return false;">下一頁</a>
        </li>
    `;
    
    html += '</ul></nav>';
    paginationContainer.innerHTML = html;
}

/**
 * 查看商品詳情
 */
function viewProduct(productId) {
    window.location.href = `product_detail.html?id=${productId}`;
}

/**
 * 平滑滾動到指定區域
 */
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
