<?php
// 開始輸出緩衝
ob_start();

// 設置錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 清理任何意外的輸出
ob_clean();

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
        case 'PUT':
            handlePutRequest($conn, $action, $user_id);
            break;
        case 'DELETE':
            handleDeleteRequest($conn, $action, $user_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '方法不被允許']);
    }
} catch (Exception $e) {
    error_log("Stocks API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '伺服器錯誤：' . $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ]);
}

function handleGetRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'search':
            searchStocks($conn);
            break;
        case 'quote':
            getStockQuote($conn);
            break;
        case 'portfolio':
            getPortfolioData($conn, $user_id);
            break;
        case 'history':
            getStockHistory($conn);
            break;
        case 'popular':
            getPopularStocks($conn);
            break;
        case 'gainers':
            getTopGainers($conn);
            break;
        case 'losers':
            getTopLosers($conn);
            break;
        default:
            listStocks($conn);
    }
}

function handlePostRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'add_to_watchlist':
            addToWatchlist($conn, $user_id);
            break;
        case 'remove_from_watchlist':
            removeFromWatchlist($conn, $user_id);
            break;
        case 'update_prices':
            updateStockPrices($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function handlePutRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'update_stock':
            updateStock($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function handleDeleteRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'delete_stock':
            deleteStock($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function searchStocks($conn) {
    $query = $_GET['query'] ?? '';
    $limit = (int)($_GET['limit'] ?? 10);
    
    if (empty($query)) {
        echo json_encode(['success' => false, 'message' => '查詢字串不能為空']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT code, name, industry, current_price, price_change, change_percent, volume, market_cap
        FROM stocks 
        WHERE (code LIKE ? OR name LIKE ?) AND status = 'active'
        ORDER BY volume DESC
        LIMIT ?
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $limit]);
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化資料
    $formattedStocks = array_map(function($stock) {
        return [
            'code' => $stock['code'],
            'name' => $stock['name'],
            'industry' => $stock['industry'],
            'price' => floatval($stock['current_price']),
            'change' => floatval($stock['price_change']),
            'changePercent' => floatval($stock['change_percent']),
            'volume' => intval($stock['volume']),
            'marketCap' => floatval($stock['market_cap'])
        ];
    }, $stocks);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedStocks,
        'count' => count($formattedStocks)
    ]);
}

function getStockQuote($conn) {
    $code = $_GET['code'] ?? '';
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => '股票代碼不能為空']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM stocks 
        WHERE code = ? AND status = 'active'
    ");
    $stmt->execute([$code]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stock) {
        echo json_encode(['success' => false, 'message' => '找不到該股票']);
        return;
    }
    
    // 獲取歷史價格
    $stmt = $conn->prepare("
        SELECT date, open_price, high_price, low_price, close_price, volume
        FROM stock_prices 
        WHERE stock_code = ? 
        ORDER BY date DESC 
        LIMIT 30
    ");
    $stmt->execute([$code]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'stock' => $stock,
            'history' => $history
        ]
    ]);
}

function getPortfolioData($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent
        FROM portfolios p
        JOIN stocks s ON p.stock_code = s.code
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalInvestment = 0;
    $totalMarketValue = 0;
    $holdings = [];
    
    foreach ($portfolios as $portfolio) {
        $investment = $portfolio['quantity'] * $portfolio['avg_price'];
        $marketValue = $portfolio['quantity'] * $portfolio['current_price'];
        $profitLoss = $marketValue - $investment;
        $profitLossPercent = $investment > 0 ? ($profitLoss / $investment) * 100 : 0;
        
        $totalInvestment += $investment;
        $totalMarketValue += $marketValue;
        
        $holdings[] = [
            'stockCode' => $portfolio['stock_code'],
            'stockName' => $portfolio['stock_name'],
            'quantity' => intval($portfolio['quantity']),
            'avgPrice' => floatval($portfolio['avg_price']),
            'currentPrice' => floatval($portfolio['current_price']),
            'investment' => $investment,
            'marketValue' => $marketValue,
            'profitLoss' => $profitLoss,
            'profitLossPercent' => $profitLossPercent,
            'priceChange' => floatval($portfolio['price_change']),
            'changePercent' => floatval($portfolio['change_percent'])
        ];
    }
    
    $totalProfitLoss = $totalMarketValue - $totalInvestment;
    $totalProfitLossPercent = $totalInvestment > 0 ? ($totalProfitLoss / $totalInvestment) * 100 : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'totalInvestment' => $totalInvestment,
            'totalMarketValue' => $totalMarketValue,
            'totalProfitLoss' => $totalProfitLoss,
            'totalProfitLossPercent' => $totalProfitLossPercent,
            'holdings' => $holdings,
            'count' => count($holdings)
        ]
    ]);
}

function getStockHistory($conn) {
    $code = $_GET['code'] ?? '';
    $days = (int)($_GET['days'] ?? 30);
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => '股票代碼不能為空']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT date, open_price, high_price, low_price, close_price, volume
        FROM stock_prices 
        WHERE stock_code = ? 
        ORDER BY date DESC 
        LIMIT ?
    ");
    $stmt->execute([$code, $days]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $history,
        'count' => count($history)
    ]);
}

function getPopularStocks($conn) {
    $limit = (int)($_GET['limit'] ?? 10);
    
    $stmt = $conn->prepare("
        SELECT code, name, current_price, price_change, change_percent, volume
        FROM stocks 
        WHERE status = 'active'
        ORDER BY volume DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stocks,
        'count' => count($stocks)
    ]);
}

function getTopGainers($conn) {
    $limit = (int)($_GET['limit'] ?? 10);
    
    $stmt = $conn->prepare("
        SELECT code, name, current_price, price_change, change_percent, volume
        FROM stocks 
        WHERE status = 'active' AND change_percent > 0
        ORDER BY change_percent DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stocks,
        'count' => count($stocks)
    ]);
}

function getTopLosers($conn) {
    $limit = (int)($_GET['limit'] ?? 10);
    
    $stmt = $conn->prepare("
        SELECT code, name, current_price, price_change, change_percent, volume
        FROM stocks 
        WHERE status = 'active' AND change_percent < 0
        ORDER BY change_percent ASC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stocks,
        'count' => count($stocks)
    ]);
}

function listStocks($conn) {
    try {
        // 獲取 DataTables 參數
        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        
        // 獲取股票數據
        $stmt = $conn->prepare("
            SELECT code, name, industry, current_price, price_change, change_percent, volume, market_cap
            FROM stocks 
            WHERE status = 'active'
            ORDER BY code
            LIMIT :length OFFSET :start
        ");
        $stmt->bindParam(':length', $length, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->execute();
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 獲取總數
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stocks WHERE status = 'active'");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // 返回 DataTables 格式
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => intval($total),
            'recordsFiltered' => intval($total),
            'data' => $stocks
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
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

function removeFromWatchlist($conn, $user_id) {
    $stockCode = $_POST['stock_code'] ?? '';
    
    if (empty($stockCode)) {
        echo json_encode(['success' => false, 'message' => '股票代碼不能為空']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    
    echo json_encode(['success' => true, 'message' => '已從關注清單中移除']);
}

function updateStockPrices($conn) {
    // 這裡應該實作從外部API獲取最新股價的邏輯
    // 目前使用模擬資料
    $stockPrices = getSimulatedStockPrices();
    
    foreach ($stockPrices as $stockCode => $data) {
        $stmt = $conn->prepare("
            UPDATE stocks 
            SET current_price = ?, price_change = ?, change_percent = ?, 
                volume = ?, updated_at = NOW()
            WHERE code = ?
        ");
        $stmt->execute([
            $data['price'],
            $data['change'],
            $data['changePercent'],
            $data['volume'],
            $stockCode
        ]);
        
        // 記錄價格歷史
        $stmt = $conn->prepare("
            INSERT INTO stock_prices (stock_code, date, open_price, high_price, low_price, close_price, volume)
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                high_price = GREATEST(high_price, VALUES(high_price)),
                low_price = LEAST(low_price, VALUES(low_price)),
                close_price = VALUES(close_price),
                volume = VALUES(volume)
        ");
        $stmt->execute([
            $stockCode,
            $data['price'],
            $data['price'] * 1.02,
            $data['price'] * 0.98,
            $data['price'],
            $data['volume']
        ]);
    }
    
    echo json_encode(['success' => true, 'message' => '股價已更新']);
}

function getSimulatedStockPrices() {
    // 模擬股價資料
    $stocks = ['2330', '2317', '2454', '2412', '6505', '2303', '3008', '2881', '2002', '2892'];
    $prices = [];
    
    foreach ($stocks as $code) {
        $basePrice = rand(50, 500);
        $change = rand(-10, 10) / 10;
        $changePercent = ($change / $basePrice) * 100;
        
        $prices[$code] = [
            'price' => $basePrice + $change,
            'change' => $change,
            'changePercent' => $changePercent,
            'volume' => rand(1000, 100000)
        ];
    }
    
    return $prices;
}
?>
