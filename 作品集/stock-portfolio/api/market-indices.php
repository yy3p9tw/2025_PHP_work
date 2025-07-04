<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $action);
            break;
        case 'POST':
            handlePostRequest($conn, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '方法不被允許']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
}

function handleGetRequest($conn, $action) {
    switch ($action) {
        case 'history':
            getIndexHistory($conn);
            break;
        case 'compare':
            compareIndices($conn);
            break;
        default:
            // 調用 functions.php 中的 getMarketIndices 函數
            $indices = getMarketIndices($conn);
            
            echo json_encode([
                'success' => true,
                'indices' => $indices,
                'count' => count($indices),
                'lastUpdated' => date('Y-m-d H:i:s')
            ]);
    }
}

function handlePostRequest($conn, $action) {
    switch ($action) {
        case 'update':
            updateMarketIndices($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
}

function getIndexHistory($conn) {
    $code = $_GET['code'] ?? '';
    $days = intval($_GET['days'] ?? 30);
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => '指數代碼不能為空']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT date, open_value, high_value, low_value, close_value, volume
        FROM market_index_history 
        WHERE index_code = ? AND date >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY date DESC
    ");
    $stmt->execute([$code, $days]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($history)) {
        // 生成模擬歷史資料
        $history = generateSimulatedIndexHistory($code, $days);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $history,
        'count' => count($history),
        'indexCode' => $code
    ]);
}

function compareIndices($conn) {
    $codes = $_GET['codes'] ?? '';
    $days = intval($_GET['days'] ?? 30);
    
    if (empty($codes)) {
        echo json_encode(['success' => false, 'message' => '指數代碼不能為空']);
        return;
    }
    
    $codeArray = explode(',', $codes);
    $comparison = [];
    
    foreach ($codeArray as $code) {
        $code = trim($code);
        if (empty($code)) continue;
        
        $stmt = $conn->prepare("
            SELECT date, close_value
            FROM market_index_history 
            WHERE index_code = ? AND date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY date ASC
        ");
        $stmt->execute([$code, $days]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($history)) {
            $history = generateSimulatedIndexHistory($code, $days);
        }
        
        $comparison[$code] = [
            'name' => getIndexName($code),
            'history' => $history,
            'performance' => calculatePerformance($history)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $comparison,
        'period' => $days . ' days'
    ]);
}

function updateMarketIndices($conn) {
    // 這裡應該實作從外部API獲取最新指數的邏輯
    // 目前使用模擬資料
    $indices = getSimulatedMarketIndices();
    
    foreach ($indices as $index) {
        $stmt = $conn->prepare("
            INSERT INTO market_indices (code, name, current_value, change_value, change_percent, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                current_value = VALUES(current_value),
                change_value = VALUES(change_value),
                change_percent = VALUES(change_percent),
                updated_at = VALUES(updated_at)
        ");
        $stmt->execute([
            $index['code'],
            $index['name'],
            $index['value'],
            $index['change'],
            $index['changePercent']
        ]);
        
        // 記錄歷史資料
        $stmt = $conn->prepare("
            INSERT INTO market_index_history (index_code, date, open_value, high_value, low_value, close_value, volume)
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                high_value = GREATEST(high_value, VALUES(high_value)),
                low_value = LEAST(low_value, VALUES(low_value)),
                close_value = VALUES(close_value),
                volume = VALUES(volume)
        ");
        $stmt->execute([
            $index['code'],
            $index['value'],
            $index['value'] * 1.005,
            $index['value'] * 0.995,
            $index['value'],
            rand(1000000, 10000000)
        ]);
    }
    
    echo json_encode(['success' => true, 'message' => '市場指數已更新']);
}

function generateSimulatedIndexHistory($code, $days) {
    $history = [];
    $baseValue = getBaseIndexValue($code);
    $currentValue = $baseValue;
    
    for ($i = $days; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $volatility = 0.02; // 2% 波動率
        
        $change = $currentValue * $volatility * (rand(-100, 100) / 100);
        $openValue = $currentValue;
        $closeValue = $currentValue + $change;
        $highValue = max($openValue, $closeValue) * (1 + rand(0, 50) / 10000);
        $lowValue = min($openValue, $closeValue) * (1 - rand(0, 50) / 10000);
        
        $history[] = [
            'date' => $date,
            'open_value' => round($openValue, 2),
            'high_value' => round($highValue, 2),
            'low_value' => round($lowValue, 2),
            'close_value' => round($closeValue, 2),
            'volume' => rand(1000000, 10000000)
        ];
        
        $currentValue = $closeValue;
    }
    
    return array_reverse($history);
}

function getBaseIndexValue($code) {
    $baseValues = [
        'TAIEX' => 17500,
        'TPEx' => 185,
        'ELECTRONIC' => 850,
        'FINANCE' => 1650,
        'SEMICONDUCTOR' => 580,
        'BIOTECH' => 320
    ];
    
    return $baseValues[$code] ?? 1000;
}

function getIndexName($code) {
    $names = [
        'TAIEX' => '加權指數',
        'TPEx' => '櫃買指數',
        'ELECTRONIC' => '電子指數',
        'FINANCE' => '金融指數',
        'SEMICONDUCTOR' => '半導體指數',
        'BIOTECH' => '生技指數'
    ];
    
    return $names[$code] ?? $code;
}

function calculatePerformance($history) {
    if (empty($history)) {
        return [
            'totalReturn' => 0,
            'totalReturnPercent' => 0,
            'volatility' => 0,
            'maxDrawdown' => 0
        ];
    }
    
    $firstValue = floatval($history[0]['close_value']);
    $lastValue = floatval(end($history)['close_value']);
    $totalReturn = $lastValue - $firstValue;
    $totalReturnPercent = $firstValue > 0 ? ($totalReturn / $firstValue) * 100 : 0;
    
    // 計算波動率
    $returns = [];
    for ($i = 1; $i < count($history); $i++) {
        $prevValue = floatval($history[$i-1]['close_value']);
        $currValue = floatval($history[$i]['close_value']);
        if ($prevValue > 0) {
            $returns[] = ($currValue - $prevValue) / $prevValue;
        }
    }
    
    $volatility = 0;
    if (count($returns) > 1) {
        $meanReturn = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(function($x) use ($meanReturn) {
            return pow($x - $meanReturn, 2);
        }, $returns)) / (count($returns) - 1);
        $volatility = sqrt($variance) * sqrt(252) * 100; // 年化波動率
    }
    
    // 計算最大回撤
    $maxDrawdown = 0;
    $peak = floatval($history[0]['close_value']);
    
    foreach ($history as $record) {
        $value = floatval($record['close_value']);
        if ($value > $peak) {
            $peak = $value;
        } else {
            $drawdown = ($peak - $value) / $peak * 100;
            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }
    }
    
    return [
        'totalReturn' => $totalReturn,
        'totalReturnPercent' => $totalReturnPercent,
        'volatility' => $volatility,
        'maxDrawdown' => $maxDrawdown
    ];
}
?>
