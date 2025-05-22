<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<table border="1">
        <tr>
            <th>索引</th>
            <th>值</th>
        </tr>
        <!-- php跟html分開寫 -->
        <?php
        $variable = ["蘋果", "香蕉", "葡萄", "芒果"];
        ?>                
        <tr>
            <td><?= $variable[0] ?></td>
            <td><?= $variable[1] ?></td>
        </tr>
        <p><?=$variable[3] ?></p>
    </table>
</body>
</html>