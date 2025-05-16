<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登錄結果</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-image: url('https://iili.io/3gzpqen.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .result-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .message {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .success {
            color: #28a745; /* 綠色表示成功 */
        }

        .failure {
            color: #dc3545; /* 紅色表示失敗 */
        }

        .back-button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            background-color: #ff6f91;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #e55a7a;
        }

        /* 響應式設計 */
        @media (max-width: 480px) {
            .result-container {
                padding: 1.5rem;
                margin: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="message <?php echo (isset($_POST['acc']) && isset($_POST['pw'])) ? 'success' : 'failure'; ?>">
            <?php
            if (isset($_POST['acc']) && isset($_POST['pw'])) {
                echo "登入成功";
            } else {
                echo "登入失敗";
            }
            ?>
        </div>
        <a href="php_main.php" class="back-button">返回登錄頁面</a>
    </div>
</body>
</html>