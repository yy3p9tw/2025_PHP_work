<?php
// 管理端：操作日誌
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$logs = db()->query("SELECT l.*, u.username FROM activity_log l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>操作日誌 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">操作日誌</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">返回</a>
    <table class="table table-bordered bg-white">
        <thead>
            <tr><th>時間</th><th>帳號</th><th>動作</th><th>說明</th></tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $i => $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td>
                  <?php if($log['detail']): ?>
                    <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#logModal" data-detail="<?=htmlspecialchars($log['detail'],ENT_QUOTES)?>">查看</button>
                  <?php else: ?>
                    <span class="text-muted">無</span>
                  <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logModalLabel">詳細內容</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="logModalBody">
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var logModal = document.getElementById('logModal');
logModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var detail = button.getAttribute('data-detail');
  document.getElementById('logModalBody').textContent = detail;
});
</script>
</body>
</html>
