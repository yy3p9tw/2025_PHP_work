<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>陣列設計</title>
</head>

<body>
    <h2>建立一個學生成績陣列</h2>
    <ul>
        <li>設計一個陣列(一維或多維)來存放學生的成績資料</li>
    </ul>
    <?php
    //二維陣列
    $transcript = [
        "王大明" => ["國文" => 95, "英文" => 64, "數學" => 70, "地理" => 90, "歷史" => 84],
        "李小王" => ["國文" => 88, "英文" => 78, "數學" => 54, "地理" => 81, "歷史" => 71],
        "張中李" => ["國文" => 45, "英文" => 60, "數學" => 68, "地理" => 70, "歷史" => 62],
        "黃大明" => ["國文" => 59, "英文" => 32, "數學" => 77, "地理" => 54, "歷史" => 42],
        "明小李" => ["國文" => 71, "英文" => 62, "數學" => 80, "地理" => 62, "歷史" => 64]
    ];

    //    $a= [ 95,  64,  70,  90,  84];
    //  for($i=0;$i<count($a);$i++){
    //     echo $a[$i];
    //  }
    foreach ($transcript as $name => $score) {
        echo "<pre>";
        echo $name . "=";
        echo '<ul>';
        foreach ($score as $subject => $a) {
            echo "<li>";
            echo $subject . ":";
            echo $a;
            echo "</li>";
        };
        echo "</ul>";
        echo "</pre>";
    }

    $names = array_keys($transcript);
    for ($i = 0; $i < count($names); $i++) {
        $a = $transcript[$names[$i]];
        $subjects = array_keys($a);
        echo $names[$i];
        echo '的成績<br>';
        $b = $transcript[$names[$i]];
        for ($j = 0; $j < count($b); $j++) {
            echo $subjects[$j];
            echo ':';
            echo $b[$subjects[$j]];
            echo '<br>';
        }
        // print_r($transcript[$name[$i]]);
    }



    ?>
    <h2>利用程式來產生陣列</h2>
    <ul>
        <li>以迴圈的方式產生一個九九乘法表</li>
        <li>將九九乘法表的每個項目以字串型式存入陣列中</li>
        <li>再以迴圈方式將陣列內容印出</li>
    </ul>
    <?php
    $ij = [];
    for ($i = 1; $i <= 9; $i++) {
        for ($j = 1; $j <= 9; $j++) {
            $ij[$i . $j] = "$i x $j = " . ($i * $j);
        }
    }
    echo "<ul>";
    foreach ($ij as $ij99) {
        echo "<li>$ij99</li>";
    }
    echo "</ul>";
    echo "<br>";
    echo "<br>";
    echo  $ij[36];

    ?>
    <h2>威力彩電腦選號沒有重覆號碼(利用while迴圈)</h2>
    <ul>
        <li>使用亂數函式rand($a,$b)來產生號碼</li>
        <li>將產生的號碼順序存入陣列中</li>
        <li>每次存入陣列中時會先檢查陣列中的資料有沒有重覆</li>
        <li>完成選號後將陣列內容印出</li>
    </ul>
    <?php
    $num = range(1, 38);
    // $num = range(1, 38); // 產生 1 到 38 的數字陣列
    $lotto = [];    // 儲存選號的陣列
    for ($i = 0; $i < 6; $i++) {
        shuffle($num);
        $lotto[] = array_pop($num);
    }
    echo "威力彩號碼：<br>";
    foreach ($lotto as $number) {
        echo $number . "<br>";
    }
    ?>

    <ul>
        <li>請依照閏年公式找出五百年內的閏年</li>
        <li>使用陣列來儲存閏年</li>
        <li>使用迴圈來印出閏年</li>
    </ul>
    <?php

    $leapYear = [];
    for ($year = 2000; $year <= 2500; $year++) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            $leapYear[$year] = true;
        } else {
            $leapYear[$year] = false;
        }
    }
    echo '<br>';
  $year2 = 2100;
    // if ($leapYear[$year2]) {
    //     echo $year2 . "是閏年";
    // }
    // echo $year2 . '不是閏年' . '<br>';
    echo $year2.($leapYear[$year2] ? '是閏年' : '不是閏年') . '<br>';
   ?>
    <h2>已知西元1024年為甲子年，請設計一支程式，可以接受任一西元年份，輸出對應的天干地支的年別。(利用迴圈)</h2>
    <ul>
        <li>天干：甲乙丙丁戊己庚辛壬癸</li>
        <li>地支：子丑寅卯辰巳午未申酉戌亥</li>
        <li>天干地支配對：甲子、乙丑、丙寅….甲戌、乙亥、丙子….</li>
        <?php
        $e1=[
            '甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'
        ];
        $e2=[
            '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'
        ];
        $year = 1984;
$d = [];
for($j=0;$j<500;$j++){
    $d[$year+$j]=$e1[$j%10] . $e2[$j%12];
}
echo '<pre>';
print_r($d);
echo '</pre>';
?>

</body>

</html>