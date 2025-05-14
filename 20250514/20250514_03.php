<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日曆</title>
    <style>
        /* 全局樣式，模仿 Google 日曆清爽設計 */
        body {
            font-family: 'Roboto', Arial, sans-serif;
            /* Google 字體，回退到 Arial */
            background-color: #f5f5f5;
            /* 淺灰背景 */
            margin: 20px;
            color: #202124;
            /* Google 深灰文字 */
        }

        h2 {
            text-align: center;
            color: #1a73e8;
            /* Google 藍色 */
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* 日曆容器，使用 CSS Grid 實現 7 列格子佈局 */
        .calendar {
            width: 70%;
            margin: 20px auto;
            background: #fff;
            /* 白色背景 */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            /* 輕微陰影 */
            border-radius: 8px;
            overflow: hidden;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* 7 列，每列等寬 */
        }

        /* 星期表頭和日期格子通用樣式 */
        .header,
        .day {
            border: 1px solid #dadce0;
            /* Google 邊框色 */
            padding: 12px;
            text-align: center;
            box-sizing: border-box;
        }

        /* 表頭樣式 */
        .header {
            background-color: #1a73e8;
            /* 藍色表頭 */
            color: #fff;
            font-weight: 500;
            font-size: 1em;
        }

        /* 日期格子樣式 */
        .day {
            min-height: 80px;
            /* 固定高度，確保格子一致 */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
            font-size: 1em;
        }

        .day:hover {
            background-color: #e8f0fe;
            /* 懸停時 Google 淺藍高亮 */
            cursor: pointer;
        }

        /* 當天高亮 */
        .today {
            background-color: #fff8b4;
            /* 淺黃色背景 */
            font-weight: bold;
        }









        /* 導航按鈕 */
        .nav-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-buttons a {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #1a73e8;
            /* 藍色按鈕 */
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        .nav-buttons a:hover {
            background-color: #1557b0;
            /* 懸停深藍 */
        }

        /* 過去日期 */
        .pass-date {
            color: #5f6368;
            /* 淺灰文字 */
        }

        /* 非本月日期 */


                /* 假日 */


                /* 週末（星期六和星期日） */
        .holiday {
            background-color: #fce8e6;
            /* 粉紅色背景 */
            color: #202124;
        }

                .other-month {
            background-color: #f1f3f4;
            /* 灰色背景 */
            color: #5f6368;
            /* 淺灰文字 */
        }
                .festival {
            color: #d93025;
            /* 紅色文字 */
            font-weight: bold;
        }

        /* 響應式設計，適應小螢幕 */
        @media (max-width: 480px) {
            .calendar {
                width: 95%;
            }

            .header,
            .day {
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
    // ---------------- 區塊 1：設置與初始化 ----------------
    // 設置時區為台北，確保日期計算一致
    date_default_timezone_set('Asia/Taipei');

    // 獲取當前日期，用於標記「今天」
    $today = date("Y-m-d");

    // 從 GET 參數獲取年份和月份，預設為當前年月
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
    $month = isset($_GET['month']) ? intval($_GET['month']) : date("m");

    // 確保月份在 1-12 之間，處理跨年邏輯
    if ($month < 1) {
        $month = 12;
        $year--;
    } elseif ($month > 12) {
        $month = 1;
        $year++;
    }

    // ---------------- 區塊 2：假日計算 ----------------
    /**
     * 計算指定年份的假日，返回日期與名稱的關聯陣列
     * @param int $year 年份
     * @return array 假日陣列，鍵為 Y-m-d，值為假日名稱
     */
    function calculateFestivals($year)
    {
        // 定義假日資料：固定日期、動態日期、農曆日期
        $festivalData = [
            'fixed' => [
                ['date' => "$year-05-01", 'name' => '勞動節'],
                ['date' => "$year-01-01", 'name' => '元旦'],
            ],
            'dynamic' => [
                'mothers_day' => [
                    'month' => 5,          // 5 月
                    'week' => 2,           // 第二週
                    'weekday' => 'Sunday', // 星期日
                    'name' => '母親節'
                ],
            ],
            'lunar' => [
                ['year' => 2025, 'date' => '2025-06-14', 'name' => '端午節'],
                ['year' => 2026, 'date' => '2026-06-02', 'name' => '端午節'], // 示例，需驗證
            ],
        ];

        $festivals = [];

        // 處理固定日期假日
        foreach ($festivalData['fixed'] as $festival) {
            $festivals[$festival['date']] = $festival['name'];
        }

        // 處理動態假日（如母親節）
        foreach ($festivalData['dynamic'] as $key => $festival) {
            $firstDayOfMonth = strtotime("$year-{$festival['month']}-01");
            $firstWeekday = strtotime("next {$festival['weekday']}", $firstDayOfMonth);
            $targetDate = strtotime("+" . ($festival['week'] - 1) . " weeks", $firstWeekday);
            $festivals[date("Y-m-d", $targetDate)] = $festival['name'];
        }


        // 處理農曆假日（簡化，使用已知日期）
        foreach ($festivalData['lunar'] as $festival) {
            if ($festival['year'] == $year) {
                $festivals[$festival['date']] = $festival['name'];
            }
        }

        // 農曆假日進階實現（需安裝庫）：
        /*
        // 使用 Composer 安裝：composer require pcdream/chinese-lunar-calendar
        require 'vendor/autoload.php';
        use Overtrue\ChineseCalendar\Calendar;
        $calendar = new Calendar();
        $dragonBoatDate = $calendar->lunar2solar($year, 5, 5); // 農曆五月初五
        $festivals[$dragonBoatDate['date']] = '端午節';
        $midAutumnDate = $calendar->lunar2solar($year, 8, 15); // 農曆八月十五
        $festivals[$midAutumnDate['date']] = '中秋節';
        */

        return $festivals;
    }

    // 計算當前年份的假日
    $festivals = calculateFestivals($year);

    // ---------------- 區塊 3：日期計算 ----------------
    // 計算當前月份的第一天
    $firstDay = date("Y-m-01", strtotime("$year-$month-01"));

    // 計算第一天是星期幾（0=星期日，6=星期六）
    $firstDayWeek = date("w", strtotime($firstDay));

    // 計算當前月份的天數
    $theDaysOfMonth = date("t", strtotime($firstDay));

    // 計算導航用的上個月和下個月
    $navMonths = [
        'prev' => ['month' => $month - 1, 'year' => $year],
        'next' => ['month' => $month + 1, 'year' => $year],
    ];
    if ($navMonths['prev']['month'] < 1) {
        $navMonths['prev']['month'] = 12;
        $navMonths['prev']['year']--;
    }
    if ($navMonths['next']['month'] > 12) {
        $navMonths['next']['month'] = 1;
        $navMonths['next']['year']++;
    }

    // ---------------- 區塊 4：HTML 輸出 ----------------
    // 定義星期名稱陣列
    $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
    ?>

    <!-- 導航按鈕 -->
    <div class="nav-buttons">
        <a href="?year=<?php echo $navMonths['prev']['year']; ?>&month=<?php echo $navMonths['prev']['month']; ?>">上一月</a>
        <a href="?year=<?php echo $navMonths['next']['year']; ?>&month=<?php echo $navMonths['next']['month']; ?>">下一月</a>
    </div>

    <!-- 顯示當前年月 -->
    <h2><?php echo date("Y 年 m 月", strtotime("$year-$month-01")); ?></h2>

    <!-- 日曆容器 -->
    <div class="calendar">
        <!-- 輸出星期表頭 -->
        <?php foreach ($weekdays as $weekday): ?>
            <div class="header"><?php echo $weekday; ?></div>
        <?php endforeach; ?>

        <!-- 輸出日期格子 -->
        <?php
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 7; $j++) {
                // 計算日期偏移量
                $day = $j + ($i * 7) - $firstDayWeek;
                $timestamp = strtotime("$day days", strtotime($firstDay));
                $date = date("Y-m-d", $timestamp);

                // 使用陣列映射 CSS 類
                $dayOfWeek = date("N", $timestamp);
                $classMap = [
                    'holiday' => $dayOfWeek == 6 || $dayOfWeek == 7,
                    'today' => $today == $date,
                    'other-month' => date("m", $timestamp) != date("m", strtotime($firstDay)),
                    'pass-date' => $timestamp < strtotime($today),
                    'festival' => array_key_exists($date, $festivals),
                ];

                // 生成 CSS 類字串
                $class = "day";
                foreach ($classMap as $className => $condition) {
                    if ($condition) {
                        $class .= " $className";
                    }
                }

                // 假日懸停提示
                $title = array_key_exists($date, $festivals) ? " title='{$festivals[$date]}'" : "";

                // 輸出格子
                echo "<div class='$class' data-date='$date'$title>";
                echo date("j", $timestamp); // 無前導零
                echo "</div>";
            }
        }
        ?>
    </div>
</body>

</html>