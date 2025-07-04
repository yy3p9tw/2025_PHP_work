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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
}

function handleGetRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'summary':
            getPortfolioSummary($conn, $user_id);
            break;
        case 'holdings':
            getPortfolioHoldings($conn, $user_id);
            break;
        case 'performance':
            getPortfolioPerformance($conn, $user_id);
            break;
        case 'allocation':
            getPortfolioAllocation($conn, $user_id);
            break;
        case 'history':
            getPortfolioHistory($conn, $user_id);
            break;
        default:
            getPortfolioData($conn, $user_id);
    }
}

function handlePostRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'add':
            addToPortfolio($conn, $user_id);
            break;
        case 'buy':
            buyStock($conn, $user_id);
            break;
        case 'sell':
            sellStock($conn, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function handlePutRequest($conn, $action, $user_id) {
    switch ($action) {
        case 'update':
            updatePortfolio($conn, $user_id);
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
    
    removeFromPortfolio($conn, $user_id, $stockCode);
}

function getPortfolioData($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent, s.industry
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
        $current_price = isset($portfolio['current_price']) ? $portfolio['current_price'] : 0;
        $stock_name = isset($portfolio['stock_name']) ? $portfolio['stock_name'] : $portfolio['stock_code'];
        $marketValue = $portfolio['quantity'] * $current_price;
        $profitLoss = $marketValue - $investment;
        $profitLossPercent = $investment > 0 ? ($profitLoss / $investment) * 100 : 0;
        
        $totalInvestment += $investment;
        $totalMarketValue += $marketValue;
        
        $holdings[] = [
            'stockCode' => $portfolio['stock_code'],
            'stockName' => $stock_name,
            'industry' => $portfolio['industry'] ?? '',
            'quantity' => intval($portfolio['quantity']),
            'avgPrice' => floatval($portfolio['avg_price']),
            'currentPrice' => floatval($current_price),
            'investment' => $investment,
            'marketValue' => $marketValue,
            'profitLoss' => $profitLoss,
            'profitLossPercent' => $profitLossPercent,
            'priceChange' => floatval($portfolio['price_change'] ?? 0),
            'changePercent' => floatval($portfolio['change_percent'] ?? 0),
            'weight' => 0, // 將在後面計算
            'createdAt' => $portfolio['created_at']
        ];
    }
    
    // 計算權重
    foreach ($holdings as &$holding) {
        $holding['weight'] = $totalMarketValue > 0 ? ($holding['marketValue'] / $totalMarketValue) * 100 : 0;
    }
    
    $totalProfitLoss = $totalMarketValue - $totalInvestment;
    $totalProfitLossPercent = $totalInvestment > 0 ? ($totalProfitLoss / $totalInvestment) * 100 : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'totalInvestment' => $totalInvestment,
                'totalMarketValue' => $totalMarketValue,
                'totalProfitLoss' => $totalProfitLoss,
                'totalProfitLossPercent' => $totalProfitLossPercent,
                'holdingsCount' => count($holdings)
            ],
            'holdings' => $holdings
        ]
    ]);
}

function getPortfolioSummary($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            SUM(p.quantity * p.avg_price) as total_investment,
            SUM(p.quantity * s.current_price) as total_market_value,
            COUNT(*) as holdings_count
        FROM portfolios p
        JOIN stocks s ON p.stock_code = s.code
        WHERE p.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalInvestment = floatval($summary['total_investment'] ?? 0);
    $totalMarketValue = floatval($summary['total_market_value'] ?? 0);
    $totalProfitLoss = $totalMarketValue - $totalInvestment;
    $totalProfitLossPercent = $totalInvestment > 0 ? ($totalProfitLoss / $totalInvestment) * 100 : 0;
    
    // 獲取今日損益
    $stmt = $conn->prepare("
        SELECT SUM(p.quantity * s.price_change) as today_profit_loss
        FROM portfolios p
        JOIN stocks s ON p.stock_code = s.code
        WHERE p.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $todayProfitLoss = floatval($stmt->fetchColumn() ?? 0);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'totalInvestment' => $totalInvestment,
            'totalMarketValue' => $totalMarketValue,
            'totalProfitLoss' => $totalProfitLoss,
            'totalProfitLossPercent' => $totalProfitLossPercent,
            'todayProfitLoss' => $todayProfitLoss,
            'holdingsCount' => intval($summary['holdings_count'] ?? 0)
        ]
    ]);
}

function getPortfolioHoldings($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent, s.industry
        FROM portfolios p
        JOIN stocks s ON p.stock_code = s.code
        WHERE p.user_id = ?
        ORDER BY (p.quantity * s.current_price) DESC
    ");
    $stmt->execute([$user_id]);
    $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalMarketValue = 0;
    $formattedHoldings = [];
    
    foreach ($holdings as $holding) {
        $current_price = isset($holding['current_price']) ? $holding['current_price'] : 0;
        $stock_name = isset($holding['stock_name']) ? $holding['stock_name'] : $holding['stock_code'];
        $marketValue = $holding['quantity'] * $current_price;
        $totalMarketValue += $marketValue;
        
        $formattedHoldings[] = [
            'stockCode' => $holding['stock_code'],
            'stockName' => $stock_name,
            'industry' => $holding['industry'] ?? '',
            'quantity' => intval($holding['quantity']),
            'avgPrice' => floatval($holding['avg_price']),
            'currentPrice' => floatval($current_price),
            'marketValue' => $marketValue,
            'investment' => $holding['quantity'] * $holding['avg_price'],
            'profitLoss' => $marketValue - ($holding['quantity'] * $holding['avg_price']),
            'priceChange' => floatval($holding['price_change'] ?? 0),
            'changePercent' => floatval($holding['change_percent'] ?? 0),
            'todayProfitLoss' => $holding['quantity'] * $holding['price_change']
        ];
    }
    
    // 計算權重
    foreach ($formattedHoldings as &$holding) {
        $holding['weight'] = $totalMarketValue > 0 ? ($holding['marketValue'] / $totalMarketValue) * 100 : 0;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedHoldings,
        'count' => count($formattedHoldings)
    ]);
}

function getPortfolioPerformance($conn, $user_id) {
    $days = intval($_GET['days'] ?? 30);
    
    $stmt = $conn->prepare("
        SELECT 
            DATE(t.created_at) as date,
            SUM(CASE WHEN t.type = 'buy' THEN t.quantity * t.price ELSE -t.quantity * t.price END) as daily_flow,
            SUM(CASE WHEN t.type = 'buy' THEN t.quantity * t.price ELSE -t.quantity * t.price END) as cumulative_flow
        FROM transactions t
        WHERE t.user_id = ? AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(t.created_at)
        ORDER BY date DESC
    ");
    $stmt->execute([$user_id, $days]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 計算每日投資組合價值
    $performance = [];
    $cumulativeFlow = 0;
    
    foreach ($transactions as $transaction) {
        $cumulativeFlow += floatval($transaction['daily_flow']);
        $performance[] = [
            'date' => $transaction['date'],
            'dailyFlow' => floatval($transaction['daily_flow']),
            'cumulativeFlow' => $cumulativeFlow
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_reverse($performance),
        'count' => count($performance)
    ]);
}

function getPortfolioAllocation($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            s.industry,
            SUM(p.quantity * s.current_price) as market_value,
            COUNT(*) as stock_count
        FROM portfolios p
        JOIN stocks s ON p.stock_code = s.code
        WHERE p.user_id = ?
        GROUP BY s.industry
        ORDER BY market_value DESC
    ");
    $stmt->execute([$user_id]);
    $allocation = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalValue = array_sum(array_column($allocation, 'market_value'));
    
    $formattedAllocation = array_map(function($item) use ($totalValue) {
        return [
            'industry' => $item['industry'],
            'marketValue' => floatval($item['market_value']),
            'stockCount' => intval($item['stock_count']),
            'percentage' => $totalValue > 0 ? (floatval($item['market_value']) / $totalValue) * 100 : 0
        ];
    }, $allocation);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedAllocation,
        'totalValue' => $totalValue
    ]);
}

function getPortfolioHistory($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            s.name as stock_name
        FROM transactions t
        JOIN stocks s ON t.stock_code = s.code
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'count' => count($transactions)
    ]);
}

function addToPortfolio($conn, $user_id) {
    $stockCode = $_POST['stock_code'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $avgPrice = floatval($_POST['avg_price'] ?? 0);
    
    if (empty($stockCode) || $quantity <= 0 || $avgPrice <= 0) {
        echo json_encode(['success' => false, 'message' => '參數不完整或無效']);
        return;
    }
    
    // 檢查股票是否存在
    $stmt = $conn->prepare("SELECT id FROM stocks WHERE code = ?");
    $stmt->execute([$stockCode]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '股票不存在']);
        return;
    }
    
    // 檢查是否已在投資組合中
    $stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // 更新現有持股
        $newQuantity = $existing['quantity'] + $quantity;
        $newAvgPrice = (($existing['quantity'] * $existing['avg_price']) + ($quantity * $avgPrice)) / $newQuantity;
        
        $stmt = $conn->prepare("
            UPDATE portfolios 
            SET quantity = ?, avg_price = ?, updated_at = NOW()
            WHERE user_id = ? AND stock_code = ?
        ");
        $stmt->execute([$newQuantity, $newAvgPrice, $user_id, $stockCode]);
    } else {
        // 新增持股
        $stmt = $conn->prepare("
            INSERT INTO portfolios (user_id, stock_code, quantity, avg_price, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $stockCode, $quantity, $avgPrice]);
    }
    
    // 記錄交易
    $stmt = $conn->prepare("
        INSERT INTO transactions (user_id, stock_code, type, quantity, price, created_at)
        VALUES (?, ?, 'buy', ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $stockCode, $quantity, $avgPrice]);
    
    echo json_encode(['success' => true, 'message' => '已加入投資組合']);
}

function buyStock($conn, $user_id) {
    addToPortfolio($conn, $user_id);
}

function sellStock($conn, $user_id) {
    $stockCode = $_POST['stock_code'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    
    if (empty($stockCode) || $quantity <= 0 || $price <= 0) {
        echo json_encode(['success' => false, 'message' => '參數不完整或無效']);
        return;
    }
    
    // 檢查持股
    $stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    $holding = $stmt->fetch();
    
    if (!$holding) {
        echo json_encode(['success' => false, 'message' => '未持有該股票']);
        return;
    }
    
    if ($holding['quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => '持股數量不足']);
        return;
    }
    
    // 更新持股
    $newQuantity = $holding['quantity'] - $quantity;
    
    if ($newQuantity > 0) {
        $stmt = $conn->prepare("
            UPDATE portfolios 
            SET quantity = ?, updated_at = NOW()
            WHERE user_id = ? AND stock_code = ?
        ");
        $stmt->execute([$newQuantity, $user_id, $stockCode]);
    } else {
        $stmt = $conn->prepare("DELETE FROM portfolios WHERE user_id = ? AND stock_code = ?");
        $stmt->execute([$user_id, $stockCode]);
    }
    
    // 記錄交易
    $stmt = $conn->prepare("
        INSERT INTO transactions (user_id, stock_code, type, quantity, price, created_at)
        VALUES (?, ?, 'sell', ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $stockCode, $quantity, $price]);
    
    echo json_encode(['success' => true, 'message' => '賣出成功']);
}

function updatePortfolio($conn, $user_id) {
    $stockCode = $_POST['stock_code'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $avgPrice = floatval($_POST['avg_price'] ?? 0);
    
    if (empty($stockCode) || $quantity <= 0 || $avgPrice <= 0) {
        echo json_encode(['success' => false, 'message' => '參數不完整或無效']);
        return;
    }
    
    $stmt = $conn->prepare("
        UPDATE portfolios 
        SET quantity = ?, avg_price = ?, updated_at = NOW()
        WHERE user_id = ? AND stock_code = ?
    ");
    $stmt->execute([$quantity, $avgPrice, $user_id, $stockCode]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '投資組合已更新']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失敗']);
    }
}

function removeFromPortfolio($conn, $user_id, $stockCode) {
    $stmt = $conn->prepare("DELETE FROM portfolios WHERE user_id = ? AND stock_code = ?");
    $stmt->execute([$user_id, $stockCode]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '已從投資組合中移除']);
    } else {
        echo json_encode(['success' => false, 'message' => '移除失敗']);
    }
}
?>
