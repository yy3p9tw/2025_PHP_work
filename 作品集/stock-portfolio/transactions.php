<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// 獲取交易記錄
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_transactions = $stmt->fetchColumn();
$total_pages = ceil($total_transactions / $per_page);

$stmt = $conn->prepare("
    SELECT t.*, s.name as stock_name
    FROM transactions t
    LEFT JOIN stocks s ON t.stock_code = s.code
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

// 統計資料
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN type = 'buy' THEN total_amount ELSE 0 END) as total_buy,
        SUM(CASE WHEN type = 'sell' THEN total_amount ELSE 0 END) as total_sell,
        SUM(CASE WHEN type = 'buy' THEN quantity ELSE 0 END) as total_buy_quantity,
        SUM(CASE WHEN type = 'sell' THEN quantity ELSE 0 END) as total_sell_quantity
    FROM transactions 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>交易記錄 - 股票投資組合系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line"></i> 股票投資組合
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">首頁</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="portfolio.php">投資組合</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stocks.php">股票查詢</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php">財經新聞</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="transactions.php">交易記錄</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-exchange-alt"></i> 交易記錄</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                        <i class="fas fa-plus"></i> 新增交易
                    </button>
                </div>

                <!-- 統計卡片 -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">總交易次數</h5>
                                <h3><?php echo number_format($stats['total_count']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">買入總額</h5>
                                <h3>$<?php echo number_format($stats['total_buy'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">賣出總額</h5>
                                <h3>$<?php echo number_format($stats['total_sell'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">淨投資額</h5>
                                <h3>$<?php echo number_format($stats['total_buy'] - $stats['total_sell'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 交易記錄表格 -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>日期</th>
                                        <th>類型</th>
                                        <th>股票代碼</th>
                                        <th>股票名稱</th>
                                        <th>數量</th>
                                        <th>價格</th>
                                        <th>總金額</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaction['type'] == 'buy' ? 'success' : 'danger'; ?>">
                                                <?php echo $transaction['type'] == 'buy' ? '買入' : '賣出'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['stock_code']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['stock_name'] ?? $transaction['stock_code']); ?></td>
                                        <td><?php echo number_format($transaction['quantity']); ?></td>
                                        <td>$<?php echo number_format($transaction['price'], 2); ?></td>
                                        <td>$<?php echo number_format($transaction['total_amount'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(<?php echo $transaction['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 分頁 -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="交易記錄分頁">
                            <ul class="pagination justify-content-center mt-3">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 新增交易 Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新增交易記錄</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTransactionForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="transactionType" class="form-label">交易類型</label>
                            <select class="form-select" id="transactionType" name="type" required>
                                <option value="">請選擇交易類型</option>
                                <option value="buy">買入</option>
                                <option value="sell">賣出</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="stockCode" class="form-label">股票代碼</label>
                            <input type="text" class="form-control" id="stockCode" name="stock_code" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">數量</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">價格</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="totalAmount" class="form-label">總金額</label>
                            <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">新增交易</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 計算總金額
        function calculateTotal() {
            const quantity = parseFloat($('#quantity').val()) || 0;
            const price = parseFloat($('#price').val()) || 0;
            const total = quantity * price;
            $('#totalAmount').val(total.toFixed(2));
        }

        $('#quantity, #price').on('input', calculateTotal);

        // 新增交易
        $('#addTransactionForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                type: $('#transactionType').val(),
                stock_code: $('#stockCode').val(),
                quantity: parseInt($('#quantity').val()),
                price: parseFloat($('#price').val()),
                total_amount: parseFloat($('#totalAmount').val())
            };

            $.ajax({
                url: 'api/transactions.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert('交易記錄新增成功');
                        window.location.reload();
                    } else {
                        alert('新增失敗：' + response.message);
                    }
                },
                error: function() {
                    alert('系統錯誤，請稍後再試');
                }
            });
        });

        // 刪除交易記錄
        function deleteTransaction(id) {
            if (confirm('確定要刪除這筆交易記錄嗎？')) {
                $.ajax({
                    url: 'api/transactions.php',
                    method: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: id }),
                    success: function(response) {
                        if (response.success) {
                            alert('交易記錄刪除成功');
                            window.location.reload();
                        } else {
                            alert('刪除失敗：' + response.message);
                        }
                    },
                    error: function() {
                        alert('系統錯誤，請稍後再試');
                    }
                });
            }
        }
    </script>
</body>
</html>
