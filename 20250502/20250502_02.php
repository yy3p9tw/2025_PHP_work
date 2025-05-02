<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>迴圈圖形</title>
</head>

<body>
    <h2>三角形</h2>
    <?php
    for ($i = 0; $i < 5; $i++) {
        for ($j = 0; $j < 5; $j++) {
            if ($i >= $j) {
                echo "*";
            }
        }
        echo '<br>';
    }
    ?>
    <h2>倒三角形</h2>
    <?php
    for ($i = 0; $i < 5; $i++) {
        for ($j = 0; $j < 5; $j++) {
            if ($i <= $j) {
                echo "*";
            }
        }
        echo '<br>';
    }
    ?>
    <h2>正三角型</h2>
    <style>
        * {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>

    <?php
    for ($i = 0; $i < 5; $i++) {
        for ($a = 0; $a < 5 - 1 - $i; $a++) {
            echo "&nbsp";
        }
        for ($j = 0; $j < (2 * $i + 1); $j++) {
            echo "*";
        }
        echo "<br>";
    }
    ?>
    <h2>菱型</h2>
    <?php
    $s = 15;
    if ($s % 2 == 0) {
        $s = $s + 1;
    }
    for ($i = 0; $i < $s; $i++) {
        if ($i <= floor($s / 2)) {
            $y = $i;
        } else {
            $y = $s - 1 - $i;
        }
        for ($j = 0; $j < floor($s / 2) - $y; $j++) {
            echo "&nbsp";
        }
        for ($k = 0; $k < $y * 2 + 1; $k++) {
            echo "*";
        }
        echo "<br>";
    }

    ?>
    <h2>矩形</h2>
    <?php
    $s = 10;
    for ($i = 0; $i < $s; $i++) {
        for ($j = 0; $j < $s; $j++) {
            if ($i == 0 || $i == $s - 1 || $j == 0 || $j == $s - 1) {
                echo '*';
            } else {
                echo '&nbsp';
            }
        }
        echo '<br>';
    }
    ?>
    <h3>對角線</h3>
    <?php
    $w = 11;
    for ($i = 0; $i < $w; $i++) {

        for ($j = 0; $j < $w; $j++) {

            if ($i == 0 || $i == $w - 1 || $j == 0  || $j == $w - 1 || $i == $j || $i == $w - 1 - $j) {
                echo "*";
            } else {
                echo "&nbsp;";
            }
        }

        echo "<br>";
    }

    ?>
    <h2>菱形對角線</h2>
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

        for ($j = 0; $j < floor($stars / 2) - $y; $j++) {
            echo "&nbsp;";
        }
        //echo "$j";

        for ($k = 0; $k < $y * 2 + 1; $k++) {
            if (($y + $k + $j) == floor($stars / 2) ||
                abs($y - ($k + $j)) == floor($stars / 2) ||
                ($k + $j) == floor($stars / 2) ||
                $i == floor($stars / 2)
            ) {
                echo "*";
            } else {
                echo "&nbsp;";
            }
        }
        echo "<br>";
    }

    ?>
    <h2>
        <?php

        ?>
        <h2>尋找字元</h2>
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
            echo '目標字元' . $target . '在字串的第' . $counter . '個位置';
        } else {
            echo '字串中沒有你要找的' . $target;
        }
        ?>

        <h2>尋找字元-中文字</h2>
        <?php
        $string = '今天真是個出遊的好日子啊~';
        $target = '個出';
        $is_find = false;
        $counter = 0;
        while ($is_find == false && $counter <mb_strlen($string)) {
            if (mb_substr($string,$counter,mb_strlen($target)) == $target) {
                $is_find = true;
            }
            $counter++;
        }
        if ($is_find) {
            echo '目標字元' . $target . '在字串的第' . $counter . '個位置';
        } else {
            echo '字串中沒有你要找的' . $target;
        }
        ?>
        <h2>尋找字元-英文單詞</h2>
        <?php
        $string = 'this is a good day';
        $target = 'good';
        $is_find = false;
        $counter = 0;
        while ($is_find == false && $counter <mb_strlen($string)) {
            if (mb_substr($string,$counter,mb_strlen($target)) == $target) {
                $is_find = true;
            }
            $counter++;
        }
        if ($is_find) {
            echo '目標字元' . $target . '在字串的第' . $counter . '個位置';
        } else {
            echo '字串中沒有你要找的' . $target;
        }
        ?>
        <hr>
        <?php
        echo mb_strpos($string,$target);  
        ?>

</body>

</html>