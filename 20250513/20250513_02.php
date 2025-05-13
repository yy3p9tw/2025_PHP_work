<!DOCTYPE html>
<?php
// header("Refresh: 1"); // 每隔 1 秒重新整理當前頁面
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>時間及日期</title>
</head>

<body>
    <h1>日期/時間</h1>
    <h2>基本函式使用</h2>
    <?php

    date_default_timezone_set("Asia/Taipei"); //設定時區(亞洲台北)
    echo "台北:";
    echo date("Y-m-d H:i:s"); //取得當前的日期和時間
    echo "<br>";
    date_default_timezone_set("Asia/Tokyo");
    echo "日本:";
    echo date("Y-m-d H:i:s");
    ?>
    <h2>時間戳記</h2>
    <?php
    $timestamp = time(); //取得當前的時間戳記
    echo "當前時間戳記: " . $timestamp . "<br>";

    //將字串轉換為時間戳記
    $dateString = "2023-05-13 12:00:00";
    $timestampFromString = strtotime($dateString); //將字串轉換為時間戳記
    echo "字串轉換為時間戳記: " . $timestampFromString . "<br>";

    //將時間戳記轉換為字串  
    $timestampToString = date("Y-m-d H:i:s", $timestampFromString); //將時間戳記轉換為字串
    echo "時間戳記轉換為字串: " . $timestampToString . "<br>";
    ?>
    <?php
    $date1 = "2023-05-13";
    $date2 = "2023-05-21";
    $timestamp1 = strtotime($date1); //將字串轉換為時間戳記
    $timestamp2 = strtotime($date2); //將字串轉換為時間戳記
    $diff = $timestamp2 - $timestamp1; //計算時間差
    // echo "時間差: " . $diff . "秒<br>";
    $days = floor($diff / (60 * 60 * 24)); //計算天數
    echo "天數: " . $days . "天<br>";
    ?>
    <h2>計算距離自己下一次生日還有幾天</h2>
    <?php
    $birthday = "2025-05-29"; //設定自己的生日
    $birthday_array = explode("-", $birthday); //將字串轉換為陣列
    $birthday_array[0] = date("Y"); //將陣列的第一個元素設為當前年份
    $nextBirthday = join("-", $birthday_array); //將陣列轉換為字串
    $today = strtotime(date("Y-m-d")); //取得當前的時間戳記
    $birthdayTimestamp = strtotime($nextBirthday); //將字串轉換為時間戳記

    if ($today > $birthdayTimestamp) {
        // 如果今天已經過了生日，則計算明年的生日
        $nextBirthday = strtotime("+1 year", $birthdayTimestamp);
    } else {
        // 否則，計算今年的生日
        $nextBirthday = $birthdayTimestamp;
    }
    echo "距離下次生日還有 " . floor(($nextBirthday - $today) / (60 * 60 * 24)) . " 天<br>";
    ?>
    <h2>利用date()函式的格式化參數，完成以下的日期格式呈現</h2>
    <ul>
        <li>2021/10/05</li>
        <li>10月5日 Tuesday</li>
        <li>2021-10-5 12:9:5</li>
        <li>2021-10-5 12:09:05</li>
        <li>今天是西元2021年10月5日 上班日(或假日)</li>
    </ul>
    <?php
    $today = strtotime(date("Y-m-d")); //取得當前的時間戳記
    $todayString = date("Y/m/d", $today); //將時間戳記轉換為字串
    echo $todayString . "<br>";
    $todayString = date("n月j日 l", $today); //將時間戳記轉換為字串
    echo $todayString . "<br>";
    $todayString = date("Y-n-j G:i:s", $today); //將時間戳記轉換為字串
    echo $todayString . "<br>";
    if (date("w", $today) == 0 || date("w", $today) == 6) {
        //如果是星期六或星期日
        $workday = "假日";
    } else {
        $workday = "上班日";
    }
    echo "今天是 " . $todayString . " " . $workday . "<br>";
    ?>
    <h2>利用迴圈來計算連續五個周一的日期</h2>
    <?php
    $monday = strtotime("next Monday", $today);
    for ($i = 0; $i < 5; $i++) {
        $temp = strtotime("+$i week", $monday);
        echo date("Y-m-d", $temp) . "<br>";
    }
    ?>
    <h2>線上月曆製作</h2>
    <ul>
        <li>以表格方式呈現整個月份的日期</li>
        <li>可以在特殊日期中顯示資訊(假日或紀念日)</li>
        <li>嘗試以block box或flex box的方式製作月曆</li>
    </ul>
    <?php

   ?>
</body>

</html>