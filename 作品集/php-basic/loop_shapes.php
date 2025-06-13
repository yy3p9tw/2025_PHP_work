<!-- filepath: d:\YU\作品集\PHP基本語法\迴圈圖形.php -->
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>迴圈圖形</title>
    <link rel="stylesheet" href="loop_shapes.css">
</head>
<body>
    <div class="container">
        <div class="section">
            <div class="section-title">三角形</div>
            <div class="shape">
                <?php
                for ($i = 0; $i < 5; $i++) {
                    echo "<div class='row'>";
                    for ($j = 0; $j < 5; $j++) {
                        if ($i >= $j) {
                            echo "<span class='star'>*</span>";
                        } else {
                            echo "<span class='space'>&nbsp;</span>";
                        }
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">倒三角形</div>
            <div class="shape">
                <?php
                for ($i = 0; $i < 5; $i++) {
                    echo "<div class='row'>";
                    for ($j = 0; $j < 5; $j++) {
                        if ($i <= $j) {
                            echo "<span class='star'>*</span>";
                        } else {
                            echo "<span class='space'>&nbsp;</span>";
                        }
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">正三角型</div>
            <div class="shape">
                <?php
                for ($i = 0; $i < 5; $i++) {
                    echo "<div class='row'>";
                    for ($a = 0; $a < 5 - 1 - $i; $a++) {
                        echo "<span class='space'>&nbsp;</span>";
                    }
                    for ($j = 0; $j < (2 * $i + 1); $j++) {
                        echo "<span class='star'>*</span>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">菱型</div>
            <div class="shape">
                <?php
                $s = 9;
                if ($s % 2 == 0) {
                    $s = $s + 1;
                }
                for ($i = 0; $i < $s; $i++) {
                    if ($i <= floor($s / 2)) {
                        $y = $i;
                    } else {
                        $y = $s - 1 - $i;
                    }
                    echo "<div class='row'>";
                    for ($j = 0; $j < floor($s / 2) - $y; $j++) {
                        echo "<span class='space'>&nbsp;</span>";
                    }
                    for ($k = 0; $k < $y * 2 + 1; $k++) {
                        echo "<span class='star'>*</span>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">矩形</div>
            <div class="shape">
                <?php
                $s = 10;
                for ($i = 0; $i < $s; $i++) {
                    echo "<div class='row'>";
                    for ($j = 0; $j < $s; $j++) {
                        if ($i == 0 || $i == $s - 1 || $j == 0 || $j == $s - 1) {
                            echo "<span class='star'>*</span>";
                        } else {
                            echo "<span class='space'>&nbsp;</span>";
                        }
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">對角線</div>
            <div class="shape">
                <?php
                $w = 11;
                for ($i = 0; $i < $w; $i++) {
                    echo "<div class='row'>";
                    for ($j = 0; $j < $w; $j++) {
                        if ($i == 0 || $i == $w - 1 || $j == 0  || $j == $w - 1 || $i == $j || $i == $w - 1 - $j) {
                            echo "<span class='star'>*</span>";
                        } else {
                            echo "<span class='space'>&nbsp;</span>";
                        }
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">菱形對角線</div>
            <div class="shape">
                <?php
                $stars = 21;
                if ($stars % 2 == 0) {
                    $stars = $stars + 1;
                }
                for ($i = 0; $i < $stars; $i++) {
                    if ($i <= floor($stars / 2)) {
                        $y = $i;
                    } else {
                        $y = $stars - 1 - $i;
                    }
                    echo "<div class='row'>";
                    for ($j = 0; $j < floor($stars / 2) - $y; $j++) {
                        echo "<span class='space'>&nbsp;</span>";
                    }
                    for ($k = 0; $k < $y * 2 + 1; $k++) {
                        if (($y + $k + $j) == floor($stars / 2) ||
                            abs($y - ($k + $j)) == floor($stars / 2) ||
                            ($k + $j) == floor($stars / 2) ||
                            $i == floor($stars / 2)
                        ) {
                            echo "<span class='star'>*</span>";
                        } else {
                            echo "<span class='space'>&nbsp;</span>";
                        }
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">尋找字元</div>
            <div class="shape">
                <?php
                $string = 'this is a good day';
                $target = 'o';
                $is_find = false;
                $counter = 0;
                while ($is_find == false && $counter < strlen($string)) {
                    if ($string[$counter] == $target) {
                        $is_find = true;
                    }
                    $counter++;
                }
                if ($is_find) {
                    echo '目標字元 <span class="target">' . $target . '</span> 在字串的第 <span class="pos">' . $counter . '</span> 個位置';
                } else {
                    echo '字串中沒有你要找的 <span class="target">' . $target . '</span>';
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">尋找字元-中文字</div>
            <div class="shape">
                <?php
                $string = '今天真是個出遊的好日子啊~';
                $target = '個出';
                $is_find = false;
                $counter = 0;
                while ($is_find == false && $counter < mb_strlen($string)) {
                    if (mb_substr($string, $counter, mb_strlen($target)) == $target) {
                        $is_find = true;
                    }
                    $counter++;
                }
                if ($is_find) {
                    echo '目標字元 <span class="target">' . $target . '</span> 在字串的第 <span class="pos">' . $counter . '</span> 個位置';
                } else {
                    echo '字串中沒有你要找的 <span class="target">' . $target . '</span>';
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">尋找字元-英文單詞</div>
            <div class="shape">
                <?php
                $string = 'this is a good day';
                $target = 'good';
                $is_find = false;
                $counter = 0;
                while ($is_find == false && $counter < mb_strlen($string)) {
                    if (mb_substr($string, $counter, mb_strlen($target)) == $target) {
                        $is_find = true;
                    }
                    $counter++;
                }
                if ($is_find) {
                    echo '目標字元 <span class="target">' . $target . '</span> 在字串的第 <span class="pos">' . $counter . '</span> 個位置';
                } else {
                    echo '字串中沒有你要找的 <span class="target">' . $target . '</span>';
                }
                ?>
            </div>
        </div>
        <div class="section">
            <div class="section-title">mb_strpos 範例</div>
            <div class="shape">
                <?php
                $string = 'this is a good day';
                $target = 'good';
                echo 'mb_strpos 結果：<span class="pos">' . mb_strpos($string, $target) . '</span>';
                ?>
            </div>
        </div>
    </div>
    <!-- 固定右下的回作品集按鈕 -->
    <a href="../index.html" class="back-btn">回作品集</a>
</body>
</html>