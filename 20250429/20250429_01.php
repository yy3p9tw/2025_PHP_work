<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="">
        <label for="name">姓名:</label>
        <input type="text" name="name" id="name">

        <input type="radio" name="002" id="cat">
        <label for="cat">貓</label>
        <input type="radio" name="002" id="dog">
        <label for="dog">狗</label>
        <input type="radio" name="002" id="bird">
        <label for="bird">鳥</label>

        <input type="checkbox" name="003" id="cat1">
        <label for="cat1">貓</label>
        <input type="checkbox" name="003" id="dog1">
        <label for="dog1">狗</label>
        <input type="checkbox" name="003" id="bird1">
        <label for="bird1">鳥</label>

        <label for="birthday">生日</label>
        <input type="date" name="birthday" id="birthday">

        <label for="car">車</label>
        <input type="text" name="car" id="car">
        <select name="car" id="car">
            <option value="bmw">bmw</option>
            <option value="aubi">aubi</option>
            <option value="toyota">toyota</option>
        </select>

        <textarea name="msa" id="msa" cols="30" rows="10">
            表單客訴內容
        </textarea>
        <input type="submit" value="送出">
    </form>
    <?php
    $a = 30;
    if (!is_numeric($a) || $a > 100 || $a < 0) {
        echo "請輸入數字";
        exit();
    }
    echo "成績" . $a . "分";

    if ($a >= 60) {
        echo "及格";
    } else {
        echo "不及格";
    }
    if ($a >= 0 && $a <= 59) {
        $level = 'e';
    } else if ($a >= 60 && $a <= 69) {
        $level = "d";
    } else if ($a >= 70 && $a <= 79) {
        $level = "c";
    } else if ($a >= 80 && $a <= 89) {
        $level = "b";
    } else {
        $level = "a";
    }
    echo $level;
    switch ($level) {
        case 'a':
            echo '很棒';
            break;
        case 'b':
            echo '超棒棒';
            break;
        case 'c':
            echo "棒棒";
            break;
        case 'd':
            echo '棒棒';
            break;
        case 'e':
            echo '棒棒';
            break;
        default:
            echo "請告知工程人員";
    }

    $yesr = 2200;
    if ($yesr % 4 == 0) {
        if ($yesr % 100 != 0) {
            echo "閏年";
        } else {
            if ($yesr % 400 != 0) {
                echo "平年";
            } else {
                echo "閏年";
            }
        }
    } else {
        echo "平年";
    }
    $yesr = 2200;
    echo ($yesr % 4 == 0 && $yesr % 100 != 0 || $yesr % 400 == 0) ? "閏年" : '平年';

    $b = [
        "王曉明" => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        "王二明" => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        "王三明" => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        "王四明" => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95],
        "王四明" => ['國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95, '國文' => 95]
    ];
    for ($j = 1; $j < 30; $j = $j + 2) {
        echo $j . ",";
    }
    for ($i = 10; $i < 100; $i = $i + 10) {
        echo $i . ",";
    }
    for ($j = 1; $j < 10; $j = $j + 1) {
        echo $j * 10 . ",";
    }

    for ($j = 1; $j <= 9; $j++) {
        echo "<tr>";
        for ($i = 1; $i <= 9; $i++) {
            echo "<td>$i x $j=" . ($j * $i) . "</td>";
        }
        echo "</tr>";
    }
    for ($j = 1; $j <= 9; $j++) {
        echo "<tr>";
               echo "<td>$j</td>";
        for ($i = 1; $i <= 9; $i++) {
            echo "<td>" . ($j * $i) . "</td>";
        }
        echo "</tr>";
    }


    ?>
</body>

</html>