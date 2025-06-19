<?php
header('Content-Type: text/html; charset=utf-8');

// 獲取POST數據
$num1 = isset($_POST['num1']) ? floatval($_POST['num1']) : 0;
$num2 = isset($_POST['num2']) ? floatval($_POST['num2']) : 0;
$operator = isset($_POST['operator']) ? $_POST['operator'] : '+';

// 計算結果
$result = 0;
$error = '';

switch($operator) {
    case '+':
        $result = $num1 + $num2;
        break;
    case '-':
        $result = $num1 - $num2;
        break;
    case '*':
        $result = $num1 * $num2;
        break;
    case '/':
        if($num2 == 0) {
            $error = '錯誤：不能除以零！';
        } else {
            $result = $num1 / $num2;
        }
        break;
    default:
        $error = '錯誤：無效的運算符！';
}

// 返回結果
if($error) {
    echo $error;
} else {
    echo sprintf("計算結果：%.2f %s %.2f = %.2f", $num1, $operator, $num2, $result);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>計算結果</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f0f2f5;
        }
        .result {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .back-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class='result'>
        <div class='error'></div>
        
    </div>
</body>
</html>