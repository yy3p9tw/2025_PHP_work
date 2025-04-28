<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>變數</title>
</head>
<body>
<?php

define("看清楚了嗎",2077);
// $宣告變數 後面不要直接打數字=除了數字外都要"xxx" 結尾要+;
$age=32;
$name="于崇銘";
$ename="yy3ptwe";
// 字串跟字串連接用.
// echo 顯示 後面可以接變數跟常數 還是要用.做連接
echo "我的年齡是:{$age}歲";
echo "<br>";
echo "我的名字是:{$name}";
echo "<hr>";
$age=27;
$name="王小明";
echo "<br>";
echo "我的名字是:" . $name ;
echo "<br>";
echo 看清楚了嗎;
echo "<br>";
echo 看清楚了嗎;
echo "<br>";
echo $age;
echo "<br>";
// 結果=(x+xx)結果在前面 計算帶入在後面
// 3=6-9 (反過來計算)
$a=10;
$b=50;
$c=30;
$c=$a;
$a=$b;
$b=$c;
$c=$b-$a;
echo $a.$b.$c; ?>
</body>
</html>