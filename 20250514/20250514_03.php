<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日曆</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif; /* Google 日曆字體 */
            background-color: #f5f5f5; /* 淺灰背景 */
            margin: 20px;
            color: #202124; /* Google 深灰文字 */
        }

        h2 {
            text-align: center;
            color: #1a73e8; /* Google 藍色 */
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .calendar {
            width: 70%;
            margin: 20px auto;
            background: #fff; /* 白色背景 */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); /* 陰影 */
            border-radius: 8px;
            overflow: hidden;
            display: grid;
            grid-template-columns: repeat(7, 1fr); /* 7 列 */
        }

        .header, .day {
            border: 1px solid #dadce0; /* Google 邊框色 */
            padding: 12px;
            text-align: center;
            box-sizing: border-box;
        }

        .header {
            background-color: #1a73e8; /* 藍色表頭 */
            color: #fff;
            font-weight: 500;
            font-size: 1em;
        }

        .day {
            min-height: 80px; /* 固定格子高度 */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
            font-size: 1em;
        }

        .day:hover {
            background-color: #e8f0fe; /* 懸停高亮 */
            cursor: pointer;
        }

        .today {
            background-color: #fff8b4; /* 當天淺黃色 */
            font-weight: bold;
        }

        .other-month {
            background-color: #f1f3f4; /* 非本月灰色 */
            color: #5f6368;
        }

        .holiday {
            background-color: #fce8e6; /* 週末粉紅色 */
            color: #202124;
        }

        .pass-date {
            color: #5f6368; /* 過去日期淺灰 */
        }

        .festival {
            color: #d93025; /* 假日紅色 */
            font-weight: bold;
        }

        .nav-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-buttons a {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #1a73e8; /* 藍色按鈕 */
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        .nav-buttons a:hover {
            background-color: #1557b0; /* 按鈕懸停深藍 */
        }

        @media (max-width: 480px) {
            .calendar {
                width: 95%;
            }

            .header, .day {
                padding: 8px;
                font-size: 0.85em;
            }

            .day {
                min-height: 50px;
            }

            .nav-buttons a {
                padding: 8px 15px;
                margin: 0 5px;
            }

            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <?php
    // 設置時區為台北，確保日期計算正確
    date_default_timezone_set('Asia/Taipei');

    // 函數：計算指定年份的假日
    function calculateFestivals($year) {
        $festivals = [];

        // 勞動節：5 月 1 日
        $festivals[date("Y-m-d", strtotime("$year-05-01"))] = '勞動節';

        // 母親節：5 月第二個星期日
        $firstDayOfMay = strtotime("$year-05-01");
        $firstSunday = strtotime("next Sunday", $firstDayOfMay);
        $mothersDay = strtotime("+7 days", $firstSunday);
        $festivals[date("Y-m-d", $mothersDay)] = '母親節';

        // 端午節：農曆五月初五（簡化，使用已知日期）
        // 2025 年為 6 月 14 日
        if ($year == 2025) {
            $festivals['2025-06-14'] = '端午節';
        }
        // 2026 年示例為 6 月 2 日（需驗證）
        if ($year == 2026) {
            $festivals['2026-06-02'] = '端午節';
        }

        // 元旦：1 月 1 日
        $festivals[date("Y-m-d", strtotime("$year-01-01"))] = '元旦';

        // 農曆假日進階實現：
        // 使用 Composer 安裝 'pcdream/chinese-lunar-calendar'：
        // composer require pcdream/chinese-lunar-calendar
        /*
        require 'vendor/autoload.php';
        use Overtrue\ChineseCalendar\Calendar;
        $calendar = new Calendar();
        $dragonBoatDate = $calendar->lunar2solar($year, 5, 5);
        $festivals[$dragonBoatDate['date']] = '端午節';
        $midAutumnDate = $calendar->lunar2solar($year, 8, 15);
        $festivals[$midAutumnDate['date']] = '中秋節';
        */

        return $festivals;
    }

    // 獲取當前日期
    $today = date("Y-m-d");

    // 從查詢參數獲取年份和月份
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
    $month = isset($_GET['month']) ? intval($_GET['month']) : date("m");

    // 確保月份在 1-12 之間
    if ($month < 1) {
        $month = 12;
        $year--;
    } elseif ($month > 12) {
        $month = 1;
        $year++;
    }

    // 計算當前年份的假日
    $festivals = calculateFestivals($year);

    // 計算當前月份的第一天
    $firstDay = date("Y-m-01", strtotime("$year-$month-01"));
    // 計算第一天是星期幾（0=星期日，6=星期六）
    $firstDayWeek = date("w", strtotime($firstDay));
    // 計算當前月份的天數
    $theDaysOfMonth = date("t", strtotime($firstDay));

    // 計算上個月和下個月的年份與月份
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    ?>

    <!-- 導航按鈕 -->
    <div class="nav-buttons">
        <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>">上一月</a>
        <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>">下一月</a>
    </div>

    <!-- 顯示當前年月 -->
    <h2><?php echo date("Y 年 m 月", strtotime("$year-$month-01")); ?></h2>

    <!-- 日曆容器 -->
    <div class="calendar">
        <!-- 星期表頭 -->
        <div class="header">日</div>
        <div class="header">一</div>
        <div class="header">二</div>
        <div class="header">三</div>
        <div class="header">四</div>
        <div class="header">五</div>
        <div class="header">六</div>

        <?php
        // 最多 6 行
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 7; $j++) {
                // 計算日期偏移量
                $day = $j + ($i * 7) - $firstDayWeek;
                $timestamp = strtotime("$day days", strtotime($firstDay));
                $date = date("Y-m-d", $timestamp);
                $class = "day";

                // 星期判斷
                $dayOfWeek = date("N", $timestamp);
                if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                    $class .= " holiday";
                }

                // 當天
                if ($today == $date) {
                    $class .= " today";
                }

                // 非本月
                if (date("m", $timestamp) != date("m", strtotime($firstDay))) {
                    $class .= " other-month";
                }

                // 過去日期
                if ($timestamp < strtotime($today)) {
                    $class .= " pass-date";
                }

                // 假日
                $title = "";
                if (array_key_exists($date, $festivals)) {
                    $class .= " festival";
                    $title = " title='{$festivals[$date]}'";
                }

                // 輸出日期格子
                echo "<div class='$class' data-date='$date'$title>";
                echo date("j", $timestamp);
                echo "</div>";
            }
        }
        ?>
    </div>
</body>
</html>