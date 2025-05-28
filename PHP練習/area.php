<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>pi</title>
</head>
<body>
    <?php
    define("PI",3.14);
    $radius=10;
    $area=PI*$radius*$radius;
    echo $area;
    echo "<br>";
    $radius=20;
    $area=PI*$radius*$radius;
    echo $area;
    echo "<br>";
    ?>
</body>
</html>