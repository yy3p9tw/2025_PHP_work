<?php

/**
 * 通用函數庫
 */

// 輸出 JSON 回應
function jsonResponse($data, $success = true, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// 輸出錯誤回應
function jsonError($message, $code = 400) {
    http_response_code($code);
    jsonResponse(null, false, $message);
}

// 清理輸入數據
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// 驗證電子郵件
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 生成隨機字符串
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// 格式化金額
function formatCurrency($amount, $symbol = '$') {
    return $symbol . number_format($amount, 2);
}

// 格式化百分比
function formatPercentage($value) {
    $sign = $value >= 0 ? '+' : '';
    return $sign . number_format($value, 2) . '%';
}

// 計算百分比變化
function calculatePercentageChange($oldValue, $newValue) {
    if ($oldValue == 0) return 0;
    return (($newValue - $oldValue) / $oldValue) * 100;
}

// 格式化日期時間
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($datetime));
}

// 獲取股票顏色類別（根據漲跌）
function getStockColorClass($change) {
    if ($change > 0) return 'text-success';
    if ($change < 0) return 'text-danger';
    return 'text-muted';
}

// 檢查檔案上傳錯誤
function checkUploadError($file) {
    if (!isset($file['error'])) {
        return '上傳失敗';
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            return null;
        case UPLOAD_ERR_NO_FILE:
            return '沒有選擇檔案';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return '檔案太大';
        default:
            return '上傳錯誤';
    }
}

// 模擬股票數據（用於測試）
function getSimulatedStockData($symbol) {
    $stocks = [
        '2330' => [
            'symbol' => '2330',
            'name' => '台積電',
            'price' => 485.50,
            'change' => 2.5,
            'volume' => 1250000
        ],
        '2317' => [
            'symbol' => '2317',
            'name' => '鴻海',
            'price' => 95.80,
            'change' => -1.2,
            'volume' => 850000
        ],
        '2454' => [
            'symbol' => '2454',
            'name' => '聯發科',
            'price' => 680.00,
            'change' => 3.1,
            'volume' => 650000
        ],
        'AAPL' => [
            'symbol' => 'AAPL',
            'name' => '蘋果公司',
            'price' => 150.25,
            'change' => 1.8,
            'volume' => 2500000
        ],
        'GOOGL' => [
            'symbol' => 'GOOGL',
            'name' => '谷歌',
            'price' => 2350.75,
            'change' => -0.9,
            'volume' => 180000
        ]
    ];
    
    return $stocks[strtoupper($symbol)] ?? null;
}

// 生成模擬新聞數據
function getSimulatedNews() {
    return [
        [
            'id' => 1,
            'title' => '台積電Q4財報超預期，股價創新高',
            'content' => '台積電公布第四季財報，營收和獲利均超越市場預期...',
            'published_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'source' => '財經日報'
        ],
        [
            'id' => 2,
            'title' => '美股三大指數收漲，科技股表現亮眼',
            'content' => '美股昨夜收盤，道瓊、標普500、納斯達克均收漲...',
            'published_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            'source' => '經濟時報'
        ],
        [
            'id' => 3,
            'title' => 'AI概念股持續熱炒，投資人關注度高',
            'content' => '人工智慧相關概念股持續受到市場關注...',
            'published_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
            'source' => '投資週刊'
        ]
    ];
}

// 獲取市場指數資料
function getMarketIndices($conn = null) {
    if ($conn === null) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    
    // 獲取市場指數資料
    $stmt = $conn->prepare("
        SELECT code, name, current_value, change_value, change_percent, updated_at
        FROM market_indices 
        WHERE status = 'active'
        ORDER BY display_order, code
    ");
    $stmt->execute();
    $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 如果資料庫中沒有資料，使用模擬資料
    if (empty($indices)) {
        $indices = getSimulatedMarketIndices();
    } else {
        // 格式化資料
        $indices = array_map(function($index) {
            return [
                'code' => $index['code'],
                'name' => $index['name'],
                'value' => (float)$index['current_value'],
                'change' => (float)$index['change_value'],
                'change_percent' => (float)$index['change_percent'],
                'updated_at' => $index['updated_at']
            ];
        }, $indices);
    }
    
    return $indices;
}

// 獲取模擬市場指數資料
function getSimulatedMarketIndices() {
    return [
        [
            'code' => 'TAIEX',
            'name' => '台灣加權指數',
            'value' => 17250.48,
            'change' => 125.30,
            'change_percent' => 0.73,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'code' => 'TPEx',
            'name' => '櫃買指數',
            'value' => 185.67,
            'change' => 2.45,
            'change_percent' => 1.34,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'code' => 'ELECTRONIC',
            'name' => '電子指數',
            'value' => 850.23,
            'change' => 12.80,
            'change_percent' => 1.53,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'code' => 'FINANCE',
            'name' => '金融指數',
            'value' => 1650.45,
            'change' => -8.20,
            'change_percent' => -0.49,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'code' => 'SEMICONDUCTOR',
            'name' => '半導體指數',
            'value' => 580.78,
            'change' => 15.60,
            'change_percent' => 2.76,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'code' => 'BIOTECH',
            'name' => '生技指數',
            'value' => 320.12,
            'change' => -4.30,
            'change_percent' => -1.33,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];
}

?>
