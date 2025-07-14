// navbar.js - 導覽列功能
document.addEventListener('DOMContentLoaded', function() {
    console.log('navbar.js 載入完成');
    
    // 檢查是否 navbar 已經存在（直接載入的情況）
    const categoryMenu = document.getElementById('category-dropdown-menu');
    if (categoryMenu) {
        console.log('發現已存在的分類選單，直接載入分類');
        loadCategories();
    } else {
        console.log('未發現分類選單，嘗試載入 navbar');
        // 如果 navbar 不存在，嘗試載入
        loadNavbar();
    }
});

// 載入導覽列
function loadNavbar() {
    console.log('開始載入 navbar.html');
    
    fetch('navbar.html')
        .then(response => {
            console.log('navbar.html 請求狀態:', response.status);
            return response.text();
        })
        .then(html => {
            console.log('navbar.html 載入成功');
            const navbarPlaceholder = document.getElementById('navbar-placeholder');
            if (navbarPlaceholder) {
                navbarPlaceholder.innerHTML = html;
                console.log('navbar HTML 插入完成');
                // 導覽列載入完成後，等待一下再載入分類以確保 DOM 準備好
                setTimeout(() => {
                    console.log('準備載入分類選單');
                    loadCategories();
                }, 100);
            } else {
                console.error('找不到 navbar-placeholder 元素');
            }
        })
        .catch(error => {
            console.error('載入導覽列失敗:', error);
        });
}

// 載入分類選單
function loadCategories() {
    console.log('開始載入分類選單...');
    
    const categoryMenu = document.getElementById('category-dropdown-menu');
    if (!categoryMenu) {
        console.error('找不到分類選單容器 #category-dropdown-menu');
        // 嘗試在 500ms 後重試，最多重試 5 次
        const retryCount = loadCategories.retryCount || 0;
        if (retryCount < 5) {
            console.log(`重試載入分類選單... (第 ${retryCount + 1} 次)`);
            loadCategories.retryCount = retryCount + 1;
            setTimeout(() => {
                loadCategories();
            }, 500);
        } else {
            console.error('重試次數已達上限，停止嘗試載入分類選單');
        }
        return;
    }
    
    // 重置重試計數器
    loadCategories.retryCount = 0;
    
    console.log('找到分類選單容器，開始 API 請求...');
    
    fetch('api/categories.php')
        .then(response => {
            console.log('API 回應狀態:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('分類 API 回應:', data);
            
            if (data.success && data.categories) {
                const menuHtml = renderCategoryMenu(data.categories);
                categoryMenu.innerHTML = menuHtml;
                console.log('分類選單載入完成，總數:', data.categories.length);
            } else {
                console.error('載入分類失敗:', data.error || '未知錯誤');
                categoryMenu.innerHTML = '<li><a class="dropdown-item text-danger" href="#">載入失敗</a></li>';
            }
        })
        .catch(error => {
            console.error('分類 API 錯誤:', error);
            if (categoryMenu) {
                categoryMenu.innerHTML = '<li><a class="dropdown-item text-danger" href="#">網路錯誤</a></li>';
            }
        });
}

// 渲染分類選單
function renderCategoryMenu(categories) {
    let html = '<li><a class="dropdown-item" href="categories.html">所有商品</a></li>';
    
    if (categories && categories.length > 0) {
        html += '<li><hr class="dropdown-divider"></li>';
        
        categories.forEach(category => {
            if (category.children && category.children.length > 0) {
                // 有子分類的父分類
                html += `
                    <li class="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="categories.html?category_id=${category.id}&name=${encodeURIComponent(category.name)}">
                            ${category.name}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="categories.html?category_id=${category.id}&name=${encodeURIComponent(category.name)}">所有 ${category.name}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            ${renderSubCategories(category.children)}
                        </ul>
                    </li>
                `;
            } else {
                // 沒有子分類的分類
                html += `
                    <li>
                        <a class="dropdown-item" href="categories.html?category_id=${category.id}&name=${encodeURIComponent(category.name)}">
                            ${category.name}
                        </a>
                    </li>
                `;
            }
        });
    }
    
    return html;
}

// 渲染子分類
function renderSubCategories(subCategories) {
    return subCategories.map(subCat => `
        <li>
            <a class="dropdown-item" href="categories.html?category_id=${subCat.id}&name=${encodeURIComponent(subCat.name)}">
                ${subCat.name}
            </a>
        </li>
    `).join('');
}

// 更新購物車徽章
function updateCartBadge(count = 0) {
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.style.display = 'inline';
        } else {
            cartBadge.style.display = 'none';
        }
    }
}

// 全域購物車計數更新函數
window.updateCartCount = function(count) {
    updateCartBadge(count);
};

// 載入購物車計數
function loadCartCount() {
    fetch('api/cart_get.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.summary) {
                updateCartBadge(data.summary.total_quantity);
            }
        })
        .catch(error => {
            console.error('載入購物車計數失敗:', error);
        });
}

// 高亮當前頁面導覽項目
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace('.html', ''))) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// 初始化導覽列
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    highlightCurrentPage();
    loadCartCount(); // 載入購物車計數
    
    if (window.innerWidth > 991) {
        const dropdowns = document.querySelectorAll('.dropdown-submenu');
        
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('mouseenter', function() {
                const submenu = this.querySelector('.dropdown-menu');
                if (submenu) {
                    submenu.classList.add('show');
                }
            });
            
            dropdown.addEventListener('mouseleave', function() {
                const submenu = this.querySelector('.dropdown-menu');
                if (submenu) {
                    submenu.classList.remove('show');
                }
            });
        });
    }
});

// 搜尋功能（如果需要的話）
function initializeSearch() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = `products.html?search=${encodeURIComponent(searchTerm)}`;
            }
        });
    }
}

// 響應式處理
window.addEventListener('resize', function() {
    // 根據螢幕大小調整選單行為
    if (window.innerWidth <= 991) {
        // 移動版：移除懸停效果，使用點擊
        document.querySelectorAll('.dropdown-submenu').forEach(submenu => {
            submenu.removeEventListener('mouseenter', showSubmenu);
            submenu.removeEventListener('mouseleave', hideSubmenu);
        });
    }
});

function showSubmenu() {
    const submenu = this.querySelector('.dropdown-menu');
    if (submenu) {
        submenu.classList.add('show');
    }
}

function hideSubmenu() {
    const submenu = this.querySelector('.dropdown-menu');
    if (submenu) {
        submenu.classList.remove('show');
    }
}
