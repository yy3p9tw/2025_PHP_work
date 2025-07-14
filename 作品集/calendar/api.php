<?php
/**
 * 簡化的萬年曆 API
 */

// 確保正確的 JSON 響應
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 引入數據配置
include_once 'calendar-data.php';

// 處理 API 請求
if (isset($_GET['api'])) {
    try {
        switch ($_GET['api']) {
            case 'calendar':
                $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
                $data = getCalendarData($year, $month);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'day-detail':
                $date = $_GET['date'] ?? date('Y-m-d');
                $data = getDayDetail($date);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'holidays':
                echo json_encode($holidays, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'events':
                echo json_encode($events, JSON_UNESCAPED_UNICODE);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid API endpoint'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 如果不是 API 請求，返回錯誤
http_response_code(404);
echo json_encode(['error' => 'API endpoint not found'], JSON_UNESCAPED_UNICODE);
?>
