<?php
// 設置時區
date_default_timezone_set('Asia/Taipei');

// 獲取當前年份、月份和視圖
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$view = isset($_GET['view']) ? $_GET['view'] : 'month';
$weekStart = isset($_GET['weekStart']) ? $_GET['weekStart'] : date('Y-m-d', strtotime('monday this week'));

// 確保月份在 1-12 之間
$month = max(1, min(12, $month));

// 處理事件創建
$eventsFile = 'events.json';
$events = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
    $newEvent = [
        'id' => uniqid(),
        'date' => $_POST['date'],
        'time' => $_POST['time'],
        'title' => $_POST['title'],
        'desc' => $_POST['desc']
    ];
    $events[$_POST['date']][] = $newEvent;
    file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
    header("Location: ?year=$year&month=$month&view=$view");
    exit;
}

// 計算日曆數據
$firstDay = new DateTime("$year-$month-01");
$firstDayOfWeek = (int)$firstDay->format('w');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$monthNames = [
    1 => '一月', 2 => '二月', 3 => '三月', 4 => '四月', 5 => '五月', 6 => '六月',
    7 => '七月', 8 => '八月', 9 => '九月', 10 => '十月', 11 => '十一月', 12 => '十二月'
];
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

// 週視圖數據
$weekStartDate = new DateTime($weekStart);
$weekEndDate = (clone $weekStartDate)->modify('+6 days');
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $day = (clone $weekStartDate)->modify("+$i days");
    $weekDays[] = $day->format('Y-m-d');
}

// 定義台灣節日 (固定日期)
$holidays = [
    '01-01' => '元旦',
    '02-28' => '和平紀念日',
    '04-04' => '清明節',
    '05-01' => '勞動節',
    '10-10' => '國慶日'
];

// 動態計算農曆節日（簡化處理）
function getLunarHolidays($year) {
    // 簡化假設的農曆節日日期，實際需農曆轉換
    $lunarHolidays = [
        sprintf("%04d-02-01", $year) => '春節',
        sprintf("%04d-05-05", $year) => '端午節',
        sprintf("%04d-08-15", $year) => '中秋節'
    ];
    return $lunarHolidays;
}
$holidays = array_merge($holidays, getLunarHolidays($year));
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>台灣萬年曆</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #f6f6f6;
            color: #202124;
        }
        .calendar-container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(60,64,67,0.3);
        }
        .header {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #dadce0;
        }
        .header-left {
            flex-grow: 1;
            display: flex;
            align-items: center;
        }
        .header-left h2 {
            margin: 0 16px;
            font-size: 22px;
            font-weight: 400;
        }
        .nav-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            color: #202124;
        }
        .view-selector {
            display: flex;
            gap: 8px;
        }
        .view-btn {
            background: none;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            color: #202124;
        }
        .view-btn.active {
            background: #1a73e8;
            color: white;
            border-color: #1a73e8;
        }
        .month-view, .week-view {
            display: none;
        }
        .month-view.active, .week-view.active {
            display: block;
        }
        /* 月視圖 */
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-size: 12px;
            color: #5f6368;
            padding: 8px 0;
            border-bottom: 1px solid #dadce0;
        }
        .days .sunday {
            color: red;
        }
        .dates {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            min-height: 600px;
        }
        .date {
            border: 1px solid #dadce0;
            padding: 8px;
            position: relative;
            background: white;
            font-size: 14px;
            cursor: pointer;
        }
        .date:hover {
            background: #f1f3f4;
        }
        .date.today {
            border-left: 3px solid #1a73e8;
            background: #e8f0fe;
        }
        .date.sunday .day-number {
            color: red;
        }
        .empty {
            background: #f6f6f6;
        }
        .holiday {
            color: red;
            font-size: 12px;
            margin-top: 2px;
        }
        .event {
            background: #e8f0fe;
            border-left: 4px solid #1a73e8;
            padding: 4px 8px;
            margin: 2px 0;
            font-size: 12px;
            border-radius: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 24px;
        }
        .event:hover {
            background: #d2e3fc;
        }
        /* 週視圖 */
        .week-container {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
        }
        .time-slot {
            border-bottom: 1px solid #dadce0;
            padding: 4px;
            font-size: 12px;
            color: #5f6368;
            height: 40px;
        }
        .week-day {
            border: 1px solid #dadce0;
            position: relative;
        }
        .week-day-header {
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid #dadce0;
            font-size: 12px;
        }
        .week-day-header.sunday {
            color: red;
        }
        .week-event, .week-holiday {
            position: absolute;
            background: #e8f0fe;
            border-left: 4px solid #1a73e8;
            padding: 4px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .week-holiday {
            background: #ffe6e6;
            border-left: 4px solid #d32f2f;
            color: red;
        }
        /* 彈窗和表單 */
        .popup, .add-event-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            z-index: 1000;
        }
        .popup h3, .add-event-form h3 {
            margin: 0 0 10px;
            font-size: 16px;
        }
        .popup p, .add-event-form label {
            margin: 5px 0;
            font-size: 14px;
        }
        .add-event-form input, .add-event-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #dadce0;
            border-radius: 4px;
        }
        .popup .close, .add-event-form .submit, .add-event-form .cancel {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .add-event-form .cancel {
            background: #dadce0;
            color: #202124;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 999;
        }
        @media (max-width: 600px) {
            .calendar-container {
                margin: 10px;
            }
            .header-left h2 {
                font-size: 18px;
            }
            .view-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            .date, .week-day-header {
                font-size: 10px;
                padding: 4px;
            }
            .event, .week-event, .holiday, .week-holiday {
                font-size: 10px;
                padding: 2px 4px;
            }
            .week-container {
                grid-template-columns: 40px repeat(7, 1fr);
            }
            .time-slot {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="header">
            <div class="header-left">
                <button class="nav-btn" data-nav="prev">◄</button>
                <h2 id="calendar-title"><?php echo $view === 'month' ? ($year . '年 ' . $monthNames[$month]) : ($weekStartDate->format('Y年n月j日') . ' - ' . $weekEndDate->format('n月j日')); ?></h2>
                <button class="nav-btn" data-nav="next">►</button>
            </div>
            <div class="view-selector">
                <button class="view-btn <?php echo $view === 'month' ? 'active' : ''; ?>" data-view="month">月</button>
                <button class="view-btn <?php echo $view === 'week' ? 'active' : ''; ?>" data-view="week">週</button>
            </div>
        </div>
        <div class="month-view <?php echo $view === 'month' ? 'active' : ''; ?>">
            <div class="days">
                <div class="sunday">日</div>
                <div>一</div>
                <div>二</div>
                <div>三</div>
                <div>四</div>
                <div>五</div>
                <div>六</div>
            </div>
            <div class="dates">
                <?php
                // 月視圖：填充空白日期格
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="date empty"></div>';
                }
                // 月視圖：填充當月日期
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $dateKey = sprintf("%02d-%02d", $month, $day);
                    $holidayName = isset($holidays[$dateKey]) ? $holidays[$dateKey] : '';
                    $isToday = $date === date('Y-m-d') ? 'today' : '';
                    $isSunday = (new DateTime($date))->format('w') == 0 ? 'sunday' : '';
                    $class = trim("$isToday $isSunday");
                    echo '<div class="date ' . $class . '" data-date="' . $date . '">';
                    echo '<span class="day-number">' . $day . '</span>';
                    if ($holidayName) {
                        echo '<div class="holiday">' . $holidayName . '</div>';
                    }
                    if (isset($events[$date])) {
                        foreach ($events[$date] as $event) {
                            echo '<div class="event" data-event-id="' . $event['id'] . '" data-title="' . htmlspecialchars($event['title']) . '" data-time="' . htmlspecialchars($event['time']) . '" data-desc="' . htmlspecialchars($event['desc']) . '">' . htmlspecialchars($event['time'] . ' ' . $event['title']) . '</div>';
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <div class="week-view <?php echo $view === 'week' ? 'active' : ''; ?>">
            <div class="week-container">
                <div></div>
                <?php foreach ($weekDays as $index => $day): ?>
                    <div class="week-day-header <?php echo (new DateTime($day))->format('w') == 0 ? 'sunday' : ''; ?>">
                        <?php echo (new DateTime($day))->format('n/j (D)'); ?>
                    </div>
                <?php endforeach; ?>
                <?php for ($hour = 0; $hour < 24; $hour++): ?>
                    <div class="time-slot"><?php echo sprintf('%02d:00', $hour); ?></div>
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <div class="week-day" data-date="<?php echo $weekDays[$i]; ?>" data-hour="<?php echo $hour; ?>"></div>
                    <?php endfor; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="overlay"></div>
    <div class="popup">
        <h3 id="popup-title"></h3>
        <p id="popup-time"></p>
        <p id="popup-desc"></p>
        <button class="close">關閉</button>
    </div>
    <div class="add-event-form">
        <h3>新增事件</h3>
        <form id="event-form">
            <input type="hidden" name="date" id="event-date">
            <input type="hidden" name="action" value="add_event">
            <label>時間: <input type="time" name="time" required></label>
            <label>標題: <input type="text" name="title" required></label>
            <label>詳情: <textarea name="desc"></textarea></label>
            <button type="submit" class="submit">保存</button>
            <button type="button" class="cancel">取消</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // 事件點擊
            $(document).on('click', '.event, .week-event', function() {
                const title = $(this).data('title');
                const time = $(this).data('time');
                const desc = $(this).data('desc');
                $('#popup-title').text(title);
                $('#popup-time').text('時間: ' + time);
                $('#popup-desc').text('詳情: ' + desc);
                $('.overlay, .popup').show();
            });

            // 節日點擊
            $(document).on('click', '.holiday, .week-holiday', function() {
                const title = $(this).text();
                $('#popup-title').text(title);
                $('#popup-time').text('全天節日');
                $('#popup-desc').text('台灣法定節日');
                $('.overlay, .popup').show();
            });

            // 關閉彈窗
            $('.close, .overlay').click(function() {
                $('.overlay, .popup, .add-event-form').hide();
            });

            // 新增事件表單
            $('.date, .week-day').click(function() {
                const date = $(this).data('date');
                $('#event-date').val(date);
                $('.overlay, .add-event-form').show();
            });

            // 提交事件
            $('#event-form').submit(function(e) {
                e.preventDefault();
                $.post('?year=<?php echo $year; ?>&month=<?php echo $month; ?>&view=<?php echo $view; ?>', $(this).serialize(), function() {
                    window.location.reload();
                });
            });

            $('.add-event-form .cancel').click(function() {
                $('.overlay, .add-event-form').hide();
            });

            // 視圖切換
            $('.view-btn').click(function() {
                $('.view-btn').removeClass('active');
                $(this).addClass('active');
                const view = $(this).data('view');
                $('.month-view, .week-view').removeClass('active');
                $(`.${view}-view`).addClass('active');
                updateTitle(view);
                window.location.href = `?year=<?php echo $year; ?>&month=<?php echo $month; ?>&view=${view}`;
            });

            // 導航
            $('.nav-btn').click(function() {
                const nav = $(this).data('nav');
                const currentView = $('.view-btn.active').data('view');
                let url = '?';
                if (currentView === 'month') {
                    url += `year=${nav === 'prev' ? <?php echo $prevYear; ?> : <?php echo $nextYear; ?>}&month=${nav === 'prev' ? <?php echo $prevMonth; ?> : <?php echo $nextMonth; ?>}&view=month`;
                } else {
                    const weekStart = new Date('<?php echo $weekStart; ?>');
                    weekStart.setDate(weekStart.getDate() + (nav === 'prev' ? -7 : 7));
                    url += `weekStart=${weekStart.toISOString().split('T')[0]}&view=week`;
                }
                window.location.href = url;
            });

            // 更新標題
            function updateTitle(view) {
                const title = view === 'month' ? '<?php echo $year . '年 ' . $monthNames[$month]; ?>' : '<?php echo $weekStartDate->format('Y年n月j日') . ' - ' . $weekEndDate->format('n月j日'); ?>';
                $('#calendar-title').text(title);
            }

            // 週視圖事件和節日渲染
            <?php foreach ($weekDays as $day): ?>
                <?php
                $dateParts = explode('-', $day);
                $monthKey = (int)$dateParts[1];
                $dayKey = (int)$dateParts[2];
                $dateKey = sprintf("%02d-%02d", $monthKey, $dayKey);
                $holidayName = isset($holidays[$dateKey]) ? $holidays[$dateKey] : '';
                ?>
                <?php if ($holidayName): ?>
                    const holidayCell = $(`.week-day[data-date="<?php echo $day; ?>"][data-hour="0"]`);
                    holidayCell.append(`<div class="week-holiday" style="top: 0; height: 40px;"><?php echo $holidayName; ?></div>`);
                <?php endif; ?>
                <?php if (isset($events[$day])): ?>
                    <?php foreach ($events[$day] as $event): ?>
                        const time = '<?php echo $event['time']; ?>';
                        const hour = parseInt(time.split(':')[0]);
                        const $dayCell = $(`.week-day[data-date="<?php echo $day; ?>"][data-hour="${hour}"]`);
                        $dayCell.append(`<div class="week-event" data-event-id="<?php echo $event['id']; ?>" data-title="<?php echo htmlspecialchars($event['title']); ?>" data-time="<?php echo htmlspecialchars($event['time']); ?>" data-desc="<?php echo htmlspecialchars($event['desc']); ?>" style="top: 0; height: 40px;"><?php echo htmlspecialchars($event['time'] . ' ' . $event['title']); ?></div>`);
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>
</body>
</html>
