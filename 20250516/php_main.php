<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登錄頁面</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-image: url('https://iili.io/3gzpqen.png');
            background-size: cover; /* 背景圖片覆蓋整個屏幕 */
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.9); /* 半透明白色背景 */
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px; /* 限制表單最大寬度 */
            text-align: center;
        }

        .acc {
            margin-bottom: 1.5rem;
        }

        .acc label {
            display: block;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .acc input[type="text"],
        .acc input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .acc input:focus {
            outline: none;
            border-color: #ff6f91; /* 聚焦時邊框顏色 */
        }

        .submit {
            display: flex;
            justify-content: center;
            gap: 1rem; /* 按鈕間距 */
        }

        .submit input[type="submit"],
        .submit input[type="reset"] {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit input[type="submit"] {
            background-color: #ff6f91; /* 主按鈕顏色 */
            color: white;
        }

        .submit input[type="submit"]:hover {
            background-color: #e55a7a; /* 懸停效果 */
        }

        .submit input[type="reset"] {
            background-color: #ccc;
            color: #333;
        }

        .submit input[type="reset"]:hover {
            background-color: #b3b3b3;
        }

        /* 響應式設計 */
        @media (max-width: 480px) {
            .form-container {
                padding: 1.5rem;
                margin: 0 1rem;
            }

            .submit {
                flex-direction: column; /* 小屏幕時按鈕垂直排列 */
            }

            .submit input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <form action="pin.php" method="post" class="form-container">
        <div class="acc">
            <label for="acc">帳號：</label>
            <input type="text" name="acc" id="acc" required>
        </div>
        <div class="acc">
            <label for="pw">密碼：</label>
            <input type="password" name="pw" id="pw" required>
        </div>
        <div class="submit">
            <input type="submit" value="登入">
            <input type="reset" value="清空內容">
        </div>
    </form>
</body>
</html>