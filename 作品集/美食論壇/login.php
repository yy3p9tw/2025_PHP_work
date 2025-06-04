
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員登入</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 48px auto;
            background: #fffbe9;
            border-radius: 18px;
            box-shadow: 0 4px 24px #ffd58077;
            padding: 36px 32px 28px 32px;
        }
        .login-title {
            font-size: 2em;
            color: #ff9800;
            text-align: center;
            margin-bottom: 24px;
            letter-spacing: 2px;
            font-weight: bold;
        }
        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: #ff9800;
            font-weight: bold;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ffd580;
            border-radius: 8px;
            font-size: 1em;
            background: #fff8e1;
            transition: border 0.2s;
        }
        .login-form input:focus {
            border: 1.5px solid #ffb347;
            outline: none;
        }
        .login-form button {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(90deg, #ffb347 60%, #ffcc80 100%);
            color: #fff;
            font-size: 1.1em;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px #ffd58055;
            transition: background 0.2s;
        }
        .login-form button:hover {
            background: linear-gradient(90deg, #ff9800 60%, #ffb347 100%);
        }
        .login-form .reg-link {
            display: block;
            text-align: right;
            margin-top: 10px;
            font-size: 0.98em;
        }
        .login-form .reg-link a {
            color: #ff9800;
            text-decoration: underline;
        }
        .login-form .reg-link a:hover {
            color: #ff7043;
        }
        .msg {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 16px;
            font-size: 1em;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="login-container">
        <div class="login-title">會員登入</div>
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg"><?=htmlspecialchars($_GET['msg'])?></div>
        <?php endif; ?>
        <form class="login-form" action="login_check.php" method="post" autocomplete="off">
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" required maxlength="20" placeholder="請輸入帳號">

            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required minlength="6" maxlength="20" placeholder="請輸入密碼">

            <button type="submit">登入</button>
            <div class="reg-link">
                沒有帳號？<a href="reg.php">註冊</a>
            </div>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>    
</body>
</html>