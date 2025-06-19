// 全局變數
let currentTable = null;
let menuItems = [];
let cart = [];
let currentCategory = 'all';

// 示範菜單資料
const sampleMenu = [
    { id: 1, name: '牛肉麵', price: 180, category: 'main', emoji: '🍜' },
    { id: 2, name: '炸雞排', price: 120, category: 'main', emoji: '🍗' },
    { id: 3, name: '蔥抓餅', price: 40, category: 'appetizer', emoji: '🥞' },
    { id: 4, name: '小籠包', price: 80, category: 'appetizer', emoji: '🥟' },
    { id: 5, name: '玉米濃湯', price: 60, category: 'soup', emoji: '🍲' },
    { id: 6, name: '紫菜蛋花湯', price: 50, category: 'soup', emoji: '🥣' },
    { id: 7, name: '紅豆湯', price: 45, category: 'dessert', emoji: '🍜' },
    { id: 8, name: '芒果冰', price: 90, category: 'dessert', emoji: '🍧' },
    { id: 9, name: '珍珠奶茶', price: 55, category: 'drink', emoji: '🧋' },
    { id: 10, name: '新鮮果汁', price: 65, category: 'drink', emoji: '🥤' }
];

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    // 檢查座位號碼
    currentTable = localStorage.getItem('currentTable');
    if (!currentTable) {
        alert('請先選擇座位號碼');
        window.location.href = 'table-input.html';
        return;
    }
    
    // 顯示座位資訊
    document.getElementById('tableInfo').textContent = `${currentTable} 號桌`;
    
    // 載入菜單
    loadMenu();
    
    // 載入購物車（但不包含座位資訊）
    loadCart();
    
    // 更新購物車顯示
    updateCartDisplay();
});

// 清除會話資料（當離開頁面時）
window.addEventListener('beforeunload', function() {
    // 可選：清除座位號碼，強制下次重新輸入
    // localStorage.removeItem('currentTable');
});

// 載入菜單
async function loadMenu() {
    try {
        // 首先嘗試從管理端同步的資料載入
        const savedMenu = localStorage.getItem('customerMenuItems');
        if (savedMenu) {
            menuItems = JSON.parse(savedMenu);
        } else {
            // 如果沒有管理端資料，使用示範資料
            menuItems = sampleMenu;
        }
        renderMenu();
    } catch (error) {
        console.error('載入菜單失敗:', error);
        // 使用示範資料
        menuItems = sampleMenu;
        renderMenu();
    }
}

// 渲染菜單
function renderMenu() {
    const menuGrid = document.getElementById('menuGrid');
    let filteredItems = menuItems;
    
    if (currentCategory !== 'all') {
        filteredItems = menuItems.filter(item => item.category === currentCategory);
    }
    
    menuGrid.innerHTML = filteredItems.map(item => {
        const cartItem = cart.find(cartItem => cartItem.id === item.id);
        const currentQuantity = cartItem ? cartItem.quantity : 0;
          return `
            <div class="menu-item">
                <div class="menu-item-image">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" onerror="this.outerHTML='<div class=\\"menu-item-placeholder\\">無圖片</div>'">` :
                        `<div class="menu-item-placeholder">無圖片</div>`
                    }
                </div>
                <div class="menu-item-info">
                    <div class="menu-item-name">${item.name}</div>
                    <div class="menu-item-price">NT$ ${item.price}</div>
                    <div class="quantity-selector">
                        <span>數量:</span>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateMenuQuantity(${item.id}, -1)">-</button>
                            <span class="quantity-display" id="qty-${item.id}">${currentQuantity}</span>
                            <button class="quantity-btn" onclick="updateMenuQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    ${currentQuantity > 0 ? 
                        `<button class="add-btn" onclick="removeFromCart(${item.id})" style="background: var(--danger-color);">
                            從購物車移除
                        </button>` :
                        `<button class="add-btn" onclick="addToCart(${item.id})">
                            加入購物車
                        </button>`
                    }
                </div>
            </div>
        `;
    }).join('');
}

// 分類篩選
function filterCategory(category) {
    currentCategory = category;
    
    // 更新按鈕狀態
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // 重新渲染菜單
    renderMenu();
}

// 加入購物車
function addToCart(itemId) {
    const item = menuItems.find(item => item.id === itemId);
    if (!item) return;
    
    const existingItem = cart.find(cartItem => cartItem.id === itemId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...item,
            quantity: 1
        });
    }
    
    saveCart();
    updateCartDisplay();
    renderMenu(); // 重新渲染菜單以更新按鈕狀態
    
    // 顯示添加成功的回饋
    showToast(`${item.name} 已加入購物車`);
}

// 從購物車移除
function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    saveCart();
    updateCartDisplay();
    renderMenu(); // 重新渲染菜單以更新按鈕狀態
    
    const item = menuItems.find(item => item.id === itemId);
    showToast(`${item.name} 已從購物車移除`);
}

// 更新菜單頁面的數量
function updateMenuQuantity(itemId, change) {
    const item = menuItems.find(item => item.id === itemId);
    if (!item) return;
    
    const existingItem = cart.find(cartItem => cartItem.id === itemId);
    
    if (change > 0) {
        // 增加數量
        if (existingItem) {
            existingItem.quantity += change;
        } else {
            cart.push({
                ...item,
                quantity: change
            });
        }
    } else {
        // 減少數量
        if (existingItem) {
            existingItem.quantity += change;
            if (existingItem.quantity <= 0) {
                cart = cart.filter(cartItem => cartItem.id !== itemId);
            }
        }
    }
    
    saveCart();
    updateCartDisplay();
    
    // 只更新該商品的數量顯示，避免整個頁面重新渲染
    const qtyDisplay = document.getElementById(`qty-${itemId}`);
    const cartItem = cart.find(cartItem => cartItem.id === itemId);
    const currentQuantity = cartItem ? cartItem.quantity : 0;
    
    if (qtyDisplay) {
        qtyDisplay.textContent = currentQuantity;
    }
    
    // 如果數量變化導致需要改變按鈕狀態，則重新渲染菜單
    const prevQuantity = currentQuantity - change;
    if ((prevQuantity === 0 && currentQuantity > 0) || (prevQuantity > 0 && currentQuantity === 0)) {
        renderMenu();
    }
}

// 更新購物車顯示
function updateCartDisplay() {
    const cartCount = document.getElementById('cartCount');
    const cartItems = document.getElementById('cartItems');
    const totalPrice = document.getElementById('totalPrice');
    
    // 計算總數量
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalQuantity;
    cartCount.style.display = totalQuantity > 0 ? 'flex' : 'none';
    
    // 渲染購物車項目
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="text-center p-3">購物車是空的</div>';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div>
                    <div class="menu-item-name">${item.name}</div>
                    <div class="menu-item-price">NT$ ${item.price}</div>
                </div>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                </div>
            </div>
        `).join('');
    }
    
    // 計算總價
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    totalPrice.textContent = `總計: NT$ ${total}`;
}

// 更新數量
function updateQuantity(itemId, change) {
    const item = cart.find(item => item.id === itemId);
    if (!item) return;
    
    item.quantity += change;
    
    if (item.quantity <= 0) {
        cart = cart.filter(item => item.id !== itemId);
    }
    
    saveCart();
    updateCartDisplay();
}

// 切換購物車顯示
function toggleCart() {
    const cartModal = document.getElementById('cartModal');
    const isVisible = cartModal.style.display === 'block';
    cartModal.style.display = isVisible ? 'none' : 'block';
}

// 提交訂單
async function submitOrder() {
    if (cart.length === 0) {
        alert('購物車是空的！');
        return;
    }
    
    const order = {
        table_number: currentTable,
        items: cart,
        total_amount: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
        created_at: new Date().toISOString()
    };
    
    try {
        // 這裡應該要發送到 API
        console.log('提交訂單:', order);
        
        // 模擬 API 請求
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // 清空購物車
        cart = [];
        saveCart();
        updateCartDisplay();
        toggleCart();
        
        // 清除座位號碼，防止重複使用
        localStorage.removeItem('currentTable');
        
        alert('訂單已送出！謝謝您的惠顧');
        
        // 返回座位選擇頁面
        window.location.href = 'table-input.html';
        
    } catch (error) {
        console.error('提交訂單失敗:', error);
        alert('訂單提交失敗，請重試');
    }
}

// 儲存購物車
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// 載入購物車
function loadCart() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}

// 顯示提示訊息
function showToast(message) {
    // 簡單的提示實現
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--success-color);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 3000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 2000);
}

// 點擊模態框外部關閉
document.addEventListener('click', function(e) {
    const cartModal = document.getElementById('cartModal');
    if (e.target === cartModal) {
        toggleCart();
    }
});
