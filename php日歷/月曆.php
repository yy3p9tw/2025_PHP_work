<?php
// 設置預設年份和月份，可以通過 GET 參數修改
$year = isset($_GET['year']) ? intval($_GET['year']) : 2025;
$month = isset($_GET['month']) ? intval($_GET['month']) : 5;

// 確保月份在 1-12 之間
$month = max(1, min(12, $month));

// 獲取該月的第一天是星期幾，以及該月總共有幾天
$firstDayOfMonth = new DateTime("$year-$month-01");
$firstDayOfWeek = $firstDayOfMonth->format('w'); // 0 (週日) 到 6 (週六)
$daysInMonth = $firstDayOfMonth->format('t'); // 該月天數

// 獲取月份名稱
$monthName = $firstDayOfMonth->format('F');
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>月曆</title>
    <style>
        table {
            border-collapse: collapse;
            width: 300px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 40px; /* 確保每個格子寬度一致 */
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 300px;
            margin: 0 auto;
            font-size: 20px;
            font-weight: bold;
        }
        .year {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <span><?php echo $monthName; ?></span>
        <span class="year"><?php echo $year; ?></span>
    </div>
    <table>
        <tr>
            <th>SUN</th>
            <th>MON</th>
            <th>TUE</th>
            <th>WED</th>
            <th>THU</th>
            <th>FRI</th>
            <th>SAT</th>
        </tr>
        <?php
        $day = 1;
        $totalCells = ($daysInMonth + $firstDayOfWeek + (6 - ($daysInMonth + $firstDayOfWeek - 1) % 7) % 7); // 計算總格子數
        $currentDay = 0;

        // 開始填入日期
        while ($currentDay < $totalCells) {
            echo "<tr>";
            for ($i = 0; $i < 7 && $currentDay < $totalCells; $i++) {
                if ($currentDay < $firstDayOfWeek || $day > $daysInMonth) {
                    echo "<td></td>";
                } else {
                    echo "<td>$day</td>";
                    $day++;
                }
                $currentDay++;
            }
            echo "</tr>";
        }
        ?>
    </table>

    <!-- 簡單的表單來更改年份和月份 -->
    <div style="text-align: center;">
        <form method="GET" action="calendar.php">
            <label>年份: </label>
            <input type="number" name="year" value="<?php echo $year; ?>" min="1900" max="9999">
            <label>月份: </label>
            <input type="number" name="month" value="<?php echo $month; ?>" min="1" max="12">
            <input type="submit" value="更新">
        </form>
    </div>
</body>
</html>