<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>迴圈練習01</title>
</head>
<body>
    <h2>直角三角形</h2>
    <?php
    for($i=0;$i<5;$i++){
        for($j=0;$j<5;$j++){
            if($i>=$j){
                echo '*';
            }
        }echo'<br>';
    } 
    ?>
    <h2>倒直角三角形</h2>
    <?php
    for($i=0;$i<5;$i++){
        for($j=0;$j<5;$j++){
            if($i<=$j){
                echo '*';
            }
        }echo'<br>';
    } 
    ?>
    <h2>正三角型</h2>
    <style>
*{
    font-family: 'Courier New', Courier, monospace;
}
    </style>
    <?php 
    for($i=0;$i<5;$i++){
        for($a=0;$a<5-1-$i;$a++){
        echo "&nbsp";}
        for($j=0;$j<(2*$i+1);$j++){
            echo "*";
        }echo '<br>';
    }
    ?>
    
</body>
</html>