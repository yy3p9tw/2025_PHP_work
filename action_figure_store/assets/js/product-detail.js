// product-detail.js
// 商品詳情頁動態功能

document.addEventListener('DOMContentLoaded', function() {
    console.log('商品詳情頁載入完成');
    
    // 從 URL 取得商品 ID
    const productId = getProductIdFromUrl();
    
    if (productId) {
        loadProductDetail(productId);
    } else {
        showError('無效的商品 ID');
    }
});

/**
 * 從 URL 取得商品 ID
 */
function getProductIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

/**
 * 載入商品詳情
 */
function loadProductDetail(productId) {
    const loadingElement = document.getElementById('product-loading');
    const contentElement = document.getElementById('product-content');
    
    // 顯示載入中
    if (loadingElement) loadingElement.style.display = 'block';
    if (contentElement) contentElement.style.display = 'none';
    
    fetch(`api/product_detail.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            if (product && product.id) {
                renderProductDetail(product);
                updatePageTitle(product.name);
            } else {
                showError('找不到商品資料');
            }
        })
        .catch(error => {
            console.error('載入商品詳情失敗:', error);
            showError('載入商品詳情失敗');
        })
        .finally(() => {
            // 隱藏載入中
            if (loadingElement) loadingElement.style.display = 'none';
            if (contentElement) contentElement.style.display = 'block';
        });
}

/**
 * 渲染商品詳情
 */
function renderProductDetail(product) {
    // 更新商品圖片
    const productImage = document.getElementById('product-image');
    if (productImage) {
        productImage.src = product.image_url;
        productImage.alt = product.name;
        productImage.onerror = function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuWVhuWTgOWcluePnjwvdGV4dD48L3N2Zz4=';
        };
    }
    
    // 更新商品名稱
    const productName = document.getElementById('product-name');
    if (productName) {
        productName.textContent = product.name;
    }
    
    // 更新商品價格
    const productPrice = document.getElementById('product-price');
    if (productPrice) {
        productPrice.textContent = `$${parseFloat(product.price).toLocaleString()}`;
    }
    
    // 更新商品描述
    const productDescription = document.getElementById('product-description');
    if (productDescription) {
        productDescription.textContent = product.description || '暫無商品描述';
    }
    
    // 更新商品規格（如果有的話）
    const productSpecs = document.getElementById('product-specs');
    if (productSpecs) {
        // 暫時顯示基本資訊，後續可以擴充
        productSpecs.innerHTML = `
            <li class="list-group-item d-flex justify-content-between">
                <span>商品編號：</span>
                <span>${product.id}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>上架時間：</span>
                <span>${formatDate(product.created_at)}</span>
            </li>
        `;
    }
    
    // 設定購買按鈕
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.onclick = () => addToCart(product.id);
    }
    
    // 設定數量控制
    initQuantityControls();
}

/**
 * 更新頁面標題
 */
function updatePageTitle(productName) {
    document.title = `${productName} - 公仔天堂`;
}

/**
 * 顯示錯誤訊息
 */
function showError(message) {
    const contentElement = document.getElementById('product-content');
    if (contentElement) {
        contentElement.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                <h3 class="mt-3">載入失敗</h3>
                <p class="text-muted">${message}</p>
                <a href="index.html" class="btn btn-primary">返回首頁</a>
            </div>
        `;
        contentElement.style.display = 'block';
    }
}

/**
 * 初始化數量控制
 */
function initQuantityControls() {
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-quantity');
    const increaseBtn = document.getElementById('increase-quantity');
    
    if (decreaseBtn) {
        decreaseBtn.onclick = () => {
            const currentValue = parseInt(quantityInput.value) || 1;
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        };
    }
    
    if (increaseBtn) {
        increaseBtn.onclick = () => {
            const currentValue = parseInt(quantityInput.value) || 1;
            quantityInput.value = currentValue + 1;
        };
    }
    
    if (quantityInput) {
        quantityInput.onchange = () => {
            const value = parseInt(quantityInput.value) || 1;
            if (value < 1) {
                quantityInput.value = 1;
            }
        };
    }
}

/**
 * 加入購物車
 */
function addToCart(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = parseInt(quantityInput?.value) || 1;
    
    // 暫時顯示提示訊息，後續實作購物車 API 時會改為實際功能
    alert(`已加入 ${quantity} 個商品到購物車！\n（購物車功能將在第三階段實作）`);
    
    // TODO: 實作購物車 API 呼叫
    // fetch('api/cart_add.php', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //     },
    //     body: JSON.stringify({
    //         product_id: productId,
    //         quantity: quantity
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (data.success) {
    //         showSuccessMessage('已加入購物車');
    //         updateCartCount(data.cart_count);
    //     } else {
    //         showErrorMessage('加入購物車失敗');
    //     }
    // });
}

/**
 * 格式化日期
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * 返回上一頁
 */
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = 'index.html';
    }
}
