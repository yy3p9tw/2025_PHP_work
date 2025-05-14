<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            border: 1px solid black;

        }
    </style>
</head>

<body>
    <?php
    $today = date("Y-m-d");
    $first_day = date("Y-m-01");
    $first_day_week = date("w", strtotime($first_day));
    $the_day_of_moth = date("t", strtotime($first_day));
    ?>
    <h1>線上日歷</h1>
    <table>
        <tr>
            <td>日</td>
            <td>一</td>
            <td>二</td>
            <td>三</td>
            <td>四</td>
            <td>五</td>
            <td>六</td>
        </tr>
    </table>
    <?php
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = 0; $j < 7; $j++) {

            $day = $j + ($i * 7) - $first_day_week;
            $date = date("Y-m-d", strtotime("$day days", strtotime($first_day)));
            echo "<td>";
                echo $date;
            echo "</td>";
        }
        echo "</tr>";
    }
    ?>
</body>

</html>