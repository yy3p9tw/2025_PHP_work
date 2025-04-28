<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選擇結構</title>
    
</head>
<body>
    <h1>判斷成績</h1>
    <P>給定一個成績數字,判斷是否及格(60)分</P>
<?php
$score=80;

if(!is_numeric($score) || $score>100 || $score<0){
    echo"請輸入合法的成績數字";
exit();
}



echo "你的成績是:".$score."分";
echo "<br>";


if($score>=60){
    echo "<span style='color:green; font-size: 20px;'>及格</span>";
}else{
    echo "<span style='color:red; font-size: 20px;'>不及格</span>";
}
    ?>
    <h2>分配成績等級</h2>
    <ul>
   <li> 給定一個成績數字，根據成績所在的區間，給定等級</li>
   <li>0 ~ 59 => E</li>
   <li>60 ~ 69 => D</li>
   <li>70 ~ 79 => C</li>
   <li>80 ~ 89 => B</li>
   <li>90 ~ 100 => A</li>
    </ul>
    <?php
    $level='';
    if($score>=0 && $score<=59){
         $level="E";
     }else if($score>=60 && $score<=69){
         $level="D";
     }else if($score>=70 && $score<=79){
         $level="C";
     }else if($score>=80 && $score<=89){
         $level="B";
     }else {
         $level="A";
     }
    echo "<br>";
    echo $level;
    ?>
    <h2>成績評語</h2>
    <?php
    switch($level){
        case "A":
        echo "棒棒棒棒棒";
        break;
        case "B";
        echo "棒棒棒棒";
        break;
        case "C":
        echo "棒棒棒";
        break;
        case "D":
        echo "棒棒";
        break;
        case "E":
        echo "棒";
        break;
        default:
        echo "請告知工程人員";
    }
    ?>
</body>
</html>