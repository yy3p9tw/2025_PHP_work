// 菜單資料管理
class MenuManager {
    constructor() {
        this.menuItems = JSON.parse(localStorage.getItem('menuItems')) || [];
        this.initializeEventListeners();
        this.loadMenuItems();
    }

    // 初始化事件監聽器
    initializeEventListeners() {
        // 新增菜品按鈕
        document.getElementById('addMenuItem').addEventListener('click', () => {
            this.showModal();
        });

        // 搜尋功能
        document.getElementById('searchMenu').addEventListener('input', (e) => {
            this.filterMenuItems(e.target.value);
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
    }

    // 載入菜單項目
    loadMenuItems() {
        const tbody = document.getElementById('menuItems');
        tbody.innerHTML = '';

        this.menuItems.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.category}</td>
                <td>$${item.price}</td>
                <td>
                    <span class="status-badge ${item.status}">
                        ${item.status === 'available' ? '可供應' : '停售'}
                    </span>
                </td>
                <td>
                    <button onclick="menuManager.editMenuItem(${index})" class="btn-edit">編輯</button>
                    <button onclick="menuManager.deleteMenuItem(${index})" class="btn-delete">刪除</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // 過濾菜單項目    filterMenuItems(searchText, category = '') {
        const tbody = document.getElementById('menuItems');
        tbody.innerHTML = '';

        this.menuItems
            .filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(searchText.toLowerCase());
                const matchesCategory = !category || item.category === category;
                const isAvailable = item.status === 'available';  // 只顯示可供應的菜品
                return matchesSearch && matchesCategory && isAvailable;
            })
            .forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>$${item.price}</td>
                    <td>
                        <span class="status-badge ${item.status}">
                            ${item.status === 'available' ? '可供應' : '停售'}
                        </span>
                    </td>
                    <td>
                        <button onclick="menuManager.editMenuItem(${index})" class="btn-edit">編輯</button>
                        <button onclick="menuManager.deleteMenuItem(${index})" class="btn-delete">刪除</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
    }

    // 顯示新增/編輯對話框
    showModal(item = null) {
        const modal = document.getElementById('menuItemModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('menuItemForm');

        modalTitle.textContent = item ? '編輯菜品' : '新增菜品';
        
        // 填充表單
        document.getElementById('itemName').value = item ? item.name : '';
        document.getElementById('itemCategory').value = item ? item.category : '主食';
        document.getElementById('itemPrice').value = item ? item.price : '';
        document.getElementById('itemStatus').value = item ? item.status : 'available';

        // 儲存編輯的項目索引
        form.dataset.editIndex = item ? this.menuItems.indexOf(item) : -1;

        modal.style.display = 'block';
    }

    // 隱藏對話框
    hideModal() {
        const modal = document.getElementById('menuItemModal');
        modal.style.display = 'none';
    }

    // 儲存菜品
    saveMenuItem() {
        const form = document.getElementById('menuItemForm');
        const editIndex = parseInt(form.dataset.editIndex);

        const menuItem = {
            name: document.getElementById('itemName').value,
            category: document.getElementById('itemCategory').value,
            price: parseInt(document.getElementById('itemPrice').value),
            status: document.getElementById('itemStatus').value
        };

        if (editIndex >= 0) {
            // 編輯現有項目
            this.menuItems[editIndex] = menuItem;
        } else {
            // 新增項目
            this.menuItems.push(menuItem);
        }

        // 儲存到 localStorage
        localStorage.setItem('menuItems', JSON.stringify(this.menuItems));

        // 重新載入列表
        this.loadMenuItems();
        this.hideModal();
    }

    // 編輯菜品
    editMenuItem(index) {
        const item = this.menuItems[index];
        this.showModal(item);
    }

    // 刪除菜品
    deleteMenuItem(index) {
        if (confirm('確定要刪除這個菜品嗎？')) {
            this.menuItems.splice(index, 1);
            localStorage.setItem('menuItems', JSON.stringify(this.menuItems));
            this.loadMenuItems();
        }
    }
}

// 初始化菜單管理器
const menuManager = new MenuManager();
