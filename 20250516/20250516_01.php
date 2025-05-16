<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>線上日曆</title>
</head>
<body>
<h1>線上日曆</h1>

<?php
// 初始化日曆參數
function initializeCalendar() {
    date_default_timezone_set("Asia/Taipei");
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
    $month = isset($_GET['month']) ? intval($_GET['month']) : date("n");
    // 輸入驗證
    if ($month < 1 || $month > 12 || $year < 1970 || $year > 9999) {
        $year = date("Y");
        $month = date("n");
    }
    return [
        'year' => $year,
        'month' => $month,
        'today' => date("Y-m-d"),
        'firstDay' => date("$year-$month-01"),
        'firstDayWeek' => date("w", strtotime("$year-$month-01")),
        'daysInMonth' => date("t", strtotime("$year-$month-01"))
    ];
}

// 獲取事件資料（模擬資料庫）
function getEvents() {
    return [
        ['date' => '2025-04-04', 'type' => 'holiday', 'name' => '兒童節'],
        ['date' => '2025-04-05', 'type' => 'holiday', 'name' => '清明節'],
        ['date' => '2025-05-01', 'type' => 'holiday', 'name' => '勞動節'],
        ['date' => '2025-05-01', 'type' => 'todo', 'name' => '開會'],
        ['date' => '2025-05-11', 'type' => 'holiday', 'name' => '母親節'],
        ['date' => '2025-05-30', 'type' => 'holiday', 'name' => '端午節'],
        ['date' => '2025-06-06', 'type' => 'todo', 'name' => '生日']
    ];
}

// 分離節日與待辦
function processEvents($events) {
    $spDate = [];
    $todoList = [];
    foreach ($events as $event) {
        if ($event['type'] === 'holiday') {
            $spDate[$event['date']] = $event['name'];
        } else {
            $todoList[$event['date']] = $event['name'];
        }
    }
    return [$spDate, $todoList];
}

// 生成線上日曆
function generateOnlineCalendar($calendar, $spDate, $todoList) {
    $firstDay = $calendar['firstDay'];
    $firstDayWeek = $calendar['firstDayWeek'];
    $daysInMonth = $calendar['daysInMonth'];
    $today = $calendar['today'];

    // 建構日曆資料
    $monthDays = array_fill(0, $firstDayWeek, null);
    for ($i = 0; $i < $daysInMonth; $i++) {
        $timestamp = strtotime("$i days", strtotime($firstDay));
        $date = date("Y-m-d", $timestamp);
        $monthDays[] = [
            'day' => date("d", $timestamp),
            'fullDate' => $date,
            'weekOfYear' => date("W", $timestamp),
            'isWeekend' => date("N", $timestamp) >= 6,
            'isToday' => $date === $today,
            'holiday' => $spDate[$date] ?? '',
            'todo' => $todoList[$date] ?? ''
        ];
    }
    $monthDays = array_pad($monthDays, 42, null);

    // 月份導航
    $prevMonth = date("Y-m", strtotime("$firstDay -1 month"));
    $nextMonth = date("Y-m", strtotime("$firstDay +1 month"));
    ?>
    <div class="nav">
        <a href="?year=<?= date('Y', strtotime($prevMonth)) ?>&month=<?= date('n', strtotime($prevMonth)) ?>">上一月</a>
        <span><?= date("Y年m月", strtotime($firstDay)) ?></span>
        <a href="?year=<?= date('Y', strtotime($nextMonth)) ?>&month=<?= date('n', strtotime($nextMonth)) ?>">下一月</a>
    </div>
    <div class="calendar">
        <?php
        foreach (['日', '一', '二', '三', '四', '五', '六'] as $day) {
            echo "<div class='header'>$day</div>";
        }
        foreach ($monthDays as $day) {
            $class = ['box'];
            $data = $day ? "data-date='{$day['fullDate']}'" : '';
            if ($day) {
                if ($day['isWeekend']) $class[] = 'weekend';
                if ($day['isToday']) $class[] = 'today';
            }
            echo "<div class='" . implode(" ", $class) . "' $data>";
            echo "<div class='day'>" . ($day['day'] ?? '') . "</div>";
            echo "<div class='week'>" . ($day['weekOfYear'] ?? '') . "</div>";
            echo "<div class='holiday'>" . (isset($day['holiday']) && $day['holiday'] ? htmlspecialchars($day['holiday']) : '') . "</div>";
            echo "<div class='todo'>" . (isset($day['todo']) && $day['todo'] ? htmlspecialchars($day['todo']) : '') . "</div>";
            echo "</div>";
        }
        ?>
    </div>
    <?php
}

// 主程式
$calendar = initializeCalendar();
$events = getEvents();
[$spDate, $todoList] = processEvents($events);
generateOnlineCalendar($calendar, $spDate, $todoList);
?>

<script>
document.querySelectorAll('.box[data-date]').forEach(box => {
    box.addEventListener('click', () => {
        const date = box.dataset.date;
        const holiday = box.querySelector('.holiday').textContent;
        const todo = box.querySelector('.todo').textContent;
        let message = `日期: ${date}`;
        if (holiday) message += `\n節日: ${holiday}`;
        if (todo) message += `\n待辦: ${todo}`;
        alert(message);
    });
});
</script>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
.nav {
    text-align: center;
    margin-bottom: 20px;
}
.nav a {
    margin: 0 20px;
    text-decoration: none;
    color: #007bff;
}
.nav a:hover {
    text-decoration: underline;
}
.calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #f0f0f0;
    padding: 5px;
}
.header {
    background: #e0e0e0;
    text-align: center;
    padding: 10px;
    font-weight: bold;
}
.box {
    background: #fff;
    border: 1px solid #ddd;
    padding: 10px;
    min-height: 100px;
    text-align: center;
    cursor: pointer;
}
.box:hover {
    background: #f9f9f9;
}
.today {
    background: #ffeb3b;
}
.weekend .day, .holiday {
    color: red;
}
.day {
    font-size: 1.2em;
    font-weight: bold;
}
.week {
    font-size: 0.8em;
    color: #666;
}
.holiday, .todo {
    font-size: 0.9em;
    margin-top: 5px;
}
.todo {
    color: #007bff;
}
</style>
</body>
</html>