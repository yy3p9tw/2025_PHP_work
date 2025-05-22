<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    $sy = 60;
    if (!is_numeric($sy) || $sy > 100 || $sy < 0) {
        echo '請輸入數字';
        exit();
    }
    echo '成績' . $sy . '分';
    if ($sy >= 60) {
        echo "及格";
    } else {
        echo '不及格';
    }
    if ($sy >= 0 && $sy <= 59) {
        $level = 'e';
    } else if ($sy >= 60 && $sy <= 69) {
        $level = 'd';
    } else if ($sy >= 70 && $sy <= 79) {
        $level = 'c';
    } else if ($sy >= 80 && $sy <= 89) {
        $level = 'b';
    } else {
        $level = 'a';
    }
    echo $level;
    switch ($level) {
        case 'a':
            echo '很棒';
            break;
        case 'b':
            echo '很棒';
            break;
        case 'c':
            echo '很棒';
            break;
        case 'd':
            echo '很棒';
            break;
        case 'e':
            echo '很棒';
            break;
        default:
            echo '請告知工程人員';
    }
    $yesr = 2200;
    if ($yesr % 4 == 0) {
        if ($yesr % 100 != 0) {
            echo '閏年';
        } else {
            if ($yesr % 400 != 0) {
                echo '平年';
            } else {
                echo '閏年';
            }
        }
    } else {
        echo '平年';
    }
    $yesr = 2200;
    echo ($yesr % 4 == 0 && $yesr % 100 != 0 || $yesr % 400 == 0) ? '閏年' : '平年';

    $a = [
        '王阿明' => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        '王阿明' => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        '王阿明' => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        '王阿明' => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        '王阿明' => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95]
    ];
    for ($j = 1; $j < 30; $j = $j + 2) {
        echo $j . ',';
    }
    for ($j = 1; $j < 10; $j = $j + 1) {
        echo $j * 10 . ',';
    }
    for ($j = 1; $j <= 9; $j++) {
        echo '<tr>';
        for ($i = 1; $i <= 9; $i++) {
            echo "<td>$i x $j=" . ($j * $i) . "</td>";
        }
        echo '</tr>';
    }
for($j = 1; $j <= 9; $j++){
    echo "<tr>";
    echo "<td> $j </td>";
    for($i = 1; $i <= 9; $i++){
        echo"<td>".($i*$j)."</td>";
    }
    echo'<tr>';
}
    ?>
</body>

</html>