/**
 * 權限管理 JavaScript
 * 處理角色管理、權限分配、權限記錄等功能
 */

class PermissionManager {
    constructor() {
        this.roles = this.loadRoles();
        this.users = this.loadUsers();
        this.permissions = this.loadPermissions();
        this.permissionLogs = this.loadPermissionLogs();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadRoleCards();
        this.loadUserPermissions();
        this.loadPermissionLogs();
        this.updatePermissionMatrix();
    }

    setupEventListeners() {
        // 表單提交
        document.getElementById('addRoleForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.addRole();
        });

        // 篩選器
        document.getElementById('logUserFilter').addEventListener('change', () => {
            this.filterLogs();
        });
        
        document.getElementById('logActionFilter').addEventListener('change', () => {
            this.filterLogs();
        });
    }

    // 角色管理
    loadRoles() {
        const defaultRoles = [
            {
                id: 'admin',
                name: '管理員',
                description: '系統最高權限，可以管理所有功能和用戶',
                permissions: ['*'], // 所有權限
                userCount: 1,
                isSystem: true
            },
            {
                id: 'manager',
                name: '經理',
                description: '餐廳經理，可以管理菜單、訂單和查看報表',
                permissions: [
                    'menu_view', 'menu_create', 'menu_edit', 'menu_delete', 'menu_category',
                    'order_view', 'order_process', 'order_cancel', 'order_refund',
                    'report_sales', 'report_revenue', 'report_dashboard'
                ],
                userCount: 0,
                isSystem: true
            },
            {
                id: 'staff',
                name: '員工',
                description: '一般員工，可以處理訂單和查看基本資訊',
                permissions: [
                    'menu_view',
                    'order_view', 'order_process'
                ],
                userCount: 0,
                isSystem: true
            }
        ];

        return JSON.parse(localStorage.getItem('userRoles') || JSON.stringify(defaultRoles));
    }

    saveRoles() {
        localStorage.setItem('userRoles', JSON.stringify(this.roles));
    }

    loadRoleCards() {
        const rolesGrid = document.querySelector('.roles-grid');
        rolesGrid.innerHTML = '';

        this.roles.forEach(role => {
            const roleCard = document.createElement('div');
            roleCard.className = 'role-card';
            roleCard.setAttribute('data-role', role.id);

            const permissionTags = role.permissions.includes('*') 
                ? '<span class="permission-tag">所有權限</span>'
                : role.permissions.slice(0, 3).map(p => `<span class="permission-tag">${this.getPermissionName(p)}</span>`).join('');

            roleCard.innerHTML = `
                <div class="role-header">
                    <h3>${role.name}</h3>
                    <span class="role-count">${role.userCount} 位用戶</span>
                </div>
                <div class="role-description">
                    ${role.description}
                </div>
                <div class="role-permissions">
                    ${permissionTags}
                    ${role.permissions.length > 3 && !role.permissions.includes('*') ? `<span class="permission-more">+${role.permissions.length - 3}</span>` : ''}
                </div>
                <div class="role-actions">
                    <button class="btn btn-secondary btn-sm" onclick="permissionManager.editRole('${role.id}')">編輯</button>
                    <button class="btn btn-outline btn-sm" onclick="permissionManager.viewRolePermissions('${role.id}')">查看權限</button>
                    ${!role.isSystem ? `<button class="btn btn-danger btn-sm" onclick="permissionManager.deleteRole('${role.id}')">刪除</button>` : ''}
                </div>
            `;

            rolesGrid.appendChild(roleCard);
        });
    }

    getPermissionName(permission) {
        const permissionNames = {
            'menu_view': '菜單查看',
            'menu_create': '新增菜品',
            'menu_edit': '編輯菜品',
            'menu_delete': '刪除菜品',
            'menu_category': '分類管理',
            'order_view': '訂單查看',
            'order_process': '處理訂單',
            'order_cancel': '取消訂單',
            'order_refund': '退款處理',
            'report_sales': '銷售報表',
            'report_revenue': '營收分析',
            'report_export': '匯出報表',
            'report_dashboard': '儀表板',
            'system_config': '系統設定',
            'user_manage': '用戶管理',
            'role_manage': '角色管理',
            'backup_restore': '備份還原'
        };
        return permissionNames[permission] || permission;
    }

    showAddRoleModal() {
        document.getElementById('addRoleModal').style.display = 'flex';
    }

    closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        if (modalId === 'addRoleModal') {
            document.getElementById('addRoleForm').reset();
        }
    }

    addRole() {
        const formData = new FormData(document.getElementById('addRoleForm'));
        const roleName = formData.get('roleName');
        const roleDescription = formData.get('roleDescription');
        
        // 收集選中的權限
        const selectedPermissions = [];
        document.querySelectorAll('#addRoleForm .permissions-checkboxes input:checked').forEach(cb => {
            selectedPermissions.push(cb.value);
        });

        // 檢查角色名稱是否已存在
        if (this.roles.find(role => role.name === roleName)) {
            this.showNotification('角色名稱已存在', 'error');
            return;
        }

        const newRole = {
            id: this.generateRoleId(roleName),
            name: roleName,
            description: roleDescription,
            permissions: selectedPermissions,
            userCount: 0,
            isSystem: false,
            createdAt: new Date().toISOString()
        };

        this.roles.push(newRole);
        this.saveRoles();
        this.loadRoleCards();
        this.closeModal('addRoleModal');
        
        this.logPermissionChange('admin', 'role_create', newRole.id, `新增角色: ${roleName}`);
        this.showNotification('角色新增成功', 'success');
    }

    generateRoleId(name) {
        return name.toLowerCase().replace(/\s+/g, '_') + '_' + Date.now();
    }

    editRole(roleId) {
        const role = this.roles.find(r => r.id === roleId);
        if (!role) return;

        // 這裡可以實作編輯角色的功能
        console.log('編輯角色:', role);
        this.showNotification('編輯功能開發中', 'info');
    }

    deleteRole(roleId) {
        const role = this.roles.find(r => r.id === roleId);
        if (!role) return;

        if (role.userCount > 0) {
            this.showNotification('無法刪除，該角色仍有用戶使用', 'error');
            return;
        }

        if (confirm(`確定要刪除角色 "${role.name}" 嗎？`)) {
            this.roles = this.roles.filter(r => r.id !== roleId);
            this.saveRoles();
            this.loadRoleCards();
            
            this.logPermissionChange('admin', 'role_delete', roleId, `刪除角色: ${role.name}`);
            this.showNotification('角色已刪除', 'success');
        }
    }

    viewRolePermissions(roleId) {
        const role = this.roles.find(r => r.id === roleId);
        if (!role) return;

        this.showEditPermissionsModal(role);
    }

    showEditPermissionsModal(role) {
        const modal = document.getElementById('editPermissionsModal');
        modal.style.display = 'flex';

        // 設定模態框標題
        modal.querySelector('.modal-header h3').textContent = `編輯權限 - ${role.name}`;

        // 設定當前權限狀態
        const checkboxes = modal.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.checked = role.permissions.includes('*') || role.permissions.includes(cb.value);
            cb.disabled = role.permissions.includes('*'); // 如果有所有權限則禁用
        });

        // 儲存當前編輯的角色ID
        this.currentEditingRole = role.id;
    }

    savePermissions() {
        if (!this.currentEditingRole) return;

        const role = this.roles.find(r => r.id === this.currentEditingRole);
        if (!role) return;

        const selectedPermissions = [];
        document.querySelectorAll('#editPermissionsModal input[type="checkbox"]:checked').forEach(cb => {
            selectedPermissions.push(cb.value);
        });

        const oldPermissions = [...role.permissions];
        role.permissions = selectedPermissions;
        
        this.saveRoles();
        this.loadRoleCards();
        this.updatePermissionMatrix();
        this.closeModal('editPermissionsModal');

        this.logPermissionChange('admin', 'permission_update', role.id, 
            `更新權限: ${this.getPermissionDiff(oldPermissions, selectedPermissions)}`);
        
        this.showNotification('權限已更新', 'success');
    }

    getPermissionDiff(oldPerms, newPerms) {
        const added = newPerms.filter(p => !oldPerms.includes(p));
        const removed = oldPerms.filter(p => !newPerms.includes(p));
        
        let diff = '';
        if (added.length > 0) diff += `新增: ${added.map(p => this.getPermissionName(p)).join(', ')}`;
        if (removed.length > 0) {
            if (diff) diff += '; ';
            diff += `移除: ${removed.map(p => this.getPermissionName(p)).join(', ')}`;
        }
        
        return diff || '無變更';
    }

    // 權限矩陣更新
    updatePermissionMatrix() {
        const tbody = document.querySelector('.permissions-table tbody');
        const modules = [
            {
                name: '系統管理',
                description: '系統設定、用戶管理、權限設定',
                permissions: ['system_config', 'user_manage', 'role_manage']
            },
            {
                name: '菜單管理',
                description: '新增、編輯、刪除菜品',
                permissions: ['menu_view', 'menu_create', 'menu_edit', 'menu_delete']
            },
            {
                name: '訂單管理',
                description: '處理、修改、取消訂單',
                permissions: ['order_view', 'order_process', 'order_cancel']
            },
            {
                name: '營業報表',
                description: '銷售統計、營收分析',
                permissions: ['report_sales', 'report_revenue', 'report_dashboard']
            },
            {
                name: '廚房管理',
                description: '廚房訂單、出餐狀態',
                permissions: ['kitchen_view', 'kitchen_process']
            },
            {
                name: '備份還原',
                description: '資料備份、系統還原',
                permissions: ['backup_restore']
            }
        ];

        tbody.innerHTML = modules.map(module => {
            const adminAccess = this.getRoleAccess('admin', module.permissions);
            const managerAccess = this.getRoleAccess('manager', module.permissions);
            const staffAccess = this.getRoleAccess('staff', module.permissions);

            return `
                <tr>
                    <td class="module-name">
                        <strong>${module.name}</strong>
                        <small>${module.description}</small>
                    </td>
                    <td>${adminAccess}</td>
                    <td>${managerAccess}</td>
                    <td>${staffAccess}</td>
                </tr>
            `;
        }).join('');
    }

    getRoleAccess(roleId, modulePermissions) {
        const role = this.roles.find(r => r.id === roleId);
        if (!role) return '<span class="permission-status denied">✗ 角色不存在</span>';

        if (role.permissions.includes('*')) {
            return '<span class="permission-status granted">✓ 完全權限</span>';
        }

        const hasAllPermissions = modulePermissions.every(p => role.permissions.includes(p));
        const hasSomePermissions = modulePermissions.some(p => role.permissions.includes(p));

        if (hasAllPermissions) {
            return '<span class="permission-status granted">✓ 完全權限</span>';
        } else if (hasSomePermissions) {
            return '<span class="permission-status limited">◐ 部分權限</span>';
        } else {
            return '<span class="permission-status denied">✗ 無權限</span>';
        }
    }

    // 用戶權限管理
    loadUsers() {
        return JSON.parse(localStorage.getItem('restaurantUsers') || '[]');
    }

    loadUserPermissions() {
        const userList = document.querySelector('.user-list');
        userList.innerHTML = '';

        this.users.forEach(user => {
            const userItem = document.createElement('div');
            userItem.className = 'user-permission-item';
            
            const role = this.roles.find(r => r.id === user.role);
            const roleName = role ? role.name : user.role;

            userItem.innerHTML = `
                <div class="user-info">
                    <div class="user-avatar">👤</div>
                    <div class="user-details">
                        <h4>${user.username}</h4>
                        <span class="user-role">${roleName}</span>
                    </div>
                </div>
                <div class="user-status">
                    <span class="status-badge ${user.status}">${user.status === 'active' ? '啟用' : '停用'}</span>
                </div>
                <div class="user-actions">
                    <button class="btn btn-outline btn-sm" onclick="permissionManager.viewUserPermissions('${user.username}')">查看權限</button>
                    <button class="btn btn-secondary btn-sm" onclick="permissionManager.editUserPermissions('${user.username}')">編輯權限</button>
                </div>
            `;

            userList.appendChild(userItem);
        });
    }

    viewUserPermissions(username) {
        const user = this.users.find(u => u.username === username);
        if (!user) return;

        const role = this.roles.find(r => r.id === user.role);
        if (!role) return;

        const permissions = role.permissions.includes('*') ? ['所有權限'] : role.permissions.map(p => this.getPermissionName(p));
        
        const permissionsList = permissions.join(', ');
        alert(`用戶 ${username} 的權限:\n\n角色: ${role.name}\n權限: ${permissionsList}`);
    }

    editUserPermissions(username) {
        const user = this.users.find(u => u.username === username);
        if (!user) return;

        // 這裡可以實作編輯用戶權限的功能
        console.log('編輯用戶權限:', user);
        this.showNotification('編輯功能開發中', 'info');
    }

    // 權限記錄管理
    loadPermissions() {
        return JSON.parse(localStorage.getItem('userPermissions') || '{}');
    }

    savePermissions() {
        localStorage.setItem('userPermissions', JSON.stringify(this.permissions));
    }

    loadPermissionLogs() {
        const defaultLogs = [
            {
                id: 1,
                timestamp: '2024-01-15 14:30:25',
                user: 'admin',
                action: 'grant',
                target: 'manager',
                description: '授予菜單管理權限'
            },
            {
                id: 2,
                timestamp: '2024-01-15 12:15:10',
                user: 'admin',
                action: 'role_change',
                target: 'staff001',
                description: '員工 → 經理'
            }
        ];

        return JSON.parse(localStorage.getItem('permissionLogs') || JSON.stringify(defaultLogs));
    }

    savePermissionLogs() {
        localStorage.setItem('permissionLogs', JSON.stringify(this.permissionLogs));
    }

    logPermissionChange(user, action, target, description) {
        const log = {
            id: Date.now(),
            timestamp: new Date().toLocaleString('zh-TW'),
            user,
            action,
            target,
            description
        };

        this.permissionLogs.unshift(log);
        
        // 保留最近 100 條記錄
        if (this.permissionLogs.length > 100) {
            this.permissionLogs.splice(100);
        }

        this.savePermissionLogs();
        this.displayPermissionLogs();
    }

    displayPermissionLogs() {
        const container = document.querySelector('.logs-container');
        container.innerHTML = '';

        this.permissionLogs.forEach(log => {
            const logItem = document.createElement('div');
            logItem.className = 'log-item';

            logItem.innerHTML = `
                <div class="log-time">${log.timestamp}</div>
                <div class="log-content">
                    <span class="log-user">${log.user}</span>
                    <span class="log-action ${log.action}">${this.getActionText(log.action)}</span>
                    <span class="log-target">${log.target}</span>
                    <span class="log-permission">${log.description}</span>
                </div>
            `;

            container.appendChild(logItem);
        });
    }

    getActionText(action) {
        const actionTexts = {
            'grant': '授予',
            'revoke': '撤銷',
            'role_change': '變更角色',
            'role_create': '新增角色',
            'role_delete': '刪除角色',
            'permission_update': '更新權限'
        };
        return actionTexts[action] || action;
    }

    filterLogs() {
        const userFilter = document.getElementById('logUserFilter').value;
        const actionFilter = document.getElementById('logActionFilter').value;

        let filteredLogs = this.permissionLogs;

        if (userFilter) {
            filteredLogs = filteredLogs.filter(log => log.user === userFilter);
        }

        if (actionFilter) {
            filteredLogs = filteredLogs.filter(log => log.action === actionFilter);
        }

        const container = document.querySelector('.logs-container');
        container.innerHTML = '';

        filteredLogs.forEach(log => {
            const logItem = document.createElement('div');
            logItem.className = 'log-item';

            logItem.innerHTML = `
                <div class="log-time">${log.timestamp}</div>
                <div class="log-content">
                    <span class="log-user">${log.user}</span>
                    <span class="log-action ${log.action}">${this.getActionText(log.action)}</span>
                    <span class="log-target">${log.target}</span>
                    <span class="log-permission">${log.description}</span>
                </div>
            `;

            container.appendChild(logItem);
        });
    }

    // 工具方法
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }

    // API 權限檢查
    hasPermission(user, permission) {
        const userObj = this.users.find(u => u.username === user);
        if (!userObj) return false;

        const role = this.roles.find(r => r.id === userObj.role);
        if (!role) return false;

        return role.permissions.includes('*') || role.permissions.includes(permission);
    }

    checkAccess(user, requiredPermissions) {
        if (!Array.isArray(requiredPermissions)) {
            requiredPermissions = [requiredPermissions];
        }

        return requiredPermissions.some(permission => this.hasPermission(user, permission));
    }
}

// 初始化
let permissionManager;
document.addEventListener('DOMContentLoaded', () => {
    permissionManager = new PermissionManager();
});

// 模態框點擊外部關閉
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
