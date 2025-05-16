<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI 結果</title>
    <style>
        body {
            font-family: "Microsoft JhengHei", sans-serif;
            background-color: #e0f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .result-box {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        h2 {
            color: #00796b;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>

<div class="result-box">
    <h2>BMI 計算結果</h2>
    <?php
    if (isset($_POST['height']) && isset($_POST['weight'])) {
        $height = $_POST['height'];
        $weight = $_POST['weight'];
        $bmi = $weight / ($height * $height);
        $bmi = round($bmi, 2);

        echo "<p>身高：{$height} 公尺</p>";
        echo "<p>體重：{$weight} 公斤</p>";
        echo "<p><strong>BMI：{$bmi}</strong></p>";
    } else {
        echo "<p>請從首頁輸入正確資料。</p>";
    }
    ?>
    <a href="bmi_main.php">回到首頁</a>
</div>

</body>
</html>