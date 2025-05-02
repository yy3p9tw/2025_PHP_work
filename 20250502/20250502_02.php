<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>九九乘法表</title>
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
    $s=15;
    if($s%2==0){
        $s=$s+1;
    }
    for ($i = 0; $i < $s; $i++) {
        if($i<=floor($s/2)){
            $y=$i;
        }else{
            $y=$s-1-$i;
        }
        for($j=0;$j<floor($s/2)-$y;$j++)
       { echo "&nbsp";}
       for($k=0;$k<$y*2+1;$k++){
        echo "*";
       }
       echo "<br>";
    }

    ?>



</body>

</html>