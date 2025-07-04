<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 檢查是否登入
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '未授權']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $action, $user_id);
            break;
        case 'POST':
            handlePostRequest($conn, $action, $user_id);
            break;
        case 'DELETE':
            handleDeleteRequest($conn, $action, $user_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '方法不被允許']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
}

function handleGetRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'list':
        default:
            getWatchlist($conn, $user_id);
    }
}

function handlePostRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'add':
            addToWatchlist($conn, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function handleDeleteRequest($conn, $action, $user_id) {
    $stockCode = $_GET['stock_code'] ?? '';
    
    if (empty($stockCode)) {
        echo json_encode(['success' => false, 'message' => '股票代碼不能為空']);
        return;
    }
    
    removeFromWatchlist($conn, $user_id, $stockCode);
}

function getWatchlist($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT w.stock_code, s.name, s.current_price, s.price_change, s.change_percent, s.volume, w.created_at
        FROM watchlist w
        JOIN stocks s ON w.stock_code = s.code
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formattedWatchlist = array_map(function($item) {
        return [
            'code' => $item['stock_code'],
            'name' => $item['name'],
            'price' => floatval($item['current_price']),
            'change' => floatval($item['price_change']),
            'changePercent' => floatval($item['change_percent']),
            'volume' => intval($item['volume']),
            'addedAt' => $item['created_at']
        ];
    }, $watchlist);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedWatchlist,
        'count' => count($formattedWatchlist)
    ]);
}

function addToWatchlist($conn, $user_id) {
    $stockCode = $_POST['stock_code'] ?? '';
    
    if (empty($stockCode)) {
        echo json_encode(['success' => false, 'message' => '股票代碼不能為空']);
        return;
    }
    
    // 檢查股票是否存在
    $stmt = $conn->prepare("SELECT id FROM stocks WHERE code = ?");
    $stmt->execute([$stockCode]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '股票不存在']);
        return;
    }
    
    // 檢查是否已在關注清單中
    $stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '已在關注清單中']);
        return;
    }
    
    // 加入關注清單
    $stmt = $conn->prepare("INSERT INTO watchlist (user_id, stock_code, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $stockCode]);
    
    echo json_encode(['success' => true, 'message' => '已加入關注清單']);
}

function removeFromWatchlist($conn, $user_id, $stockCode) {
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '已從關注清單中移除']);
    } else {
        echo json_encode(['success' => false, 'message' => '移除失敗']);
    }
}
?>
