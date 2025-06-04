<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>函式練習</title>
</head>
<body>
    <?php
    function printStars($w) {
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $w; $j++) {
                if ($i >= $j) {
                    echo "*";
                }
            }
            echo '<br>';
        }
    }
    function printStars2($w) {
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $w; $j++) {
                if ($i <= $j) {
                    echo "*";
                }
            }
            echo '<br>';
        }
    }
    function printPyramid($h) {
        for ($i = 0; $i < $h; $i++) {
            for ($a = 0; $a < $h - 1 - $i; $a++) {
                echo "&nbsp";
            }
            for ($j = 0; $j < (2 * $i + 1); $j++) {
                echo "*";
            }
            echo "<br>";
        }
    }
    ?>
</body>
</html>