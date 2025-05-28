<h1><?=$year;?>年<?=$month;?>月</h1>
<div class="nav">
    <a href="?year=<?=$prevyear;?>&month=<?=$prev;?>">《 上一月</a>
    <a href="?year=<?=$nextyear;?>&month=<?=$next;?>">下一月 》</a>
</div>

<!-- <form method="get">
    <label for="searchDate">搜尋日期：</label>
    <input type="date" id="searchDate" name="searchDate" value="<?= isset($_GET['searchDate']) ? $_GET['searchDate'] : '' ?>">
    <input type="submit" value="搜尋">
</form> -->

<div class="box-container">
    <div class="th-box">日</div>
    <div class="th-box">一</div>
    <div class="th-box">二</div>
    <div class="th-box">三</div>
    <div class="th-box">四</div>
    <div class="th-box">五</div>
    <div class="th-box">六</div>

    <?php foreach($monthDays as $day): ?>
        <div class="box <?= ($highlightDate && isset($day['fullDate']) && $day['fullDate'] === $highlightDate) ? 'highlight' : '' ?>">
            <div class="day-info">
                <div class="day-num"><?= $day["day"] ?? "&nbsp;" ?></div>
                <div class="day-week"><?= $day["weekOfYear"] ?? "&nbsp;" ?></div>
            </div>
            <div class="holiday-info"><?= $day['holiday'] ?? "&nbsp;" ?></div>
            <div class="todo-info"><?= !empty($day['todo']) ? $day['todo'] : "&nbsp;" ?></div>
        </div>
    <?php endforeach; ?>
</div>