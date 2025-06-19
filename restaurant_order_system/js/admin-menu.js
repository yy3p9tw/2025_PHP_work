// 菜單資料管理
class MenuManager {
    constructor() {
        this.menuItems = [];
        this.filteredItems = [];
        this.currentEditId = null;
        this.currentImageData = null; // 新增當前圖片資料
        this.initializeData();
        this.initializeEventListeners();
        this.loadMenuItems();
    }

    // 初始化資料 - 從顧客端載入現有菜品或使用預設資料
    initializeData() {        // 預設菜單資料（與顧客端同步）
        const defaultMenu = [
            { id: 1, name: '牛肉麵', price: 180, category: 'main', image: '', status: 'available' },
            { id: 2, name: '炸雞排', price: 120, category: 'main', image: '', status: 'available' },
            { id: 3, name: '蔥抓餅', price: 40, category: 'appetizer', image: '', status: 'available' },
            { id: 4, name: '小籠包', price: 80, category: 'appetizer', image: '', status: 'available' },
            { id: 5, name: '玉米濃湯', price: 60, category: 'soup', image: '', status: 'available' },
            { id: 6, name: '紫菜蛋花湯', price: 50, category: 'soup', image: '', status: 'available' },
            { id: 7, name: '紅豆湯', price: 45, category: 'dessert', image: '', status: 'available' },
            { id: 8, name: '芒果冰', price: 90, category: 'dessert', image: '', status: 'available' },
            { id: 9, name: '珍珠奶茶', price: 55, category: 'drink', image: '', status: 'available' },
            { id: 10, name: '新鮮果汁', price: 65, category: 'drink', image: '', status: 'available' }
        ];

        // 檢查是否有儲存的菜單資料
        const savedMenu = localStorage.getItem('restaurantMenuItems');
        if (savedMenu) {            try {
                this.menuItems = JSON.parse(savedMenu);
                // 確保每個項目都有 id、status 和 image 屬性，並處理舊的 emoji 格式
                this.menuItems = this.menuItems.map((item, index) => ({
                    id: item.id || index + 1,
                    name: item.name,
                    price: item.price,
                    category: item.category,
                    image: item.image || item.emoji || '', // 如果有舊的 emoji 就轉換，否則為空
                    status: item.status || 'available'
                }));
            } catch (error) {
                console.error('載入儲存的菜單資料失敗:', error);
                this.menuItems = defaultMenu;
            }
        } else {
            this.menuItems = defaultMenu;
            this.saveMenuItems();
        }
    }

    // 儲存菜單資料到 localStorage
    saveMenuItems() {
        localStorage.setItem('restaurantMenuItems', JSON.stringify(this.menuItems));
        // 同時儲存給顧客端使用的格式
        this.syncToCustomerMenu();
    }

    // 同步資料到顧客端菜單格式
    syncToCustomerMenu() {        // 只同步可供應的菜品到顧客端
        const customerMenu = this.menuItems
            .filter(item => item.status === 'available')
            .map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                category: item.category,
                image: item.image
            }));
        
        localStorage.setItem('customerMenuItems', JSON.stringify(customerMenu));
    }

    // 獲取下一個可用的 ID
    getNextId() {
        const maxId = this.menuItems.length > 0 ? Math.max(...this.menuItems.map(item => item.id)) : 0;
        return maxId + 1;
    }    // 初始化事件監聽器
    initializeEventListeners() {
        // 新增菜品按鈕
        document.getElementById('addMenuItem').addEventListener('click', () => {
            this.showModal();
        });

        // 搜尋功能
        document.getElementById('searchMenu').addEventListener('input', (e) => {
            this.filterMenuItems(e.target.value, document.getElementById('categoryFilter').value);
        });

        // 分類過濾
        document.getElementById('categoryFilter').addEventListener('change', (e) => {
            this.filterMenuItems(document.getElementById('searchMenu').value, e.target.value);
        });

        // 表單提交
        document.getElementById('menuItemForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveMenuItem();
        });

        // 取消按鈕
        document.getElementById('cancelEdit').addEventListener('click', () => {
            this.hideModal();
        });

        // 關閉對話框（點擊背景）
        document.getElementById('menuItemModal').addEventListener('click', (e) => {
            if (e.target.id === 'menuItemModal') {
                this.hideModal();
            }
        });

        // 圖片上傳處理
        this.initializeImageUpload();
    }

    // 初始化圖片上傳功能
    initializeImageUpload() {
        const fileInput = document.getElementById('itemImage');
        const preview = document.getElementById('imagePreview');

        // 檔案選擇事件
        fileInput.addEventListener('change', (e) => {
            this.handleImageUpload(e.target.files[0]);
        });

        // 拖拽上傳
        preview.addEventListener('dragover', (e) => {
            e.preventDefault();
            preview.classList.add('drag-over');
        });

        preview.addEventListener('dragleave', (e) => {
            e.preventDefault();
            preview.classList.remove('drag-over');
        });

        preview.addEventListener('drop', (e) => {
            e.preventDefault();
            preview.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleImageUpload(files[0]);
                fileInput.files = files;
            }
        });
    }

    // 處理圖片上傳
    handleImageUpload(file) {
        if (!file) return;

        // 檢查檔案類型
        if (!file.type.startsWith('image/')) {
            alert('請選擇圖片檔案');
            return;
        }

        // 檢查檔案大小 (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('圖片檔案不能超過 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.currentImageData = e.target.result;
            this.showImagePreview(e.target.result, file);
        };
        reader.readAsDataURL(file);
    }

    // 顯示圖片預覽
    showImagePreview(imageData, file) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = `
            <img src="${imageData}" alt="預覽圖片" class="preview-image">
            <div class="preview-info">
                檔案名稱: ${file.name}<br>
                檔案大小: ${(file.size / 1024).toFixed(1)} KB
            </div>
            <button type="button" class="remove-image" onclick="menuManager.removeImage()">移除圖片</button>
        `;
    }

    // 移除圖片
    removeImage() {
        this.currentImageData = null;
        document.getElementById('itemImage').value = '';
        document.getElementById('imagePreview').innerHTML = '<div class="preview-placeholder">選擇圖片文件</div>';
    }

    // 載入菜單項目
    loadMenuItems() {
        this.filteredItems = [...this.menuItems];
        this.renderMenuItems();
    }

    // 渲染菜單項目
    renderMenuItems() {
        const tbody = document.getElementById('menuItems');
        tbody.innerHTML = '';        this.filteredItems.forEach((item, filteredIndex) => {
            const originalIndex = this.menuItems.findIndex(menuItem => menuItem.id === item.id);
            const tr = document.createElement('tr');
            
            // 處理圖片顯示
            const imageHtml = item.image ? 
                `<img src="${item.image}" alt="${item.name}" class="menu-item-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iNCIgZmlsbD0iI0Y1RjVGNSIvPgo8cGF0aCBkPSJNMTIgMTZIMjhWMjRIMTJWMTZaIiBzdHJva2U9IiM5OTk5OTkiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPgo8Y2lyY2xlIGN4PSIxNiIgY3k9IjIwIiByPSIyIiBmaWxsPSIjOTk5OTk5Ii8+Cjxwb2x5bGluZSBwb2ludHM9IjE2LDI0IDIwLDIwIDI4LDI4IDEyLDI4IiBzdHJva2U9IiM5OTk5OTkiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPgo8L3N2Zz4K';">` :
                `<div class="menu-item-placeholder">無圖片</div>`;
            
            tr.innerHTML = `
                <td>${filteredIndex + 1}</td>
                <td>${item.name}</td>
                <td>${this.getCategoryName(item.category)}</td>
                <td>NT$ ${item.price}</td>
                <td>
                    <span class="status-badge ${item.status}">
                        ${item.status === 'available' ? '可供應' : '停售'}
                    </span>
                </td>
                <td class="image-cell">${imageHtml}</td>
                <td>
                    <button onclick="menuManager.editMenuItem(${item.id})" class="btn-edit">編輯</button>
                    <button onclick="menuManager.toggleStatus(${item.id})" class="btn-toggle">
                        ${item.status === 'available' ? '停售' : '上架'}
                    </button>
                    <button onclick="menuManager.deleteMenuItem(${item.id})" class="btn-delete">刪除</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // 如果沒有項目，顯示提示訊息
        if (this.filteredItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">沒有找到符合條件的菜品</td></tr>';
        }
    }

    // 獲取分類中文名稱
    getCategoryName(category) {
        const categoryMap = {
            'main': '主食',
            'appetizer': '開胃菜',
            'soup': '湯品',
            'dessert': '甜點',
            'drink': '飲料'
        };
        return categoryMap[category] || category;
    }    // 過濾菜單項目
    filterMenuItems(searchText = '', category = '') {
        this.filteredItems = this.menuItems.filter(item => {
            const matchesSearch = item.name.toLowerCase().includes(searchText.toLowerCase());
            const matchesCategory = !category || item.category === category;
            return matchesSearch && matchesCategory;
        });
        
        this.renderMenuItems();
    }

    // 顯示新增/編輯對話框
    showModal(item = null) {
        const modal = document.getElementById('menuItemModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('menuItemForm');        modalTitle.textContent = item ? '編輯菜品' : '新增菜品';
        
        // 填充表單
        document.getElementById('itemName').value = item ? item.name : '';
        document.getElementById('itemCategory').value = item ? item.category : 'main';
        document.getElementById('itemPrice').value = item ? item.price : '';
        document.getElementById('itemStatus').value = item ? item.status : 'available';

        // 處理圖片顯示
        this.currentImageData = item ? item.image : null;
        const preview = document.getElementById('imagePreview');
        
        if (item && item.image) {
            preview.innerHTML = `
                <img src="${item.image}" alt="目前圖片" class="preview-image">
                <div class="preview-info">目前圖片</div>
                <button type="button" class="remove-image" onclick="menuManager.removeImage()">移除圖片</button>
            `;
        } else {
            preview.innerHTML = '<div class="preview-placeholder">選擇圖片文件</div>';
        }

        // 清空檔案輸入框
        document.getElementById('itemImage').value = '';

        // 儲存編輯的項目 ID
        form.dataset.editId = item ? item.id : '';

        modal.style.display = 'block';
        
        // 聚焦到名稱輸入框
        setTimeout(() => {
            document.getElementById('itemName').focus();
        }, 100);
    }    // 隱藏對話框
    hideModal() {
        const modal = document.getElementById('menuItemModal');
        modal.style.display = 'none';
        
        // 清空表單
        document.getElementById('menuItemForm').reset();
        
        // 清空圖片預覽
        this.currentImageData = null;
        document.getElementById('imagePreview').innerHTML = '<div class="preview-placeholder">選擇圖片文件</div>';
    }

    // 儲存菜品
    saveMenuItem() {
        const form = document.getElementById('menuItemForm');
        const editId = form.dataset.editId;        // 驗證表單
        const name = document.getElementById('itemName').value.trim();
        const price = document.getElementById('itemPrice').value;

        if (!name) {
            alert('請輸入菜品名稱');
            document.getElementById('itemName').focus();
            return;
        }

        if (!price || price <= 0) {
            alert('請輸入有效的價格');
            document.getElementById('itemPrice').focus();
            return;
        }

        // 檢查菜品名稱是否重複（排除自己）
        const existingItem = this.menuItems.find(item => 
            item.name === name && (!editId || item.id != editId)
        );
        
        if (existingItem) {
            alert('菜品名稱已存在，請使用不同的名稱');
            document.getElementById('itemName').focus();
            return;
        }        const menuItem = {
            name: name,
            category: document.getElementById('itemCategory').value,
            price: parseInt(price),
            image: this.currentImageData || '',
            status: document.getElementById('itemStatus').value
        };

        if (editId) {
            // 編輯現有項目
            const index = this.menuItems.findIndex(item => item.id == editId);
            if (index !== -1) {
                this.menuItems[index] = { ...menuItem, id: parseInt(editId) };
            }
        } else {
            // 新增項目
            menuItem.id = this.getNextId();
            this.menuItems.push(menuItem);
        }

        // 儲存到 localStorage 並同步
        this.saveMenuItems();

        // 重新載入列表
        this.loadMenuItems();
        this.hideModal();
        
        // 顯示成功訊息
        const message = editId ? '菜品已更新！' : '菜品已新增！';
        this.showNotification(message, 'success');
    }

    // 編輯菜品
    editMenuItem(id) {
        const item = this.menuItems.find(item => item.id === id);
        if (item) {
            this.showModal(item);
        } else {
            alert('找不到該菜品');
        }
    }

    // 切換菜品狀態（上架/停售）
    toggleStatus(id) {
        const item = this.menuItems.find(item => item.id === id);
        if (!item) {
            alert('找不到該菜品');
            return;
        }

        const newStatus = item.status === 'available' ? 'unavailable' : 'available';
        const action = newStatus === 'available' ? '上架' : '停售';
        
        if (confirm(`確定要${action}「${item.name}」嗎？`)) {
            item.status = newStatus;
            this.saveMenuItems();
            this.loadMenuItems();
            this.showNotification(`「${item.name}」已${action}`, 'success');
        }
    }

    // 刪除菜品
    deleteMenuItem(id) {
        const item = this.menuItems.find(item => item.id === id);
        if (!item) {
            alert('找不到該菜品');
            return;
        }

        if (confirm(`確定要刪除「${item.name}」嗎？\n\n注意：刪除後無法復原！`)) {
            this.menuItems = this.menuItems.filter(item => item.id !== id);
            this.saveMenuItems();
            this.loadMenuItems();
            this.showNotification(`「${item.name}」已刪除`, 'success');
        }
    }

    // 顯示通知訊息
    showNotification(message, type = 'info') {
        // 創建通知元素
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        `;

        document.body.appendChild(notification);

        // 顯示動畫
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 100);

        // 自動隱藏
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);        }, 3000);
    }
}

// 初始化菜單管理器
const menuManager = new MenuManager();
