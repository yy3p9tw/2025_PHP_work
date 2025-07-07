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

// 處理新聞操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF 驗證失敗';
    } else {
        switch ($_POST['action']) {
            case 'add_news':
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $summary = trim($_POST['summary']);
                $source = trim($_POST['source']);
                $url = trim($_POST['url']);
                $published_at = $_POST['published_at'] ?: date('Y-m-d H:i:s');
                
                if (empty($title) || empty($content)) {
                    $error = '標題和內容不能為空';
                } else {
                    $result = $db->execute('
                        INSERT INTO news (title, content, summary, source, url, published_at, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ', [$title, $content, $summary, $source, $url, $published_at, 'active']);
                    
                    if ($result) {
                        $success = '新聞已新增';
                    } else {
                        $error = '新增失敗';
                    }
                }
                break;
                
            case 'update_news':
                $id = (int)$_POST['id'];
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $summary = trim($_POST['summary']);
                $source = trim($_POST['source']);
                $url = trim($_POST['url']);
                $status = $_POST['status'];
                $published_at = $_POST['published_at'];
                
                if (empty($title) || empty($content)) {
                    $error = '標題和內容不能為空';
                } else {
                    $result = $db->execute('
                        UPDATE news 
                        SET title = ?, content = ?, summary = ?, source = ?, url = ?, status = ?, published_at = ?
                        WHERE id = ?
                    ', [$title, $content, $summary, $source, $url, $status, $published_at, $id]);
                    
                    if ($result) {
                        $success = '新聞已更新';
                    } else {
                        $error = '更新失敗';
                    }
                }
                break;
                
            case 'delete_news':
                $id = (int)$_POST['id'];
                
                $result = $db->execute('DELETE FROM news WHERE id = ?', [$id]);
                if ($result) {
                    $success = '新聞已刪除';
                } else {
                    $error = '刪除失敗';
                }
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                $current_status = $_POST['current_status'];
                $new_status = $current_status === 'active' ? 'inactive' : 'active';
                
                $result = $db->execute('UPDATE news SET status = ? WHERE id = ?', [$new_status, $id]);
                if ($result) {
                    $success = '新聞狀態已更新';
                } else {
                    $error = '更新失敗';
                }
                break;
        }
    }
}

// 獲取搜尋和篩選參數
$search = $_GET['search'] ?? '';
$source_filter = $_GET['source'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 構建查詢條件
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = '(title LIKE ? OR content LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($source_filter)) {
    $where_conditions[] = 'source = ?';
    $params[] = $source_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = 'status = ?';
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 獲取新聞列表
$news_list = $db->fetchAll("
    SELECT * FROM news 
    {$where_clause}
    ORDER BY published_at DESC 
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

// 獲取總數
$total_result = $db->fetchOne("SELECT COUNT(*) as count FROM news {$where_clause}", $params);
$total = $total_result['count'];
$total_pages = ceil($total / $limit);

// 獲取所有來源
$sources = $db->fetchAll('SELECT DISTINCT source FROM news WHERE source IS NOT NULL ORDER BY source');

$page_title = '新聞管理';
include '../includes/admin_header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>新聞管理</h1>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                        <i class="fas fa-plus"></i> 新增新聞
                    </button>
                </div>
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

            <!-- 搜尋和篩選 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="搜尋新聞標題或內容" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="source">
                                <option value="">所有來源</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?php echo htmlspecialchars($src['source']); ?>" 
                                        <?php echo $source_filter === $src['source'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($src['source']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">所有狀態</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>啟用</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>停用</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">搜尋</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 新聞列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>標題</th>
                                    <th>來源</th>
                                    <th>狀態</th>
                                    <th>發布時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news_list as $news): ?>
                                <tr>
                                    <td><?php echo $news['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars(mb_substr($news['title'], 0, 50)); ?></strong>
                                        <?php if (mb_strlen($news['title']) > 50): ?>...<?php endif; ?>
                                        <?php if ($news['summary']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($news['summary'], 0, 100)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($news['source'] ?? '未知'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $news['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $news['status'] === 'active' ? '啟用' : '停用'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($news['published_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../news_detail.php?id=<?php echo $news['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" target="_blank">
                                                預覽
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editNewsModal"
                                                    data-news='<?php echo json_encode($news); ?>'>
                                                編輯
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $news['status']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-<?php echo $news['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                    <?php echo $news['status'] === 'active' ? '停用' : '啟用'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="delete_news">
                                                <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('確定要刪除此新聞嗎？')">
                                                    刪除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="新聞列表分頁">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&source=<?php echo urlencode($source_filter); ?>&status=<?php echo urlencode($status_filter); ?>">上一頁</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&source=<?php echo urlencode($source_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&source=<?php echo urlencode($source_filter); ?>&status=<?php echo urlencode($status_filter); ?>">下一頁</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新增新聞模態框 -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增新聞</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_news">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">標題 *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="summary" class="form-label">摘要</label>
                        <textarea class="form-control" id="summary" name="summary" rows="2" 
                                  placeholder="新聞摘要，用於列表顯示"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="source" class="form-label">來源</label>
                        <input type="text" class="form-control" id="source" name="source" 
                               placeholder="例如：自由時報、經濟日報、中央社">
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="form-label">原始連結</label>
                        <input type="url" class="form-control" id="url" name="url" 
                               placeholder="https://example.com/news/123">
                    </div>
                    
                    <div class="mb-3">
                        <label for="published_at" class="form-label">發布時間</label>
                        <input type="datetime-local" class="form-control" id="published_at" name="published_at">
                        <div class="form-text">留空則使用目前時間</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">內容 *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
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

<!-- 編輯新聞模態框 -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯新聞</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_news">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">標題 *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_summary" class="form-label">摘要</label>
                        <textarea class="form-control" id="edit_summary" name="summary" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_source" class="form-label">來源</label>
                        <input type="text" class="form-control" id="edit_source" name="source">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_url" class="form-label">原始連結</label>
                        <input type="url" class="form-control" id="edit_url" name="url">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">狀態</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">啟用</option>
                            <option value="inactive">停用</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_published_at" class="form-label">發布時間</label>
                        <input type="datetime-local" class="form-control" id="edit_published_at" name="published_at">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">內容 *</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="10" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">更新</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 編輯新聞模態框
    const editModal = document.getElementById('editNewsModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const news = JSON.parse(button.getAttribute('data-news'));
        
        document.getElementById('edit_id').value = news.id;
        document.getElementById('edit_title').value = news.title;
        document.getElementById('edit_summary').value = news.summary || '';
        document.getElementById('edit_source').value = news.source || '';
        document.getElementById('edit_url').value = news.url || '';
        document.getElementById('edit_status').value = news.status;
        document.getElementById('edit_content').value = news.content;
        
        // 處理發布時間
        if (news.published_at) {
            const date = new Date(news.published_at);
            const isoString = date.toISOString().slice(0, 16);
            document.getElementById('edit_published_at').value = isoString;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
