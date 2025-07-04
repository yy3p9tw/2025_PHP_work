<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '用戶未登入']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetTransactions($conn, $user_id);
            break;
        case 'POST':
            handleCreateTransaction($conn, $user_id);
            break;
        case 'DELETE':
            handleDeleteTransaction($conn, $user_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
    }
} catch (Exception $e) {
    error_log("Transaction API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '系統錯誤']);
}

function handleGetTransactions($conn, $user_id) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
    $offset = ($page - 1) * $per_page;

    // 獲取交易記錄總數
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total = $stmt->fetchColumn();

    // 獲取交易記錄
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
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'pages' => ceil($total / $per_page)
            ],
            'stats' => $stats
        ]
    ]);
}

function handleCreateTransaction($conn, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '無效的請求資料']);
        return;
    }

    $required_fields = ['type', 'stock_code', 'quantity', 'price', 'total_amount'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "缺少必要欄位: $field"]);
            return;
        }
    }

    $type = $input['type'];
    $stock_code = $input['stock_code'];
    $quantity = (int)$input['quantity'];
    $price = (float)$input['price'];
    $total_amount = (float)$input['total_amount'];

    // 驗證交易類型
    if (!in_array($type, ['buy', 'sell'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '無效的交易類型']);
        return;
    }

    // 驗證數量和價格
    if ($quantity <= 0 || $price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '數量和價格必須大於 0']);
        return;
    }

    // 驗證總金額
    if (abs($total_amount - ($quantity * $price)) > 0.01) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '總金額計算錯誤']);
        return;
    }

    // 檢查股票是否存在
    $stmt = $conn->prepare("SELECT id FROM stocks WHERE code = ?");
    $stmt->execute([$stock_code]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '股票代碼不存在']);
        return;
    }

    $conn->beginTransaction();
    
    try {
        // 新增交易記錄
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, stock_code, type, quantity, price, total_amount)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $stock_code, $type, $quantity, $price, $total_amount]);
        $transaction_id = $conn->lastInsertId();

        // 更新投資組合
        updatePortfolio($conn, $user_id, $stock_code, $type, $quantity, $price);

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => '交易記錄新增成功',
            'data' => ['transaction_id' => $transaction_id]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction creation error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '新增交易記錄失敗']);
    }
}

function handleDeleteTransaction($conn, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少交易記錄 ID']);
        return;
    }

    $transaction_id = (int)$input['id'];

    // 檢查交易記錄是否存在且屬於該用戶
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transaction_id, $user_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '交易記錄不存在']);
        return;
    }

    $conn->beginTransaction();
    
    try {
        // 刪除交易記錄
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$transaction_id, $user_id]);

        // 反向更新投資組合
        $reverse_type = $transaction['type'] == 'buy' ? 'sell' : 'buy';
        updatePortfolio($conn, $user_id, $transaction['stock_code'], $reverse_type, $transaction['quantity'], $transaction['price']);

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => '交易記錄刪除成功'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction deletion error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '刪除交易記錄失敗']);
    }
}

function updatePortfolio($conn, $user_id, $stock_code, $type, $quantity, $price) {
    // 檢查投資組合是否存在
    $stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stock_code]);
    $portfolio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($portfolio) {
        // 更新現有投資組合
        $current_quantity = $portfolio['quantity'];
        $current_avg_price = $portfolio['avg_price'];
        
        if ($type == 'buy') {
            $new_quantity = $current_quantity + $quantity;
            $new_avg_price = (($current_quantity * $current_avg_price) + ($quantity * $price)) / $new_quantity;
        } else {
            $new_quantity = $current_quantity - $quantity;
            $new_avg_price = $current_avg_price; // 賣出時平均成本不變
        }

        if ($new_quantity > 0) {
            $stmt = $conn->prepare("
                UPDATE portfolios 
                SET quantity = ?, avg_price = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND stock_code = ?
            ");
            $stmt->execute([$new_quantity, $new_avg_price, $user_id, $stock_code]);
        } else {
            // 如果數量為 0 或負數，刪除投資組合記錄
            $stmt = $conn->prepare("DELETE FROM portfolios WHERE user_id = ? AND stock_code = ?");
            $stmt->execute([$user_id, $stock_code]);
        }
    } else {
        // 新增投資組合（只有買入時才會發生）
        if ($type == 'buy') {
            $stmt = $conn->prepare("
                INSERT INTO portfolios (user_id, stock_code, quantity, avg_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $stock_code, $quantity, $price]);
        }
    }
}
?>
