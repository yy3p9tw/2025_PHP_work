// category-navbar.js
// 動態載入巢狀分類並渲染到 navbar

$(function() {
    // 載入分類選單
    loadCategories();
    
    // 載入購物車數量（暫時固定為0，後續實作購物車功能時會動態更新）
    updateCartCount(0);
});

/**
 * 載入分類資料
 */
function loadCategories() {
    fetch('api/categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const menuHtml = renderCategoryMenu(data.categories);
                document.getElementById('category-dropdown-menu').innerHTML = menuHtml;
            } else {
                console.error('載入分類失敗:', data.error);
                document.getElementById('category-dropdown-menu').innerHTML = 
                    '<li><a class="dropdown-item text-danger" href="#">載入失敗</a></li>';
            }
        })
        .catch(error => {
            console.error('分類 API 錯誤:', error);
            document.getElementById('category-dropdown-menu').innerHTML = 
                '<li><a class="dropdown-item text-danger" href="#">載入失敗</a></li>';
        });
}

/**
 * 渲染分類選單
 */
function renderCategoryMenu(categories) {
    let html = '';
    categories.forEach(cat => {
        if (cat.children && cat.children.length > 0) {
            html += `
                <li class="dropdown-submenu position-relative">
                    <a class="dropdown-item dropdown-toggle" href="category.html?cid=${cat.id}" data-bs-toggle="dropdown">
                        ${cat.name}
                    </a>
                    <ul class="dropdown-menu">
                        ${renderCategoryMenu(cat.children)}
                    </ul>
                </li>`;
        } else {
            html += `<li><a class="dropdown-item" href="category.html?cid=${cat.id}">${cat.name}</a></li>`;
        }
    });
    return html;
}

/**
 * 更新購物車數量顯示
 */
function updateCartCount(count) {
    const cartBadges = document.querySelectorAll('.cart-count');
    cartBadges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    });
}

// Bootstrap 5 巢狀下拉選單支援
$(document).on('mouseenter', '.dropdown-submenu', function() {
    $(this).children('.dropdown-menu').addClass('show');
});

$(document).on('mouseleave', '.dropdown-submenu', function() {
    $(this).children('.dropdown-menu').removeClass('show');
});

// RWD: 手機版點擊展開
$(document).on('click', '.dropdown-submenu > a', function(e) {
    if (window.innerWidth < 992) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggleClass('show');
    }
});

// 避免點擊子選單時關閉下拉
$(document).on('click', '.dropdown-submenu .dropdown-menu', function(e) {
    e.stopPropagation();
});
