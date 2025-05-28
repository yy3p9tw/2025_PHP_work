
<?php
$month = $_GET['month'] ?? date("m");
$year = $_GET['year'] ?? date("Y");

$prev = ($month - 1 > 0) ? $month - 1 : 12;
$prevyear = ($month - 1 > 0) ? $year : $year - 1;
$next = ($month + 1 > 12) ? 1 : $month + 1;
$nextyear = ($month + 1 > 12) ? $year + 1 : $year;

$today = date("Y-$month-d");
$firstDay = date("Y-$month-01");
$firstDayWeek = date("w", strtotime($firstDay));
$theDaysOfMonth = date("t", strtotime($firstDay));

$spDate = [
    '2025-04-04'=>'兒童節',
    '2025-04-05'=>'清明節',
    '2025-05-01'=>'勞動節',
    '2025-05-11'=>'母親節',
    '2025-05-29'=>"生日",
    '2025-05-30'=>'端午節',
];

$todoList = [
    '2025-07-01'=>'韓國保寧泥漿節', '2025-04-13'=>'泰國潑水節', '2025-04-14'=>'泰國潑水節',
    '2025-04-15'=>'泰國潑水節', '2025-04-16'=>'泰國潑水節', '2025-07-03'=>'羅斯威爾UFO節',
    '2025-07-04'=>'羅斯威爾UFO節', '2025-07-05'=>'羅斯威爾UFO節', '2025-07-06'=>'羅斯威爾UFO節',
    '2025-04-9'=>'埃及聞風節', '2025-10-31'=>'墨西哥亡靈節', '2025-11-01'=>'墨西哥亡靈節',
    '2025-11-01'=>'墨西哥亡靈節', '2025-04-22'=>'草莓蛋糕之日', '2025-04-30'=>'荷蘭女王節',
    '2025-01-22'=>'草莓蛋糕之日', '2025-02-22'=>'草莓蛋糕之日', '2025-03-22'=>'草莓蛋糕之日',
    '2025-05-22'=>'草莓蛋糕之日', '2025-06-22'=>'草莓蛋糕之日', '2025-07-22'=>'草莓蛋糕之日',
    '2025-08-22'=>'草莓蛋糕之日', '2025-09-22'=>'草莓蛋糕之日', '2025-10-22'=>'草莓蛋糕之日',
    '2025-11-22'=>'草莓蛋糕之日', '2025-05-06'=>'可樂餅之日', '2025-05-25'=>'布丁之日',
];

$monthDays = [];
for ($i = 0; $i < $firstDayWeek; $i++) {
    $monthDays[] = [];
}
for ($i = 0; $i < $theDaysOfMonth; $i++) {
    $timestamp = strtotime(" +$i days", strtotime($firstDay));
    $fullDate = date("Y-m-d", $timestamp);
    $monthDays[] = [
        "day" => date("d", $timestamp),
        "fullDate" => $fullDate,
        "weekOfYear" => date("W", $timestamp),
        "holiday" => $spDate[$fullDate] ?? '',
        "todo" => $todoList[$fullDate] ?? ''
    ];
}
$highlightDate = $_GET['searchDate'] ?? null;
?>
