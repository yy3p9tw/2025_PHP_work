<?php
// filepath: d:\YU\JavaScript練習\jav\calculate.php

header('Content-Type: text/plain; charset=utf-8');

$num1 = isset($_POST['num1']) ? floatval($_POST['num1']) : 0;
$num2 = isset($_POST['num2']) ? floatval($_POST['num2']) : 0;
$operator = isset($_POST['operator']) ? $_POST['operator'] : '+';

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

if($error) {
    echo $error;
} else {
    echo sprintf("計算結果：%.2f %s %.2f = %.2f", $num1, $operator, $num2, $result);
}