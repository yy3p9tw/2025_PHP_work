/**
 * 系統設定管理 JavaScript
 * 處理餐廳資訊、用戶管理、系統配置等功能
 */

class AdminSettings {
    constructor() {
        this.currentTab = 'restaurant-info';
        this.settings = this.loadSettings();
        this.users = this.loadUsers();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadRestaurantInfo();
        this.loadSystemConfig();
        this.loadUsers();
        this.generateTableGrid();
        this.loadBackupHistory();
    }

    setupEventListeners() {
        // 標籤切換
        document.querySelectorAll('.settings-menu a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = link.getAttribute('data-tab');
                this.switchTab(tabId);
            });
        });

        // 表單提交
        document.getElementById('restaurantInfoForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveRestaurantInfo();
        });

        document.getElementById('systemConfigForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveSystemConfig();
        });

        document.getElementById('addUserForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.addUser();
        });

        // 檔案上傳
        document.getElementById('restoreFile').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('restoreFileName').textContent = file.name;
                document.getElementById('restoreBtn').disabled = false;
            }
        });

        // 桌號設定
        document.getElementById('maxTableNumber').addEventListener('change', () => {
            this.generateTableGrid();
        });

        document.getElementById('tablePrefix').addEventListener('input', () => {
            this.generateTableGrid();
        });
    }

    switchTab(tabId) {
        // 移除所有 active 類別
        document.querySelectorAll('.settings-menu a').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelectorAll('.settings-section').forEach(section => {
            section.classList.remove('active');
        });

        // 添加 active 類別
        document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(tabId).classList.add('active');

        this.currentTab = tabId;
    }

    // 餐廳資訊管理
    loadRestaurantInfo() {
        const info = this.settings.restaurantInfo || {};
        document.getElementById('restaurantName').value = info.name || '';
        document.getElementById('restaurantPhone').value = info.phone || '';
        document.getElementById('restaurantAddress').value = info.address || '';
        document.getElementById('businessHours').value = info.businessHours || '';
        document.getElementById('restaurantDescription').value = info.description || '';
    }

    saveRestaurantInfo() {
        const formData = new FormData(document.getElementById('restaurantInfoForm'));
        const info = {
            name: formData.get('restaurantName'),
            phone: formData.get('restaurantPhone'),
            address: formData.get('restaurantAddress'),
            businessHours: formData.get('businessHours'),
            description: formData.get('restaurantDescription')
        };

        this.settings.restaurantInfo = info;
        this.saveSettings();
        this.showNotification('餐廳資訊已儲存', 'success');
    }

    // 系統配置管理
    loadSystemConfig() {
        const config = this.settings.systemConfig || {};
        
        document.getElementById('autoAcceptOrders').checked = config.autoAcceptOrders || false;
        document.getElementById('orderTimeout').value = config.orderTimeout || 30;
        document.getElementById('theme').value = config.theme || 'default';
        document.getElementById('language').value = config.language || 'zh-TW';
        document.getElementById('soundNotification').checked = config.soundNotification !== false;
        document.getElementById('emailNotification').checked = config.emailNotification || false;
    }

    saveSystemConfig() {
        const formData = new FormData(document.getElementById('systemConfigForm'));
        const config = {
            autoAcceptOrders: formData.get('autoAcceptOrders') === 'on',
            orderTimeout: parseInt(formData.get('orderTimeout')),
            theme: formData.get('theme'),
            language: formData.get('language'),
            soundNotification: formData.get('soundNotification') === 'on',
            emailNotification: formData.get('emailNotification') === 'on'
        };

        this.settings.systemConfig = config;
        this.saveSettings();
        this.applyTheme(config.theme);
        this.showNotification('系統配置已儲存', 'success');
    }

    applyTheme(theme) {
        document.body.className = document.body.className.replace(/theme-\w+/g, '');
        if (theme !== 'default') {
            document.body.classList.add(`theme-${theme}`);
        }
    }

    resetSystemConfig() {
        if (confirm('確定要恢復系統預設配置嗎？')) {
            this.settings.systemConfig = {};
            this.saveSettings();
            this.loadSystemConfig();
            this.showNotification('已恢復預設配置', 'info');
        }
    }

    // 用戶管理
    loadUsers() {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';

        this.users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.username}</td>
                <td><span class="role-badge ${user.role}">${this.getRoleText(user.role)}</span></td>
                <td><span class="status-badge ${user.status}">${user.status === 'active' ? '啟用' : '停用'}</span></td>
                <td>${user.lastLogin || '從未登入'}</td>
                <td>
                    <button class="btn-icon" onclick="adminSettings.editUser('${user.username}')" title="編輯">✏️</button>
                    <button class="btn-icon" onclick="adminSettings.toggleUserStatus('${user.username}')" title="${user.status === 'active' ? '停用' : '啟用'}">
                        ${user.status === 'active' ? '🔒' : '🔓'}
                    </button>
                    ${user.username !== 'admin' ? `<button class="btn-icon" onclick="adminSettings.deleteUser('${user.username}')" title="刪除">🗑️</button>` : ''}
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    getRoleText(role) {
        const roleMap = {
            'admin': '管理員',
            'manager': '經理',
            'staff': '員工'
        };
        return roleMap[role] || role;
    }

    showAddUserModal() {
        document.getElementById('addUserModal').style.display = 'flex';
    }

    closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    addUser() {
        const formData = new FormData(document.getElementById('addUserForm'));
        const newUser = {
            username: formData.get('newUsername'),
            email: formData.get('newUserEmail'),
            password: formData.get('newUserPassword'), // 實際應用中應該加密
            role: formData.get('newUserRole'),
            status: 'active',
            lastLogin: null,
            createdAt: new Date().toISOString()
        };

        // 檢查用戶名是否已存在
        if (this.users.find(user => user.username === newUser.username)) {
            this.showNotification('用戶名已存在', 'error');
            return;
        }

        this.users.push(newUser);
        this.saveUsers();
        this.loadUsers();
        this.closeModal('addUserModal');
        document.getElementById('addUserForm').reset();
        this.showNotification('用戶新增成功', 'success');
    }

    editUser(username) {
        const user = this.users.find(u => u.username === username);
        if (!user) return;

        // 這裡可以實作編輯用戶的模態框
        console.log('編輯用戶:', user);
        this.showNotification('編輯功能開發中', 'info');
    }

    toggleUserStatus(username) {
        const user = this.users.find(u => u.username === username);
        if (!user) return;

        if (username === 'admin') {
            this.showNotification('無法停用管理員帳號', 'error');
            return;
        }

        user.status = user.status === 'active' ? 'inactive' : 'active';
        this.saveUsers();
        this.loadUsers();
        this.showNotification(`用戶 ${username} 已${user.status === 'active' ? '啟用' : '停用'}`, 'success');
    }

    deleteUser(username) {
        if (confirm(`確定要刪除用戶 "${username}" 嗎？`)) {
            this.users = this.users.filter(u => u.username !== username);
            this.saveUsers();
            this.loadUsers();
            this.showNotification('用戶已刪除', 'success');
        }
    }

    // 桌號管理
    generateTableGrid() {
        const maxTable = parseInt(document.getElementById('maxTableNumber').value) || 50;
        const prefix = document.getElementById('tablePrefix').value || '';
        const grid = document.getElementById('tableGrid');
        
        grid.innerHTML = '';
        
        for (let i = 1; i <= maxTable; i++) {
            const tableNumber = prefix + i;
            const tableDiv = document.createElement('div');
            tableDiv.className = 'table-item available';
            tableDiv.innerHTML = `
                <span class="table-number">${tableNumber}</span>
                <span class="table-status">可用</span>
            `;
            
            tableDiv.addEventListener('click', () => {
                this.toggleTableStatus(tableDiv, tableNumber);
            });
            
            grid.appendChild(tableDiv);
        }
    }

    toggleTableStatus(tableDiv, tableNumber) {
        const isAvailable = tableDiv.classList.contains('available');
        
        if (isAvailable) {
            tableDiv.classList.remove('available');
            tableDiv.classList.add('occupied');
            tableDiv.querySelector('.table-status').textContent = '使用中';
        } else {
            tableDiv.classList.remove('occupied');
            tableDiv.classList.add('available');
            tableDiv.querySelector('.table-status').textContent = '可用';
        }
    }

    saveTableConfig() {
        const maxTable = parseInt(document.getElementById('maxTableNumber').value);
        const prefix = document.getElementById('tablePrefix').value;
        
        this.settings.tableConfig = {
            maxTableNumber: maxTable,
            tablePrefix: prefix
        };
        
        this.saveSettings();
        this.showNotification('桌號設定已儲存', 'success');
    }

    resetAllTables() {
        if (confirm('確定要重置所有桌號狀態嗎？')) {
            this.generateTableGrid();
            this.showNotification('所有桌號已重置', 'info');
        }
    }

    // 備份還原
    createBackup() {
        const backupData = {
            timestamp: new Date().toISOString(),
            version: '1.0',
            data: {
                menu: this.loadMenuData(),
                orders: this.loadOrderData(),
                settings: this.settings,
                users: this.users
            }
        };

        const backupItem = {
            id: Date.now(),
            date: new Date().toLocaleString('zh-TW'),
            size: this.calculateSize(backupData),
            data: backupData
        };

        const backups = JSON.parse(localStorage.getItem('backupHistory') || '[]');
        backups.unshift(backupItem);
        
        // 保留最近 10 次備份
        if (backups.length > 10) {
            backups.splice(10);
        }
        
        localStorage.setItem('backupHistory', JSON.stringify(backups));
        this.loadBackupHistory();
        this.showNotification('備份建立成功', 'success');
    }

    downloadBackup() {
        const backups = JSON.parse(localStorage.getItem('backupHistory') || '[]');
        if (backups.length === 0) {
            this.showNotification('沒有可下載的備份', 'warning');
            return;
        }

        const latestBackup = backups[0];
        const dataStr = JSON.stringify(latestBackup.data, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `restaurant_backup_${new Date().toISOString().split('T')[0]}.json`;
        link.click();
    }

    restoreData() {
        const fileInput = document.getElementById('restoreFile');
        const file = fileInput.files[0];
        
        if (!file) {
            this.showNotification('請選擇備份檔案', 'warning');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const backupData = JSON.parse(e.target.result);
                
                if (confirm('還原操作將覆蓋現有資料，確定要繼續嗎？')) {
                    // 還原資料
                    if (backupData.data) {
                        if (backupData.data.settings) {
                            this.settings = backupData.data.settings;
                            this.saveSettings();
                        }
                        if (backupData.data.users) {
                            this.users = backupData.data.users;
                            this.saveUsers();
                        }
                        // 這裡可以還原菜單和訂單資料
                    }
                    
                    this.showNotification('資料還原成功', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } catch (error) {
                this.showNotification('備份檔案格式錯誤', 'error');
            }
        };
        
        reader.readAsText(file);
    }

    loadBackupHistory() {
        const backups = JSON.parse(localStorage.getItem('backupHistory') || '[]');
        const container = document.getElementById('backupHistory');
        
        container.innerHTML = '';
        
        if (backups.length === 0) {
            container.innerHTML = '<p class="no-data">暫無備份記錄</p>';
            return;
        }

        backups.forEach(backup => {
            const item = document.createElement('div');
            item.className = 'backup-item';
            item.innerHTML = `
                <span class="backup-date">${backup.date}</span>
                <span class="backup-size">${backup.size}</span>
                <div class="backup-actions">
                    <button class="btn-icon" onclick="adminSettings.downloadSpecificBackup(${backup.id})" title="下載">📥</button>
                    <button class="btn-icon" onclick="adminSettings.deleteBackup(${backup.id})" title="刪除">🗑️</button>
                </div>
            `;
            container.appendChild(item);
        });
    }

    downloadSpecificBackup(backupId) {
        const backups = JSON.parse(localStorage.getItem('backupHistory') || '[]');
        const backup = backups.find(b => b.id === backupId);
        
        if (!backup) return;

        const dataStr = JSON.stringify(backup.data, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `restaurant_backup_${backup.date.replace(/[:/\s]/g, '_')}.json`;
        link.click();
    }

    deleteBackup(backupId) {
        if (confirm('確定要刪除此備份嗎？')) {
            let backups = JSON.parse(localStorage.getItem('backupHistory') || '[]');
            backups = backups.filter(b => b.id !== backupId);
            localStorage.setItem('backupHistory', JSON.stringify(backups));
            this.loadBackupHistory();
            this.showNotification('備份已刪除', 'success');
        }
    }

    // 安全設定
    saveSecuritySettings() {
        const securitySettings = {
            minPasswordLength: parseInt(document.getElementById('minPasswordLength').value),
            requireUppercase: document.getElementById('requireUppercase').checked,
            requireNumbers: document.getElementById('requireNumbers').checked,
            requireSymbols: document.getElementById('requireSymbols').checked,
            maxLoginAttempts: parseInt(document.getElementById('maxLoginAttempts').value),
            lockoutDuration: parseInt(document.getElementById('lockoutDuration').value),
            sessionTimeout: parseInt(document.getElementById('sessionTimeout').value)
        };

        this.settings.securitySettings = securitySettings;
        this.saveSettings();
        this.showNotification('安全設定已儲存', 'success');
    }

    resetSecuritySettings() {
        if (confirm('確定要恢復安全設定預設值嗎？')) {
            this.settings.securitySettings = {
                minPasswordLength: 8,
                requireUppercase: true,
                requireNumbers: true,
                requireSymbols: false,
                maxLoginAttempts: 5,
                lockoutDuration: 15,
                sessionTimeout: 60
            };
            this.saveSettings();
            this.showNotification('安全設定已重置', 'info');
        }
    }

    // 工具方法
    loadSettings() {
        return JSON.parse(localStorage.getItem('restaurantSettings') || '{}');
    }

    saveSettings() {
        localStorage.setItem('restaurantSettings', JSON.stringify(this.settings));
    }

    loadUsers() {
        const defaultUsers = [{
            username: 'admin',
            email: 'admin@restaurant.com',
            role: 'admin',
            status: 'active',
            lastLogin: '2024-01-15 14:30',
            createdAt: '2024-01-01T00:00:00.000Z'
        }];
        
        return JSON.parse(localStorage.getItem('restaurantUsers') || JSON.stringify(defaultUsers));
    }

    saveUsers() {
        localStorage.setItem('restaurantUsers', JSON.stringify(this.users));
    }

    loadMenuData() {
        return JSON.parse(localStorage.getItem('menuItems') || '[]');
    }

    loadOrderData() {
        return JSON.parse(localStorage.getItem('orderHistory') || '[]');
    }

    calculateSize(data) {
        const sizeInBytes = new Blob([JSON.stringify(data)]).size;
        if (sizeInBytes < 1024) {
            return sizeInBytes + ' B';
        } else if (sizeInBytes < 1024 * 1024) {
            return Math.round(sizeInBytes / 1024) + ' KB';
        } else {
            return Math.round(sizeInBytes / (1024 * 1024)) + ' MB';
        }
    }

    resetForm(formId) {
        if (confirm('確定要重置表單嗎？')) {
            document.getElementById(formId).reset();
            if (formId === 'restaurantInfoForm') {
                this.loadRestaurantInfo();
            }
        }
    }

    showNotification(message, type = 'info') {
        // 創建通知元素
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;

        // 添加到頁面
        document.body.appendChild(notification);

        // 3秒後自動移除
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }
}

// 初始化
let adminSettings;
document.addEventListener('DOMContentLoaded', () => {
    adminSettings = new AdminSettings();
});

// 模態框點擊外部關閉
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
