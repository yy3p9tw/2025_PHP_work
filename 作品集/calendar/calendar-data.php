<?php
/**
 * 萬年曆數據配置文件
 * 包含節日、活動等數據
 */

// 節日數據
$holidays = [
    // 2025年節日
    '2025-01-01' => '元旦',
    '2025-01-29' => '農曆新年除夕',
    '2025-01-30' => '農曆新年初一',
    '2025-01-31' => '農曆新年初二',
    '2025-02-01' => '農曆新年初三',
    '2025-02-28' => '和平紀念日',
    '2025-04-04' => '兒童節',
    '2025-04-05' => '清明節',
    '2025-05-01' => '勞動節',
    '2025-05-11' => '母親節',
    '2025-06-08' => '父親節',
    '2025-06-23' => '端午節',
    '2025-09-15' => '中秋節',
    '2025-10-10' => '國慶日',
    '2025-10-31' => '萬聖節',
    '2025-12-25' => '聖誕節',
    
    // 可以添加更多年份的節日
    '2024-01-01' => '元旦',
    '2024-02-10' => '農曆新年',
    '2024-04-04' => '兒童節',
    '2024-04-05' => '清明節',
    '2024-05-01' => '勞動節',
    '2024-10-10' => '國慶日',
    '2024-12-25' => '聖誕節',
    
    '2026-01-01' => '元旦',
    '2026-02-17' => '農曆新年',
    '2026-04-05' => '清明節',
    '2026-05-01' => '勞動節',
    '2026-10-10' => '國慶日',
    '2026-12-25' => '聖誕節',
];

// 特殊活動/事件數據
$events = [
    // 國際文化節日
    '2025-01-22' => '草莓蛋糕之日',
    '2025-02-14' => '情人節',
    '2025-02-22' => '草莓蛋糕之日',
    '2025-03-08' => '國際婦女節',
    '2025-03-22' => '草莓蛋糕之日',
    '2025-04-01' => '愚人節',
    '2025-04-09' => '埃及聞風節',
    '2025-04-13' => '泰國潑水節',
    '2025-04-14' => '泰國潑水節',
    '2025-04-15' => '泰國潑水節',
    '2025-04-16' => '泰國潑水節',
    '2025-04-22' => '世界地球日',
    '2025-04-30' => '荷蘭女王節',
    '2025-05-06' => '可樂餅之日',
    '2025-05-22' => '草莓蛋糕之日',
    '2025-05-25' => '布丁之日',
    '2025-05-29' => '生日',
    '2025-06-22' => '草莓蛋糕之日',
    '2025-07-01' => '韓國保寧泥漿節',
    '2025-07-03' => '羅斯威爾UFO節',
    '2025-07-04' => '羅斯威爾UFO節',
    '2025-07-05' => '羅斯威爾UFO節',
    '2025-07-06' => '羅斯威爾UFO節',
    '2025-07-22' => '草莓蛋糕之日',
    '2025-08-22' => '草莓蛋糕之日',
    '2025-09-22' => '草莓蛋糕之日',
    '2025-10-22' => '草莓蛋糕之日',
    '2025-10-31' => '墨西哥亡靈節',
    '2025-11-01' => '墨西哥亡靈節',
    '2025-11-22' => '草莓蛋糕之日',
    '2025-12-22' => '草莓蛋糕之日',
    '2025-12-31' => '跨年夜',
];

/**
 * 獲取指定月份的日曆數據
 */
function getCalendarData($year, $month) {
    global $holidays, $events;
    
    $firstDay = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
    $lastDay = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));
    $firstDayWeek = date("w", strtotime($firstDay));
    $daysInMonth = date("t", strtotime($firstDay));
    
    $calendarData = [];
    
    // 添加前一個月的空白天數
    for ($i = 0; $i < $firstDayWeek; $i++) {
        $calendarData[] = null;
    }
    
    // 添加當月的天數
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $dayData = [
            'date' => $currentDate,
            'day' => $day,
            'weekday' => date('w', strtotime($currentDate)),
            'weekOfYear' => date('W', strtotime($currentDate)),
            'holiday' => $holidays[$currentDate] ?? null,
            'event' => $events[$currentDate] ?? null,
            'isToday' => $currentDate === date('Y-m-d'),
            'isWeekend' => in_array(date('w', strtotime($currentDate)), [0, 6])
        ];
        $calendarData[] = $dayData;
    }
    
    return $calendarData;
}

/**
 * 獲取特定日期的詳細信息
 */
function getDayDetail($date) {
    global $holidays, $events;
    
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return null;
    }
    
    return [
        'date' => $date,
        'formatted_date' => date('Y年m月d日', $timestamp),
        'weekday' => ['日', '一', '二', '三', '四', '五', '六'][date('w', $timestamp)],
        'weekOfYear' => date('W', $timestamp),
        'holiday' => $holidays[$date] ?? null,
        'event' => $events[$date] ?? null,
        'isToday' => $date === date('Y-m-d'),
        'dayOfYear' => date('z', $timestamp) + 1,
        'lunarInfo' => getLunarInfo($date) // 可擴展農曆功能
    ];
}

/**
 * 獲取農曆信息（簡化版本）
 */
function getLunarInfo($date) {
    // 這裡可以集成農曆轉換庫
    // 暫時返回簡單信息
    return [
        'lunar_date' => '農曆待開發',
        'zodiac' => getZodiac($date),
        'constellation' => getConstellation($date)
    ];
}

/**
 * 獲取生肖
 */
function getZodiac($date) {
    $year = date('Y', strtotime($date));
    $zodiacs = ['猴', '雞', '狗', '豬', '鼠', '牛', '虎', '兔', '龍', '蛇', '馬', '羊'];
    return $zodiacs[$year % 12];
}

/**
 * 獲取星座
 */
function getConstellation($date) {
    $month = date('n', strtotime($date));
    $day = date('j', strtotime($date));
    
    $constellations = [
        '魔羯座', '水瓶座', '雙魚座', '牡羊座', '金牛座', '雙子座',
        '巨蟹座', '獅子座', '處女座', '天秤座', '天蠍座', '射手座'
    ];
    
    $dates = [
        [1, 20], [2, 19], [3, 21], [4, 20], [5, 21], [6, 21],
        [7, 23], [8, 23], [9, 23], [10, 23], [11, 22], [12, 22]
    ];
    
    $index = $month - 1;
    if ($day < $dates[$index][1]) {
        $index = ($index - 1 + 12) % 12;
    }
    
    return $constellations[$index];
}

// API 端點處理
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    switch ($_GET['api']) {
        case 'calendar':
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            echo json_encode(getCalendarData($year, $month));
            break;
            
        case 'day-detail':
            $date = $_GET['date'] ?? date('Y-m-d');
            echo json_encode(getDayDetail($date));
            break;
            
        default:
            echo json_encode(['error' => 'Invalid API endpoint']);
    }
    exit;
}
?>
