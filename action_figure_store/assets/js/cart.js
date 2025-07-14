import { apiRequest } from './api.js';

// cart.js
// 購物車功能管理

// 全域變數
let cartItems = [];
let cartTotal = 0;
let shippingFee = 60; // 基本運費
let freeShippingThreshold = 1000; // 免運門檻

document.addEventListener('DOMContentLoaded', function() {
    console.log('購物車頁面載入完成');
    
    // 載入購物車內容
    loadCartItems();
    
    // 載入推薦商品
    loadRecommendedProducts();
    
    // 初始化 Modal 事件
    initializeModals();
});

/**
 * 載入購物車商品
 */
async function loadCartItems() {
    showLoading();
    
    try {
        const data = await apiRequest('/api/cart/get.php');
        
        hideLoading();
        
        if (data.success && data.items && data.items.length > 0) {
            cartItems = data.items;
            renderCartItems();
            updateCartSummary();
            showCartContent();
        } else {
            showEmptyCart();
        }
    } catch (error) {
        console.error('載入購物車失敗:', error);
        hideLoading();
        
        // 如果 API 不存在，從 localStorage 載入
        loadCartFromLocalStorage();
    }
}

/**
 * 從 localStorage 載入購物車（暫時方案）
 */
function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('cart_items');
    if (savedCart) {
        try {
            cartItems = JSON.parse(savedCart);
            if (cartItems.length > 0) {
                renderCartItems();
                updateCartSummary();
                showCartContent();
            } else {
                showEmptyCart();
            }
        } catch (e) {
            console.error('解析購物車資料失敗:', e);
            showEmptyCart();
        }
    } else {
        showEmptyCart();
    }
}

/**
 * 渲染購物車商品
 */
function renderCartItems() {
    const container = document.getElementById('cart-items-container');
    
    const itemsHtml = cartItems.map(item => `
        <div class="card mb-3" data-product-id="${item.product_id}">
            <div class="row g-0">
                <div class="col-md-2">
                    <img src="${item.image_url}" 
                         alt="${item.name}" 
                         class="img-fluid rounded-start h-100 object-fit-cover"
                         onerror="this.src='assets/images/placeholder_figure.jpg'">
                </div>
                <div class="col-md-10">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="card-title mb-1">${item.name}</h6>
                                <p class="card-text text-muted small mb-0">
                                    ${item.description ? item.description.substring(0, 50) + '...' : ''}
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="fw-bold text-primary">$${item.price}</span>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" class="form-control text-center" 
                                           value="${item.quantity}" min="1" max="99"
                                           onchange="updateQuantity(${item.product_id}, this.value)">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="fw-bold">$${(item.price * item.quantity).toFixed(0)}</span>
                            </div>
                            <div class="col-md-1 text-center">
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="confirmRemoveItem(${item.product_id})"
                                        title="移除商品">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = itemsHtml;
}

/**
 * 更新商品數量
 */
function updateQuantity(productId, newQuantity) {
    const quantity = Math.max(1, Math.min(99, parseInt(newQuantity)));
    
    if (isNaN(quantity)) return;
    
    // 先更新本地資料
    const itemIndex = cartItems.findIndex(item => item.product_id == productId);
    if (itemIndex !== -1) {
        cartItems[itemIndex].quantity = quantity;
        
        // 更新顯示
        renderCartItems();
        updateCartSummary();
        saveCartToLocalStorage();
        
        // 呼叫 API 更新
        updateCartItemAPI(productId, quantity);
    }
}

/**
 * 呼叫 API 更新購物車商品
 */
function updateCartItemAPI(productId, quantity) {
    fetch('api/cart_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('更新購物車失敗:', data.error);
            // 如果更新失敗，重新載入購物車
            loadCartItems();
        }
    })
    .catch(error => {
        console.error('更新購物車 API 錯誤:', error);
    });
}

/**
 * 確認移除商品
 */
function confirmRemoveItem(productId) {
    // 顯示確認 Modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // 設定確認按鈕事件
    document.getElementById('confirm-delete-btn').onclick = function() {
        removeFromCart(productId);
        modal.hide();
    };
    
    modal.show();
}

/**
 * 從購物車移除商品
 */
function removeFromCart(productId) {
    // 從本地陣列移除
    cartItems = cartItems.filter(item => item.product_id != productId);
    
    // 更新顯示
    if (cartItems.length > 0) {
        renderCartItems();
        updateCartSummary();
    } else {
        showEmptyCart();
    }
    
    saveCartToLocalStorage();
    
    // 呼叫 API 移除
    removeCartItemAPI(productId);
    
    // 更新導航列購物車數量
    updateNavbarCartCount();
}

/**
 * 呼叫 API 移除購物車商品
 */
function removeCartItemAPI(productId) {
    fetch('api/cart_remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('移除商品失敗:', data.error);
        }
    })
    .catch(error => {
        console.error('移除商品 API 錯誤:', error);
    });
}

/**
 * 清空購物車
 */
function clearCart() {
    const modal = new bootstrap.Modal(document.getElementById('clearCartModal'));
    
    // 設定確認按鈕事件
    document.getElementById('confirm-clear-btn').onclick = function() {
        cartItems = [];
        localStorage.removeItem('cart_items');
        
        showEmptyCart();
        updateNavbarCartCount();
        
        // 呼叫 API 清空
        clearCartAPI();
        
        modal.hide();
    };
    
    modal.show();
}

/**
 * 呼叫 API 清空購物車
 */
function clearCartAPI() {
    fetch('api/cart_clear.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('清空購物車失敗:', data.error);
        }
    })
    .catch(error => {
        console.error('清空購物車 API 錯誤:', error);
    });
}

/**
 * 更新購物車摘要
 */
function updateCartSummary() {
    const totalQuantity = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const currentShippingFee = subtotal >= freeShippingThreshold ? 0 : shippingFee;
    const total = subtotal + currentShippingFee;
    
    cartTotal = total;
    
    // 更新摘要顯示
    document.getElementById('summary-quantity').textContent = totalQuantity;
    document.getElementById('summary-subtotal').textContent = `$${subtotal.toFixed(0)}`;
    document.getElementById('summary-shipping').textContent = currentShippingFee === 0 ? '免費' : `$${currentShippingFee}`;
    document.getElementById('summary-total').textContent = `$${total.toFixed(0)}`;
    
    // 顯示免運提示
    if (subtotal > 0 && subtotal < freeShippingThreshold) {
        const remaining = freeShippingThreshold - subtotal;
        const shippingInfo = document.getElementById('summary-shipping').parentElement;
        shippingInfo.innerHTML = `
            <span>運費：</span>
            <div class="text-end">
                <span>$${currentShippingFee}</span>
                <small class="d-block text-muted">還差 $${remaining.toFixed(0)} 享免運</small>
            </div>
        `;
    }
}

/**
 * 套用優惠券
 */
function applyCoupon() {
    const couponCode = document.getElementById('coupon-code').value.trim();
    
    if (!couponCode) {
        alert('請輸入優惠券代碼');
        return;
    }
    
    // TODO: 實作優惠券功能
    alert('優惠券功能將在後續開發中實作');
}

/**
 * 前往結帳
 */
function proceedToCheckout() {
    if (cartItems.length === 0) {
        alert('購物車是空的，請先添加商品');
        return;
    }
    
    // TODO: 實作結帳流程
    alert('結帳功能將在後續開發中實作');
}

/**
 * 載入推薦商品
 */
function loadRecommendedProducts() {
    fetch('api/products.php?limit=3&sort=newest')
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                renderRecommendedProducts(data.products);
                document.getElementById('recommended-products').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('載入推薦商品失敗:', error);
        });
}

/**
 * 渲染推薦商品
 */
function renderRecommendedProducts(products) {
    const container = document.getElementById('recommended-items');
    
    const productsHtml = products.map(product => `
        <div class="d-flex align-items-center mb-3 border-bottom pb-3">
            <img src="${product.image_url}" 
                 alt="${product.name}" 
                 class="rounded me-3"
                 style="width: 60px; height: 60px; object-fit: cover;"
                 onerror="this.src='assets/images/placeholder_figure.jpg'">
            <div class="flex-grow-1">
                <h6 class="mb-1 small">${product.name}</h6>
                <p class="text-primary mb-1 fw-bold">$${product.price}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="addRecommendedToCart(${product.id})">
                    <i class="bi bi-cart-plus me-1"></i>加入購物車
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = productsHtml;
}

/**
 * 添加推薦商品到購物車
 */
function addRecommendedToCart(productId) {
    // 先獲取商品資訊
    fetch(`api/product_detail.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            if (product) {
                addToCartLocal(product, 1);
                alert(`${product.name} 已加入購物車！`);
                loadCartItems(); // 重新載入購物車
            }
        })
        .catch(error => {
            console.error('載入商品詳情失敗:', error);
        });
}

/**
 * 加入商品到購物車（本地處理）
 */
function addToCartLocal(product, quantity) {
    const existingItem = cartItems.find(item => item.product_id == product.id);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cartItems.push({
            product_id: product.id,
            name: product.name,
            description: product.description,
            price: parseFloat(product.price),
            image_url: product.image_url,
            quantity: quantity
        });
    }
    
    saveCartToLocalStorage();
    updateNavbarCartCount();
}

/**
 * 儲存購物車到 localStorage
 */
function saveCartToLocalStorage() {
    localStorage.setItem('cart_items', JSON.stringify(cartItems));
}

/**
 * 更新導航列購物車數量
 */
function updateNavbarCartCount() {
    const totalQuantity = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    
    // 呼叫全域的 updateCartCount 函數
    if (typeof window.updateCartCount === 'function') {
        window.updateCartCount(totalQuantity);
    }
}

/**
 * 顯示載入中
 */
function showLoading() {
    document.getElementById('cart-loading').style.display = 'block';
    document.getElementById('cart-items-container').style.display = 'none';
    document.getElementById('empty-cart').style.display = 'none';
    document.getElementById('cart-summary').style.display = 'none';
    document.getElementById('continue-shopping').style.display = 'none';
    document.getElementById('clear-cart-btn').style.display = 'none';
}

/**
 * 隱藏載入中
 */
function hideLoading() {
    document.getElementById('cart-loading').style.display = 'none';
}

/**
 * 顯示購物車內容
 */
function showCartContent() {
    document.getElementById('cart-items-container').style.display = 'block';
    document.getElementById('cart-summary').style.display = 'block';
    document.getElementById('continue-shopping').style.display = 'block';
    document.getElementById('clear-cart-btn').style.display = 'inline-block';
    document.getElementById('empty-cart').style.display = 'none';
}

/**
 * 顯示空購物車
 */
function showEmptyCart() {
    document.getElementById('empty-cart').style.display = 'block';
    document.getElementById('cart-items-container').style.display = 'none';
    document.getElementById('cart-summary').style.display = 'none';
    document.getElementById('continue-shopping').style.display = 'none';
    document.getElementById('clear-cart-btn').style.display = 'none';
}

/**
 * 初始化 Modal 事件
 */
function initializeModals() {
    // 清除 Modal 事件，避免重複綁定
    const deleteModal = document.getElementById('deleteConfirmModal');
    const clearModal = document.getElementById('clearCartModal');
    
    deleteModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('confirm-delete-btn').onclick = null;
    });
    
    clearModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('confirm-clear-btn').onclick = null;
    });
}

// 全域函數：從其他頁面添加商品到購物車
window.addToCart = function(productId, quantity = 1) {
    fetch(`api/product_detail.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            if (product) {
                addToCartLocal(product, quantity);
                
                // 顯示成功訊息
                showAddToCartSuccess(product.name);
            }
        })
        .catch(error => {
            console.error('加入購物車失敗:', error);
            alert('加入購物車失敗，請稍後再試');
        });
};

/**
 * 顯示加入購物車成功訊息
 */
function showAddToCartSuccess(productName) {
    // 創建 Toast 訊息
    const toastHtml = `
        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>${productName} 已加入購物車！
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // 創建 Toast 容器（如果不存在）
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // 添加 Toast
    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHtml;
    toastContainer.appendChild(toastElement.firstElementChild);
    
    // 顯示 Toast
    const toast = new bootstrap.Toast(toastContainer.lastElementChild);
    toast.show();
    
    // 移除已隱藏的 Toast
    toastContainer.addEventListener('hidden.bs.toast', function(e) {
        e.target.remove();
    });
}
