<!-- filepath: d:\YU\作品集\PHP基本語法\九九乘法表.php -->
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>九九乘法表</title>
    <link rel="stylesheet" href="multiplication_table.css">
</head>
<body>
    <h2 class="main-title">九九乘法表</h2>
    <!-- 第一個：含運算式 -->
    <div class="table-wrap">
        <div class="table-title">含運算式</div>
        <div class="mul-table">
            <?php
            for ($j = 1; $j <= 9; $j++) {
                echo "<div class='row'>";
                for ($i = 1; $i <= 9; $i++) {
                    echo "<div class='cell'>$i × $j = " . ($j * $i) . "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <!-- 第二個：僅顯示結果 -->
    <div class="table-wrap">
        <div class="table-title">僅顯示結果</div>
        <div class="mul-table" id="only-result">
            <div class="row">
                <div class="cell head"></div>
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <div class="cell head"><?= $i ?></div>
                <?php endfor; ?>
            </div>
            <?php for ($j = 1; $j <= 9; $j++): ?>
                <div class="row">
                    <div class="cell head"><?= $j ?></div>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <div class="cell"><?= $j * $i ?></div>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <!-- 固定右下的回作品集按鈕 -->
    <a href="../index.html" class="back-btn">回作品集</a>
</body>
</html>