<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>銷售報表</title>
</head>
<body>
<h2>銷售報表</h2>
<?php
include_once "./api/db.php";
$reports=q("SELECT `items`.`name`,
             `items`.`price`,
	         sum(`sales`.`quantity`) as `sales_count`,
             sum(`items`.`price`*`sales`.`quantity`) as `total_sales`
        FROM `items`
   LEFT JOIN `sales` 
          ON `items`.`id`=`sales`.`item_id`
    GROUP BY `sales`.`item_id`");
    
/* $dsn="mysql:host=localhost;dbname=store;charset=utf8";
$pdo=new PDO($dsn, 'root', '');
$sql="SELECT `items`.`name`,
             `items`.`price`,
	         sum(`sales`.`quantity`) as `sales_count`,
             sum(`items`.`price`*`sales`.`quantity`) as `total_sales`
        FROM `items`
   LEFT JOIN `sales` 
          ON `items`.`id`=`sales`.`item_id`
    GROUP BY `sales`.`item_id`";
$reports=$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); */

?>
<style>
    body {
        background: #f4f6f8;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    table {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        font-size: 16px;
    }

    tr:first-child td {
        background: #2d6cdf;
        color: #fff;
        font-weight: bold;
        letter-spacing: 1px;
        border-bottom: 2px solid #1a4173;
    }

    tr:nth-child(even):not(:first-child) {
        background: #f0f4fa;
    }

    tr:hover:not(:first-child) {
        background: #e6f0ff;
        transition: background 0.2s;
    }

    td, th {
        border: none;
        padding: 12px 18px;
    }

    td {
        color: #333;
    }

</style>
<table>
    <tr>
        <td>品項</td>
        <td>個數</td>
        <td>單價</td>
        <td>小計</td>
    </tr>
    <?php
    foreach($reports as $report):
    ?>
    <tr>
        <td><?=$report['name'];?></td>
        <td><?=$report['sales_count'];?></td>
        <td><?=$report['price'];?></td>
        <td><?=$report['total_sales'];?></td>
    </tr>
    <?php 
    endforeach;
    ?>
</table>


</body>
</html>


