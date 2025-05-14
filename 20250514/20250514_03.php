<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日曆</title>
    <style>
        <?php
        // 定義滑鼠懸停效果的背景色和文字顏色
        $hoverBackgroundColor = '#e0f7fa';
        $hoverTextColor = '#007bff';
        ?>
        body {
            font-family: Arial, sans-serif; /* 使用 Arial 字體，若不可用則使用系統預設無襯線字體 */
            background-color: #f5f5f5; /* 設置頁面背景色為淺灰色 */
            margin: 20px; /* 設置頁面外邊距為 20px */
        }

        h2 {
            text-align: center; /* 標題居中 */
            color: #1e90ff; /* 標題顏色為藍色 */
            font-size: 1.8em; /* 標題字體大小 */
            margin-bottom: 20px; /* 標題下方間距 */
        }

        table {
            width: 70%; /* 表格寬度占頁面 70% */
            border-collapse: collapse; /* 合併表格邊框 */
            margin: 20px auto; /* 表格上下外邊距 20px，左右自動居中 */
            background: white; /* 表格背景色為白色 */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* 添加輕微陰影效果 */
            border-radius: 8px; /* 表格圓角 */
            overflow: hidden; /* 確保圓角生效 */
        }

        th, td {
            border: 1px solid #1e90ff; /* 表格單元格邊框為藍色 */
            text-align: center; /* 內容居中 */
            padding: 10px; /* 單元格內邊距 */
            font-size: 1em; /* 字體大小 */
        }

        th {
            background-color: #1e90ff; /* 表頭背景色為藍色 */
            color: white; /* 表頭文字為白色 */
            font-weight: bold; /* 表頭文字加粗 */
        }

        td {
            transition: all 0.3s ease; /* 滑鼠懸停時平滑過渡效果 */
        }

        td:hover {
            background-color: <?php echo $hoverBackgroundColor; ?>; /* 滑鼠懸停時背景色 */
            color: <?php echo $hoverTextColor; ?>; /* 滑鼠懸停時文字顏色 */
            cursor: pointer; /* 滑鼠懸停時顯示手形光標 */
        }

        .today {
            background-color: #ffff99; /* 當天背景色為淺黃色 */
            font-weight: bold; /* 當天文字加粗 */
        }



        .holiday {
            background-color: #ffcccc; /* 週末背景色為淺紅色（粉紅色） */
            color: #333; /* 週末文字顏色 */
        }

        .pass-date {
            color: #aaa; /* 過去日期文字為淺灰色 */
        }
        .other-month {
            background-color: #e0e0e0; /* 非本月日期背景色為灰色 */
            color: #aaa; /* 非本月日期文字為淺灰色 */
        }
        .festival {
            color: #ff0000; /* 假日文字顏色為紅色 */
        }

        .nav-buttons {
            text-align: center; /* 導航按鈕居中 */
            margin-bottom: 20px; /* 導航按鈕下方間距 */
        }

        .nav-buttons a {
            display: inline-block; /* 按鈕為行內塊元素 */
            padding: 10px 20px; /* 按鈕內邊距 */
            margin: 0 10px; /* 按鈕間距 */
            background-color: #1e90ff; /* 按鈕背景色 */
            color: white; /* 按鈕文字顏色 */
            text-decoration: none; /* 移除下劃線 */
            border-radius: 5px; /* 按鈕圓角 */
            transition: background-color 0.3s ease; /* 按鈕懸停過渡效果 */
        }

        .nav-buttons a:hover {
            background-color: #007bff; /* 按鈕懸停時背景色 */
        }

        @media (max-width: 480px) {
            table {
                width: 95%; /* 小螢幕時表格寬度占 95% */
            }

            th, td {
                padding: 8px; /* 小螢幕時單元格內邊距減小 */
                font-size: 0.9em; /* 小螢幕時字體稍小 */
            }

            .nav-buttons a {
                padding: 8px 15px; /* 小螢幕時按鈕內邊距減小 */
                margin: 0 5px; /* 小螢幕時按鈕間距減小 */
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

        // 勞動節：每年 5 月 1 日
        $festivals[date("Y-m-d", strtotime("$year-05-01"))] = '勞動節';

        // 母親節：5 月第二個星期日
        $firstDayOfMay = strtotime("$year-05-01");
        $firstSunday = strtotime("next Sunday", $firstDayOfMay);
        $mothersDay = strtotime("+7 days", $firstSunday);
        $festivals[date("Y-m-d", $mothersDay)] = '母親節';

        // 端午節：農曆五月初五（簡化方法，使用已知日期）
        // 2025 年端午節為 6 月 14 日（根據農曆推算）
        if ($year == 2025) {
            $festivals['2025-06-14'] = '端午節';
        }
        // 為未來年份提供示例（需實際農曆計算）
        // 2026 年端午節約為 6 月 2 日（僅示例，需驗證）
        if ($year == 2026) {
            $festivals['2026-06-02'] = '端午節';
        }

        // 注意：動態計算農曆假日（如端午節）需使用農曆庫
        // 推薦使用 Composer 安裝 'pcdream/chinese-lunar-calendar'：
        // require 'vendor/autoload.php';
        // use Overtrue\ChineseCalendar\Calendar;
        // $calendar = new Calendar();
        // $dragonBoatDate = $calendar->lunar2solar($year, 5, 5);
        // $festivals[$dragonBoatDate['date']] = '端午節';

        // 可添加更多假日，例如：
        // $festivals[date("Y-m-d", strtotime("$year-01-01"))] = '元旦';
        // 中秋節（農曆八月十五）：需類似端午節的農曆轉換

        return $festivals;
    }

    // 獲取當前日期
    $today = date("Y-m-d");

    // 從查詢參數獲取年份和月份，若無則使用當前年月
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

    <!-- 顯示導航按鈕 -->
    <div class="nav-buttons">
        <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>">上一月</a>
        <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>">下一月</a>
    </div>

    <!-- 顯示當前年月 -->
    <h2><?php echo date("Y 年 m 月", strtotime("$year-$month-01")); ?></h2>

    <!-- 開始繪製日曆表格 -->
    <table>
        <!-- 表頭顯示星期 -->
        <tr>
            <th>日</th>
            <th>一</th>
            <th>二</th>
            <th>三</th>
            <th>四</th>
            <th>五</th>
            <th>六</th>
        </tr>
        <?php
        // 最多顯示 6 行（確保所有日期都能顯示）
        for ($i = 0; $i < 6; $i++) {
            echo "<tr>";
            // 每行顯示 7 天
            for ($j = 0; $j < 7; $j++) {
                // 計算當前單元格的日期偏移量
                $day = $j + ($i * 7) - $firstDayWeek;
                $timestamp = strtotime("$day days", strtotime($firstDay));
                $date = date("Y-m-d", $timestamp);
                $class = "";

                // 獲取星期值（1=星期一，6=星期六，7=星期日）
                $dayOfWeek = date("N", $timestamp);
                // 檢查是否為週末（僅星期六或星期日）
                if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                    $class .= " holiday";
                }

                // 檢查是否為當天
                if ($today == $date) {
                    $class .= " today";
                }

                // 檢查是否為非本月日期（包括五月31天後）
                if (date("m", $timestamp) != date("m", strtotime($firstDay))) {
                    $class .= " other-month";
                }

                // 檢查是否為過去日期
                if ($timestamp < strtotime($today)) {
                    $class .= " pass-date";
                }

                // 檢查是否為假日
                if (array_key_exists($date, $festivals)) {
                    $class .= " festival";
                }

                // 輸出單元格
                echo "<td class='$class' data-date='$date'>";
                // 只顯示日期數字（不顯示前導零）
                echo date("j", $timestamp);
                echo "</td>";
            }
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>