<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        <?php
        // Define hover style variables
        $hoverBackgroundColor = '#e0f7fa';
        $hoverTextColor = '#007bff';
        ?>body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 20px;
        }

        h2 {
            text-align: center;
            color: #1e90ff;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        table {
            width: 70%;
            border-collapse: collapse;
            margin: 0 auto;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #1e90ff;
            text-align: center;
            padding: 10px;
            font-size: 1em;
        }

        th {
            background-color: #1e90ff;
            color: white;
            font-weight: bold;
        }

        td {
            transition: all 0.3s ease;
        }

        td:hover {
            background-color: <?php echo $hoverBackgroundColor; ?>;
            color: <?php echo $hoverTextColor; ?>;
            cursor: pointer;
        }

        .today {
            background-color: #ffff99;
            font-weight: bold;
        }

        .other-month {
            background-color: #e0e0e0;
            color: #aaa;
        }

        .holiday {
            background-color: #ffcccc;
            color: #333;
        }

        .pass-date {
            color: #aaa;
        }

        @media (max-width: 480px) {
            table {
                width: 95%;
            }

            th,
            td {
                padding: 8px;
                font-size: 0.9em;
            }
        }
    </style>
</head>

<body>
    <?php
    $today = date("Y-m-d");
    $firstDay = date("Y-m-01");
    $firstDayWeek = date("w", strtotime($firstDay));
    $theDaysOfMonth = date("t", strtotime($firstDay));

    ?>
    <h2 style='text-align:center;'><?= date("Y 年 m 月"); ?></h2>
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
        <?php
        for ($i = 0; $i < 6; $i++) {
            echo "<tr>";

            for ($j = 0; $j < 7; $j++) {
                $day = $j + ($i * 7) - $firstDayWeek;
                $timestamp = strtotime(" $day days", strtotime($firstDay));
                $date = date("Y-m-d", $timestamp);
                $class = "";

                if (date("N", $timestamp) > 5) {
                    $class = $class . " holiday";
                }

                if ($today == $date) {

                    $class = $class . " today";
                } else if (date("m", $timestamp) != date("m", strtotime($firstDay))) {

                    $class = $class . " other-month";
                }
                if ($timestamp < strtotime($today)) {
                    $class = $class . " pass-date";
                }
                echo "<td class='$class' data-date='$date'>";
                echo date("d", $timestamp);
                echo "</td>";
            }

            echo "</tr>";
        }
        ?>
</body>

</html>