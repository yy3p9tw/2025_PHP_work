<?php
include "./data/data.php";

// dd($data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            width: 80%;
            margin: auto;
            text-align: center;
        }

        table,
        th,
        tr,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th>id</th>
            <th>name</th>
        </tr>
        <?php foreach ($data as $key => $value) : ?>
            <tr>
                <td><?= $value['id']?></td>
                <td><?= $value['name']?></td>
            </tr>
        <?php endforeach; ?>


    </table>

    <script>
        
    </script>
</body>

</html>