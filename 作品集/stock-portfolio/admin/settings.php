<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/auth.php';

// 檢查是否已登入且為管理員
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();

// 處理設定操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF 驗證失敗';
    } else {
        switch ($_POST['action']) {
            case 'update_settings':
                $success_count = 0;
                $error_count = 0;
                
                foreach ($_POST['settings'] as $key => $value) {
                    if ($key === 'csrf_token' || $key === 'action') continue;
                    
                    $result = $db->execute('
                        UPDATE settings 
                        SET setting_value = ? 
                        WHERE setting_key = ?
                    ', [$value, $key]);
                    
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
                
                if ($error_count === 0) {
                    $success = "成功更新 {$success_count} 個設定";
                } else {
                    $error = "更新失敗 {$error_count} 個設定";
                }
                break;
                
            case 'add_setting':
                $key = trim($_POST['setting_key']);
                $value = trim($_POST['setting_value']);
                $description = trim($_POST['description']);
                
                if (empty($key)) {
                    $error = '設定鍵不能為空';
                } else {
                    // 檢查鍵是否已存在
                    $existing = $db->fetchOne('SELECT id FROM settings WHERE setting_key = ?', [$key]);
                    if ($existing) {
                        $error = '設定鍵已存在';
                    } else {
                        $result = $db->execute('
                            INSERT INTO settings (setting_key, setting_value, description) 
                            VALUES (?, ?, ?)
                        ', [$key, $value, $description]);
                        
                        if ($result) {
                            $success = '設定已新增';
                        } else {
                            $error = '新增失敗';
                        }
                    }
                }
                break;
                
            case 'delete_setting':
                $id = (int)$_POST['id'];
                
                $result = $db->execute('DELETE FROM settings WHERE id = ?', [$id]);
                if ($result) {
                    $success = '設定已刪除';
                } else {
                    $error = '刪除失敗';
                }
                break;
        }
    }
}

// 獲取所有設定
$settings = $db->fetchAll('SELECT * FROM settings ORDER BY setting_key');

// 獲取系統統計
$stats = [
    'total_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users')['count'],
    'active_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users WHERE is_active = 1')['count'],
    'total_stocks' => $db->fetchOne('SELECT COUNT(*) as count FROM stocks WHERE status = ?', ['active'])['count'],
    'total_news' => $db->fetchOne('SELECT COUNT(*) as count FROM news WHERE status = ?', ['active'])['count'],
    'total_transactions' => $db->fetchOne('SELECT COUNT(*) as count FROM transactions')['count'],
    'total_portfolios' => $db->fetchOne('SELECT COUNT(*) as count FROM portfolios WHERE quantity > 0')['count']
];

$page_title = '系統設定';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>系統設定</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- 系統統計 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">系統統計</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h3 text-primary"><?php echo number_format($stats['total_users']); ?></div>
                                    <div class="text-muted">總用戶數</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h3 text-success"><?php echo number_format($stats['active_users']); ?></div>
                                    <div class="text-muted">活躍用戶</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h3 text-info"><?php echo number_format($stats['total_stocks']); ?></div>
                                    <div class="text-muted">股票數量</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h3 text-warning"><?php echo number_format($stats['total_news']); ?></div>
                                    <div class="text-muted">新聞數量</div>
                                </div>
                                <div class="col-6">
                                    <div class="h3 text-danger"><?php echo number_format($stats['total_transactions']); ?></div>
                                    <div class="text-muted">交易記錄</div>
                                </div>
                                <div class="col-6">
                                    <div class="h3 text-secondary"><?php echo number_format($stats['total_portfolios']); ?></div>
                                    <div class="text-muted">投資組合</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系統設定 -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">系統設定</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                                <i class="fas fa-plus"></i> 新增設定
                            </button>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>設定鍵</th>
                                                <th>設定值</th>
                                                <th>描述</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($settings as $setting): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($setting['setting_key']); ?></strong></td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]" 
                                                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($setting['description']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteSetting(<?php echo $setting['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 儲存設定
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 系統資訊 -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">系統資訊</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>PHP 版本</th>
                                            <td><?php echo PHP_VERSION; ?></td>
                                        </tr>
                                        <tr>
                                            <th>伺服器軟體</th>
                                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>作業系統</th>
                                            <td><?php echo PHP_OS; ?></td>
                                        </tr>
                                        <tr>
                                            <th>記憶體限制</th>
                                            <td><?php echo ini_get('memory_limit'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>上傳限制</th>
                                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>執行時間限制</th>
                                            <td><?php echo ini_get('max_execution_time'); ?>s</td>
                                        </tr>
                                        <tr>
                                            <th>時區</th>
                                            <td><?php echo date_default_timezone_get(); ?></td>
                                        </tr>
                                        <tr>
                                            <th>目前時間</th>
                                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新增設定模態框 -->
<div class="modal fade" id="addSettingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增設定</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_setting">
                    
                    <div class="mb-3">
                        <label for="setting_key" class="form-label">設定鍵 *</label>
                        <input type="text" class="form-control" id="setting_key" name="setting_key" required>
                        <div class="form-text">例如：site_name、max_upload_size</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_value" class="form-label">設定值</label>
                        <input type="text" class="form-control" id="setting_value" name="setting_value">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">描述</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">新增</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 隱藏的刪除表單 -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete_setting">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function deleteSetting(id) {
    if (confirm('確定要刪除此設定嗎？')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
