<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>for 迴圈</title>
</head>

<body>
    <h2>使用迴圈來產生以下的數列</h2>
    <ul>
        <li>1,3,5,7,9....n</li>
        <li>10,20,30,40,50,60....n</li>
        <li>3,5,7,11,13,17....97</li>
    </ul>
    <?php
    for ($i = 1; $i < 30; $i = $i + 2) {
        echo $i . ",";
    }
    echo "<br>";
    for ($i = 1; $i < 10; $i = $i + 1) {
        echo $i * 10 . ",";
    }
    echo "<br>";
    for ($i = 10; $i < 100; $i = $i + 10) {
        echo $i . ",";
    }
    echo "<br>";

    // 外層迴圈：從 3 開始檢查奇數直到 100，步進 2（跳過偶數）
    for ($j = 3; $j <= 100; $j = $j + 2) {
        // 初始化質數標誌為 true，假設 $j 是質數
        $test = true;
        // 內層迴圈：檢查 $j 是否能被 2 到 $j-1 的數整除
        for ($i = 2; $i < $j; $i = $i + 1) {
            // 如果 $j 能被 $i 整除，則 $j 不是質數
            if ($j % $i == 0) {
                // 將質數標誌設為 false
                $test = false;
                // 找到除數後立即退出內層迴圈
                break;
            }
        }
        // 如果 $test 仍為 true，則 $j 是質數
        if ($test) {
            // 輸出質數 $j，並在後面加上逗號
            echo $j . ",";
        }
    }



    ?>
</body>

</html>