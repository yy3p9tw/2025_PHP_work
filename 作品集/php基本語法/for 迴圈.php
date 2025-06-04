<!-- filepath: d:\YU\作品集\php基本語法\for 迴圈.php -->
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>for 迴圈</title>
    <link rel="stylesheet" href="for_loop_demo.css">
</head>

<body>
    <div class="container">
        <h2>使用 for 迴圈產生數列</h2>
        <div class="section">
            <div class="section-title">1, 3, 5, 7, 9 ... n</div>
            <div class="output">
                <?php
                for ($i = 1; $i < 30; $i = $i + 2) {
                    echo "<span class='num'>{$i}</span>";
                    if ($i + 2 < 30) echo "<span class='comma'>,</span>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">10, 20, 30, 40, 50, 60 ... n</div>
            <div class="output">
                <?php
                for ($i = 1; $i < 10; $i = $i + 1) {
                    echo "<span class='num'>" . ($i * 10) . "</span>";
                    if ($i < 9) echo "<span class='comma'>,</span>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">10, 20, 30, ... 90</div>
            <div class="output">
                <?php
                for ($i = 10; $i < 100; $i = $i + 10) {
                    echo "<span class='num'>{$i}</span>";
                    if ($i < 90) echo "<span class='comma'>,</span>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">3, 5, 7, 11, 13, 17 ... 97（質數）</div>
            <div class="output">
                <?php
                $first = true;
                for ($j = 3; $j <= 100; $j += 2) {
                    $test = true;
                    for ($i = 2; $i < $j; $i++) {
                        if ($j % $i == 0) {
                            $test = false;
                            break;
                        }
                    }
                    if ($test) {
                        if (!$first) echo "<span class='comma'>,</span>";
                        echo "<span class='num'>{$j}</span>";
                        $first = false;
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <!-- 固定右下的回作品集按鈕 -->
    <a href="../作品集.html" class="back-btn">回作品集</a>
</body>

</html>