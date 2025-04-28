<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>九九乘法表</title>
</head>
<body>

<h2>九九乘法表</h2>
<table border=1>
    <?php
        for($j=1;$j<=9;$j++){
            
            echo "<tr>";
            for($i=1;$i<=9 ;$i++){
                echo "<td>$i x $j = ". ($j * $i) ."</td>";
            }

            echo "</tr>";
        }
        ?>
</table>

<style>
    #tt{
        border-collapse:collapse;
        margin:20px;
        box-shadow:2px 2px 15px blue;
    }
    #tt td{
        padding:3px 6px;
        border:1px solid #CCC;
        text-align:center;
        width:25px;
        text-shadow:1px 1px 2px #99f;
    }

    #tt tr:nth-child(1)
    ,#tt td:nth-child(1){
        background-color:#999;
        color:white;
    }
    #tt td:hover{
        background-color:green;
        color:skyblue;
    }
</style>
<table id='tt'>
    <tr>
        <td></td>
        <td>1</td>
        <td>2</td>
        <td>3</td>
        <td>4</td>
        <td>5</td>
        <td>6</td>
        <td>7</td>
        <td>8</td>
        <td>9</td>
    </tr>
    <?php
        for($j=1;$j<=9;$j++){
            
            echo "<tr>";
                echo "<td>$j</td>";

            for($i=1;$i<=9 ;$i++){
                echo "<td>". ($j * $i) ."</td>";
            }

            echo "</tr>";
        }
        ?>
</table>


<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>


</body>
</html>