<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>變數練習</title>
    <link rel="stylesheet" href="variable_demo.css">
</head>
<body>
    <div class="container">
        <h1>PHP 變數與常數練習</h1>
        <div class="section">
            <div class="section-title">常數與變數宣告</div>
            <div class="code-block">
                <code>
                    define("看清楚了嗎",2077);<br>
                    $age=32;<br>
                    $name="于崇銘";<br>
                    $ename="yy3ptwe";
                </code>
            </div>
        </div>
        <div class="section">
            <div class="section-title">輸出結果</div>
            <div class="output">
                <?php
                define("看清楚了嗎",2077);
                $age=32;
                $name="于崇銘";
                $ename="yy3ptwe";
                echo "<div>我的年齡是：<span class='value'>{$age}歲</span></div>";
                echo "<div>我的名字是：<span class='value'>{$name}</span></div>";
                echo "<hr>";
                $age=27;
                $name="王小明";
                echo "<div>我的名字是：<span class='value'>" . $name . "</span></div>";
                echo "<div>常數：<span class='value'>" . 看清楚了嗎 . "</span></div>";
                echo "<div>常數：<span class='value'>" . 看清楚了嗎 . "</span></div>";
                echo "<div>年齡：<span class='value'>" . $age . "</span></div>";
                $a=10;
                $b=50;
                $c=$a-$b;
                echo "<div>10 - 50 = <span class='value'>{$c}</span></div>";
                ?>
            </div>
        </div>
    </div>
    <!-- 固定右下的回作品集按鈕 -->
    <a href="../作品集.html" class="back-btn">回作品集</a>
</body>
</html>