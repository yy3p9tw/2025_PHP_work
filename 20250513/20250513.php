<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>字串處理</title>
    <style>
        h1{
            text-align: center;
            color: lightblue;
            font-size: 3em;
            border-bottom: 1px solid lightblue;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>字串處理</h1>
    <h2>自串取代</h2>
    <p>將”aaddw1123”改成”*********”</p>
    <?php
    $a = "aaddw1123";
    // $a= str_replace("a","*",$a);
    // $a = str_replace("d","*",$a);
    // $a = str_replace("w","*",$a);
    // $a = str_replace("1","*",$a);
    // $a = str_replace("2","*",$a);
    // $a = str_replace("3","*",$a);
    // $a = str_replace("aaddw1123", "*********", $a);
    // $a=str_replace(['a','d','w','1','2','3'],'*',$a);
    // $a=str_replace(str_split($a,1),'*',$a);
    $a=str_repeat('*', strlen($a));
    echo $a;
    ?>
</body>
</html>