<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>九九乘法表</title>
</head>

<body>

    <h2>九九乘法表</h2>
    <!-- 第一個表格：顯示九九乘法表（含運算式） -->
    <table border="1">
        <?php
        // 外層迴圈：控制列數，從 1 到 9
        for ($j = 1; $j <= 9; $j++) {
            // 開始新的一列
            echo "<tr>";
            // 內層迴圈：控制每列的格子，從 1 到 9
            for ($i = 1; $i <= 9; $i++) {
                // 輸出每個格子的內容，格式為 "i x j = 結果"
                echo "<td>$i x $j = " . ($j * $i) . "</td>";
            }
            // 結束當前列
            echo "</tr>";
        }
        ?>
    </table>

    <!-- CSS 樣式：美化第二個表格的外觀 -->
    <style>
        /* 為 id="tt" 的表格設定樣式 */
        #tt {
            border-collapse: collapse;
            /* 合併邊框線 */
            margin: 20px;
            /* 外邊距 20px */
            box-shadow: 2px 2px_gamma 15px blue;
            /* 藍色陰影效果 */
        }

        /* 為表格內的 td 元素設定樣式 */
        #tt td {
            padding: 3px 6px;
            /* 內邊距：上下 3px，左右 6px */
            border: 1px solid #CCC;
            /* 灰色邊框 */
            text-align: center;
            /* 文字置中 */
            width: 25px;
            /* 固定寬度 25px */
            text-shadow: 1px 1px 2px #99f;
            /* 藍色文字陰影 */
        }

        /* 為第一列和第一行的格子設定背景色與文字顏色 */
        #tt tr:nth-child(1),
        #tt td:nth-child(1) {
            background-color: #999;
            /* 灰色背景 */
            color: white;
            /* 白色文字 */
        }

        /* 滑鼠懸停時的格子樣式 */
        #tt td:hover {
            background-color: green;
            /* 綠色背景 */
            color: skyblue;
            /* 天藍色文字 */
        }
    </style>

    <!-- 第二個表格：顯示九九乘法表（僅顯示結果） -->
    <table id='tt'>
        <!-- 標題列：顯示 1 到 9 的數字 -->
        <tr>
            <td></td> <!-- 左上角空格 -->
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

        for ($j = 1; $j <= 9; $j++) {
            echo "<tr>";
            echo "<td>$j</td>";
            for ($i = 1; $i <= 9; $i++) {
                if ($i <= $j) {
                    echo "<td>" . ($j * $i) . "</td>";
                }
            }
            echo "</tr>";
        }   
        ?>
    </table>
</body>

</html>