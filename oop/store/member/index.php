<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>早餐店系統</title>
</head>
<body>
    <h1>泰山泰好吃早餐店</h1>
    <h2>餐點項目</h2>
    <?php 
    include_once "./api/db.php";
    
    $items=$Item->all();
/*      $dsn="mysql:host=localhost;dbname=store;charset=utf8";
     $pdo=new PDO($dsn, 'root', '');
     $items=$pdo->query("SELECT `id`, `name`,`price` FROM items")->fetchAll(PDO::FETCH_ASSOC); */
    
    ?>
    <style>
        #items,.btns{
            width: 50%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        #items td, #items th {
            border: 1px solid #ddd;
            padding: 5px 12px;
            text-align: center;
        }
        .btns{
            display: flex;
            justify-content: space-between;
        }
    </style>
    <div class='btns'>
        <button><a href='add_item.php'>+</a></button>
        <button><a href='sales_report.php'>銷售報表</a></button>
    </div>
    <table id='items'>
        <tr>
            <td>品項</td>
            <td>價格</td>
            <td>操作</td>
        </tr>
        <?php
        foreach($items as $item):
        ?>
        <tr>
            <td><?=$item['name'];?></td>
            <td><?=$item['price'];?></td>
            <td>
                <a href='update_item.php?id=<?=$item['id'];?>'>編輯</a>
                <a href='./api/delete_item.php?id=<?=$item['id'];?>'>刪除</a>
            </td>
        </tr>
        <?php
        endforeach;
        ?>

    </table>
</body>
</html>