// 菜單頁面 JavaScript - 餐廳點餐系統

class MenuPage {    constructor() {
        this.currentTable = null;
        this.menuItems = [];
        this.filteredItems = [];
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.currentCategory = 'all';
        this.searchQuery = '';
        
        this.init();
    }

    init() {
        console.log('🍽️ 菜單頁面初始化中...');
        
        // 檢查座號
        this.checkTableNumber();
        
        // 設定事件監聽
        this.setupEventListeners();
        
        // 載入菜單資料
        this.loadMenuData();
        
        // 更新購物車顯示
        this.updateCartDisplay();
        
        console.log('✅ 菜單頁面初始化完成');
    }

    checkTableNumber() {
        const savedTable = localStorage.getItem('currentTable');
        if (savedTable) {
            const tableData = JSON.parse(savedTable);
            this.currentTable = tableData.number;
            
            // 更新頁面顯示
            const tableDisplay = document.getElementById('currentTableNumber');
            if (tableDisplay) {
                tableDisplay.textContent = `座號：${this.currentTable}`;
            }
        } else {
            // 如果沒有座號，返回首頁
            console.warn('⚠️ 沒有找到座號資訊，返回首頁');
            window.location.href = 'index.html';
        }
    }

    setupEventListeners() {
        // 分類按鈕
        const categoryBtns = document.querySelectorAll('.category-btn');
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const category = e.target.getAttribute('data-category');
                this.filterByCategory(category);
            });
        });        // 搜尋功能
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }

        const searchClearBtn = document.getElementById('searchClearBtn');
        if (searchClearBtn) {
            searchClearBtn.addEventListener('click', () => {
                this.clearSearch();
            });        }
    }

    async loadMenuData() {
        try {
            console.log('📖 載入菜單資料...');
            
            // 顯示載入狀態
            const loadingState = document.getElementById('menuLoading');
            if (loadingState) {
                loadingState.classList.remove('d-none');
            }

            // 模擬載入延遲
            await this.delay(1000);

            // 載入示例菜單資料
            this.menuItems = this.getSampleMenuData();
            this.filteredItems = [...this.menuItems];

            // 渲染菜單
            this.renderMenuItems();

            console.log(`✅ 已載入 ${this.menuItems.length} 個菜品`);

        } catch (error) {
            console.error('❌ 菜單載入失敗:', error);
            this.showError('菜單載入失敗，請重新整理頁面');
        }
    }

    getSampleMenuData() {
        return [
            {
                id: 'main_001',
                name: '招牌牛肉麵',
                description: '精選牛肉配上香濃湯頭，搭配手工拉麵，是本店的招牌菜品',
                price: 180,
                category: '主食',
                image: 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=牛肉麵',
                available: true,
                tags: ['招牌', '辣'],
                spicyLevel: 2,
                isVegetarian: false
            },
            {
                id: 'main_002',
                name: '海鮮炒飯',
                description: '新鮮海鮮配上粒粒分明的炒飯，香氣四溢',
                price: 160,
                category: '主食',
                image: 'https://via.placeholder.com/300x200/FFA726/FFFFFF?text=海鮮炒飯',
                available: true,
                tags: ['人氣'],
                spicyLevel: 0,
                isVegetarian: false
            },
            {
                id: 'main_003',
                name: '素食咖哩飯',
                description: '香濃咖哩搭配新鮮蔬菜，素食者的最愛',
                price: 140,
                category: '主食',
                image: 'https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=咖哩飯',
                available: true,
                tags: ['素食', '健康'],
                spicyLevel: 1,
                isVegetarian: true
            },
            {
                id: 'drink_001',
                name: '珍珠奶茶',
                description: '濃郁奶茶配上Q彈珍珠，經典台灣味',
                price: 55,
                category: '飲品',
                image: 'https://via.placeholder.com/300x200/8D6E63/FFFFFF?text=珍珠奶茶',
                available: true,
                tags: ['經典'],
                spicyLevel: 0,
                isVegetarian: true
            },
            {
                id: 'drink_002',
                name: '檸檬蜂蜜茶',
                description: '清香檸檬配上天然蜂蜜，清爽解膩',
                price: 45,
                category: '飲品',
                image: 'https://via.placeholder.com/300x200/FFC107/FFFFFF?text=檸檬茶',
                available: true,
                tags: ['清爽', '健康'],
                spicyLevel: 0,
                isVegetarian: true
            },
            {
                id: 'dessert_001',
                name: '手工布丁',
                description: '綿密香甜的手工布丁，入口即化',
                price: 35,
                category: '甜點',
                image: 'https://via.placeholder.com/300x200/795548/FFFFFF?text=布丁',
                available: true,
                tags: ['手工', '甜品'],
                spicyLevel: 0,
                isVegetarian: true
            },
            {
                id: 'side_001',
                name: '涼拌小黃瓜',
                description: '清脆爽口的涼拌小黃瓜，開胃小菜',
                price: 25,
                category: '小菜',
                image: 'https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=小黃瓜',
                available: true,
                tags: ['清爽', '開胃'],
                spicyLevel: 0,
                isVegetarian: true
            },
            {
                id: 'side_002',
                name: '麻辣豆腐',
                description: '香辣可口的麻辣豆腐，下飯好選擇',
                price: 40,
                category: '小菜',
                image: 'https://via.placeholder.com/300x200/F44336/FFFFFF?text=麻辣豆腐',
                available: true,
                tags: ['辣', '下飯'],
                spicyLevel: 3,
                isVegetarian: true
            }
        ];
    }

    renderMenuItems() {
        const menuGrid = document.getElementById('menuGrid');
        const loadingState = document.getElementById('menuLoading');
        const emptyState = document.getElementById('emptyState');

        if (!menuGrid) return;

        // 隱藏載入狀態
        if (loadingState) {
            loadingState.classList.add('d-none');
        }

        // 清空容器
        menuGrid.innerHTML = '';

        if (this.filteredItems.length === 0) {
            // 顯示空狀態
            if (emptyState) {
                emptyState.classList.remove('d-none');
            }
            return;
        }

        // 隱藏空狀態
        if (emptyState) {
            emptyState.classList.add('d-none');
        }

        // 渲染菜品
        this.filteredItems.forEach(item => {
            const itemElement = this.createMenuItemElement(item);
            menuGrid.appendChild(itemElement);
        });
    }    createMenuItemElement(item) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'menu-item';
        itemDiv.setAttribute('data-item-id', item.id);

        // 建立標籤HTML
        const tagsHtml = item.tags.map(tag => {
            let tagClass = 'menu-tag';
            if (tag === '辣') tagClass += ' spicy';
            if (tag === '素食' || tag === '健康') tagClass += ' vegetarian';
            return `<span class="${tagClass}">${tag}</span>`;
        }).join('');        // 獲取購物車中此商品的數量
        const cartItem = this.cart.find(cartItem => cartItem.id === item.id);
        const currentQuantity = cartItem ? cartItem.quantity : 0;

        itemDiv.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="menu-item-image" loading="lazy">
            <div class="menu-item-content">
                <h3 class="menu-item-name">${item.name}</h3>
                <p class="menu-item-description">${item.description}</p>
                <div class="menu-item-footer">
                    <span class="menu-item-price">$${item.price}</span>
                    ${item.available ? '' : '<span class="text-muted">暫時售完</span>'}
                </div>
                <div class="menu-item-tags">
                    ${tagsHtml}
                </div>
                ${item.available ? `
                <div class="menu-item-controls">
                    <div class="quantity-controls">
                        <button class="qty-btn qty-minus" data-action="minus">-</button>
                        <span class="qty-display">${currentQuantity}</span>
                        <button class="qty-btn qty-plus" data-action="plus">+</button>
                    </div>
                </div>
                ` : ''}
            </div>
        `;        // 為數量控制按鈕添加事件監聽
        if (item.available) {
            const qtyMinus = itemDiv.querySelector('.qty-minus');
            const qtyPlus = itemDiv.querySelector('.qty-plus');

            qtyMinus.addEventListener('click', (e) => {
                e.stopPropagation();
                this.changeItemQuantity(itemDiv, -1);
            });

            qtyPlus.addEventListener('click', (e) => {
                e.stopPropagation();
                this.changeItemQuantity(itemDiv, 1);
            });
        }

        return itemDiv;
    }

    filterByCategory(category) {
        this.currentCategory = category;
        
        // 更新分類按鈕狀態
        const categoryBtns = document.querySelectorAll('.category-btn');
        categoryBtns.forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-category') === category) {
                btn.classList.add('active');
            }
        });

        // 過濾菜品
        this.applyFilters();
    }

    handleSearch(query) {
        this.searchQuery = query.trim();
        
        // 顯示/隱藏清除按鈕
        const clearBtn = document.getElementById('searchClearBtn');
        if (clearBtn) {
            if (this.searchQuery) {
                clearBtn.classList.remove('d-none');
            } else {
                clearBtn.classList.add('d-none');
            }
        }

        // 應用過濾
        this.applyFilters();
    }

    clearSearch() {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('searchClearBtn');
        
        if (searchInput) {
            searchInput.value = '';
        }
        if (clearBtn) {
            clearBtn.classList.add('d-none');
        }
        
        this.searchQuery = '';
        this.applyFilters();
    }

    applyFilters() {
        this.filteredItems = this.menuItems.filter(item => {
            // 分類過濾
            const categoryMatch = this.currentCategory === 'all' || item.category === this.currentCategory;
            
            // 搜尋過濾
            const searchMatch = !this.searchQuery || 
                item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                item.description.toLowerCase().includes(this.searchQuery.toLowerCase());
            
            return categoryMatch && searchMatch;
        });

        this.renderMenuItems();
    }    updateCartDisplay() {
        const cartCount = document.getElementById('cartCount');
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        if (cartCount) {
            cartCount.textContent = totalItems;
            if (totalItems > 0) {
                cartCount.classList.remove('d-none');
            } else {
                cartCount.classList.add('d-none');
            }
        }
    }

    showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (toast && toastMessage) {
            toastMessage.textContent = message;
            toast.classList.remove('d-none');
            
            // 3秒後自動隱藏
            setTimeout(() => {
                toast.classList.add('d-none');
            }, 3000);
        }
    }

    showError(message) {
        console.error('❌', message);
        this.showToast(message);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }    changeItemQuantity(itemElement, change) {
        const qtyDisplay = itemElement.querySelector('.qty-display');
        const currentQty = parseInt(qtyDisplay.textContent);
        const newQty = Math.max(0, Math.min(99, currentQty + change));
        
        const itemId = itemElement.getAttribute('data-item-id');
        const item = this.menuItems.find(i => i.id === itemId);
        
        if (!item) return;
        
        // 更新顯示
        qtyDisplay.textContent = newQty;
        
        if (newQty === 0) {
            // 從購物車中移除
            this.cart = this.cart.filter(cartItem => cartItem.id !== itemId);
            this.showToast(`${item.name} 已從購物車移除`);
        } else {
            // 加入或更新購物車
            this.addItemToCart(item, newQty);
            if (currentQty === 0) {
                this.showToast(`${item.name} 已加入購物車 (${newQty} 份)`);
            } else {
                this.showToast(`${item.name} 數量已調整為 ${newQty} 份`);
            }
        }
        
        // 儲存到本地存儲
        localStorage.setItem('cart', JSON.stringify(this.cart));
        
        // 更新購物車顯示
        this.updateCartDisplay();
    }

    addItemToCart(item, quantity = 1) {
        const existingItem = this.cart.find(cartItem => cartItem.id === item.id);
        
        if (existingItem) {
            existingItem.quantity = quantity;
        } else {
            this.cart.push({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: quantity,
                image: item.image
            });
        }
        
        // 儲存到本地存儲
        localStorage.setItem('cart', JSON.stringify(this.cart));
        
        // 更新購物車顯示
        this.updateCartDisplay();
    }
}

// 全域函數
function openCart() {
    // 跳轉到購物車頁面
    window.location.href = 'cart.html';
}

// 當頁面載入完成時初始化
document.addEventListener('DOMContentLoaded', () => {
    window.menuPage = new MenuPage();
});

// 錯誤處理
window.addEventListener('error', (event) => {
    console.error('菜單頁面錯誤:', event.error);
});

// 返回頂部功能
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset > 300;
    // 可以在這裡添加返回頂部按鈕的顯示邏輯
});
