<!-- filepath: d:\YU\作品集\PHP基本語法\如果....就switch_case.php -->
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選擇結構</title>
    <link rel="stylesheet" href="switch_case.css">
</head>

<body>
    <div class="container">
        <h1>判斷成績</h1>
        <div class="section">
            <div class="desc">給定一個成績數字,判斷是否及格(60)分</div>
            <?php
            $score = 60;
            if (!is_numeric($score) || $score > 100 || $score < 0) {
                echo "<div class='error'>請輸入合法的成績數字</div>";
                exit();
            }
            echo "<div class='score'>你的成績是: <span class='score-num'>{$score}分</span></div>";
            if ($score >= 60) {
                echo "<div class='result pass'>及格</div>";
            } else {
                echo "<div class='result fail'>不及格</div>";
            }
            ?>
        </div>
        <div class="section">
            <h2>分配成績等級</h2>
            <div class="desc">給定一個成績數字，根據成績所在的區間，給定等級</div>
            <div class="grade-list">
                <div class="grade-item">0 ~ 59 <span class="arrow">→</span> E</div>
                <div class="grade-item">60 ~ 69 <span class="arrow">→</span> D</div>
                <div class="grade-item">70 ~ 79 <span class="arrow">→</span> C</div>
                <div class="grade-item">80 ~ 89 <span class="arrow">→</span> B</div>
                <div class="grade-item">90 ~ 100 <span class="arrow">→</span> A</div>
            </div>
            <?php
            if ($score >= 0 && $score <= 59) {
                $level = "E";
            } else if ($score >= 60 && $score <= 69) {
                $level = "D";
            } else if ($score >= 70 && $score <= 79) {
                $level = "C";
            } else if ($score >= 80 && $score <= 89) {
                $level = "B";
            } else {
                $level = "A";
            }
            echo "<div class='level'>等級：<span class='level-badge'>{$level}</span></div>";
            ?>
        </div>
        <div class="section">
            <h2>成績評語</h2>
            <div class="comment">
            <?php
            switch ($level) {
                case "A":
                    echo "棒棒棒棒棒";
                    break;
                case "B":
                    echo "棒棒棒棒";
                    break;
                case "C":
                    echo "棒棒棒";
                    break;
                case "D":
                    echo "棒棒";
                    break;
                case "E":
                    echo "棒";
                    break;
                default:
                    echo "請告知工程人員";
            }
            ?>
            </div>
        </div>
        <div class="section">
            <h2>閏年判斷</h2>
            <div class="desc">給定一個西元年份，判斷是否為閏年</div>
            <div class="leap-info">
                <div>地球對太陽的公轉一年的真實時間大約是365天5小時48分46秒，因此以365天定為一年 的狀況下，每年會多出近六小時的時間，所以每隔四年設置一個閏年來消除這多出來的一天。</div>
                <div>公元年分除以4不可整除，為平年。</div>
                <div>公元年分除以4可整除但除以100不可整除，為閏年。</div>
                <div>公元年分除以100可整除但除以400不可整除，為平年。</div>
            </div>
            <?php
            $year = 2200;
            $result = "";
            if ($year % 4 == 0) {
                if ($year % 100 != 0) {
                    $result = "閏年";
                } else {
                    if ($year % 400 != 0) {
                        $result = "平年";
                    } else {
                        $result = "閏年";
                    }
                }
            } else {
                $result = "平年";
            }
            echo "<div class='leap-result'>{$year} 年：{$result}</div>";
            // 簡化
            $year = 2200;
            $result2 = ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) ? "閏年" : "平年";
            echo "<div class='leap-result'>（簡化判斷）{$year} 年：{$result2}</div>";
            ?>
        </div>
    </div>
    <!-- 固定右下的回作品集按鈕 -->
    <a href="../作品集.html" class="back-btn">回作品集</a>
</body>

</html>