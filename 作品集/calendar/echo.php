<div class="calendar-wrap">
    <div class="calendar-header">
        <div class="calendar-title"><?=$year;?> 年 <?=$month;?> 月</div>
        <div class="nav">
            <a href="?year=<?=$prevyear;?>&month=<?=$prev;?>" class="calendar-btn">《 上一月</a>
            <a href="?year=<?=$nextyear;?>&month=<?=$next;?>" class="calendar-btn">下一月 》</a>
        </div>
    </div>

    <!-- 搜尋日期表單 -->
    <form method="get" class="calendar-search">
        <label for="searchDate">搜尋日期：</label>
        <input type="date" id="searchDate" name="searchDate" value="<?= isset($_GET['searchDate']) ? $_GET['searchDate'] : '' ?>">
        <input type="hidden" name="year" value="<?=$year?>">
        <input type="hidden" name="month" value="<?=$month?>">
        <input type="submit" value="搜尋">
        <?php if (!empty($_GET['searchDate'])): ?>
            <a href="?year=<?=$year?>&month=<?=$month?>" class="reset-btn">清除搜尋</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($highlightDate)): ?>
        <div class="search-result">
            <?php
            $found = false;
            foreach($monthDays as $day) {
                if (isset($day['fullDate']) && $day['fullDate'] === $highlightDate) {
                    $found = true;
                    echo "<div>搜尋日期：<span class='highlight-date'>{$highlightDate}</span>";
                    if (!empty($day['holiday'])) {
                        echo "，節日：<span class='holiday-label'>{$day['holiday']}</span>";
                    }
                    if (!empty($day['todo'])) {
                        echo "，待辦：<span class='todo-label'>{$day['todo']}</span>";
                    }
                    echo "</div>";
                    break;
                }
            }
            if (!$found) {
                echo "<div>查無此日期於本月！</div>";
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="box-container">
        <div class="th-box">日</div>
        <div class="th-box">一</div>
        <div class="th-box">二</div>
        <div class="th-box">三</div>
        <div class="th-box">四</div>
        <div class="th-box">五</div>
        <div class="th-box">六</div>

        <?php foreach($monthDays as $day): ?>
            <div class="box
                <?= ($highlightDate && isset($day['fullDate']) && $day['fullDate'] === $highlightDate) ? ' highlight' : '' ?>
                <?= (isset($day['holiday']) && $day['holiday']) ? ' holiday' : '' ?>
                <?= (isset($day['todo']) && $day['todo']) ? ' todo' : '' ?>
                <?= empty($day) ? 'empty' : '' ?>"
            >
                <div class="day-info">
                    <div class="day-num"><?= $day["day"] ?? "&nbsp;" ?></div>
                    <div class="day-week"><?= $day["weekOfYear"] ?? "&nbsp;" ?></div>
                </div>
                <div class="holiday-info"><?= $day['holiday'] ?? "&nbsp;" ?></div>
                <div class="todo-info"><?= !empty($day['todo']) ? $day['todo'] : "&nbsp;" ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>