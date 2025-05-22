<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>線上日曆</title>
    <style>
        h1{
            text-align:center;
            color:blue;
        }
        table{
            min-width:60%;
            border-collapse:collapse;
            margin:0 auto;
        }
        td{
            border:1px solid blue;
            text-align:center;
            padding:5px 10px;
        }
        .today{
            background-color:yellow;
            font-weight:bold;
        }
        .other-month{
            background-color:gray;
            color:#aaa;
        }
        .holiday{
            background-color:pink;
            color:white;
            font-size:12px;

        }
        tr:not(tr:nth-child(1)) td:hover{
            background-color:lightblue;
            cursor:pointer;
            font-size:16px;
            font-weight:bold;
        }
        .pass-date{
            /* background-color:lightgray; */
            color:#aaa;
        }
        .date-num{
            font-size:14px;
            text-align:left;
        }
        .date-event{
            
            height:40px;
        }
        .box,.th-box{
            width:60px;
            height:80px;
            background-color:lightblue;
            display:inline-block;
            border:1px solid blue;
            box-sizing:border-box;
            margin-left:-1px;
            margin-top:-1px;
            vertical-align:top;
        }
        .box-container{
            width:420px;
            margin:0 auto;
            box-sizing:border-box;
            padding-left:1px;    
            padding-top:1px;    
        }
        .th-box{
            height:25px;
            text-align:center;
        }
        .day-num,.day-week{
            display:inline-block;
            width:50%;

        }
        .day-num{
            color:#999;
            font-size:14px;
        }
        .day-week{
            color:#aaa;
            font-size:12px;
            text-align:right;
        }
    </style>
</head>
<body>
<!-- <div class="box-container">
<?php

/* for($i=0;$i<20;$i++){
    echo "<div class='box'>";
        echo $i;
    echo "</div>";
} */
?>
</div> -->





 <h1>線上日曆</h1>  

 <?php
$month=5;
$today = date("Y-$month-d");
$firstDay = date("Y-$month-01");
$firstDayWeek = date("w", strtotime($firstDay));
$theDaysOfMonth=date("t", strtotime($firstDay));


$spDate=[
    '2025-04-04'=>'兒童節',
    '2025-04-05'=>'清明節',
    '2025-05-11'=>'母親節',
    '2025-05-01'=>'勞動節',
    '2025-05-30'=>'端午節',
    '2025-06-06'=>"生日"
];

$todoList=[ '2025-05-01'=>'開會'];

$monthDays=[];

//填入空白日期
for($i=0;$i<$firstDayWeek;$i++){
    $monthDays[]=[];
}

//填入當日日期
for($i=0;$i<$theDaysOfMonth;$i++){
        $timestamp = strtotime(" $i days", strtotime($firstDay));
        $date=date("d", $timestamp);
        $holiday="";
        foreach($spDate as $d=>$value){
            if($d==date("Y-m-d", $timestamp)){
                $holiday=$value;
            }
        }
        $todo='';
        foreach($todoList as $d=>$value){
            if($d==date("Y-m-d", $timestamp)){
                $todo=$value;
            }
        }
        $monthDays[]=[
            "day"=>date("d", $timestamp),
            "fullDate"=>date("Y-m-d", $timestamp),
            "weekOfYear"=>date("W", $timestamp),
            "week"=>date("w", $timestamp),
            "daysOfYear"=>date("z", $timestamp),
            "workday"=>date("N", $timestamp)<6?true:false,
            "holiday"=>$holiday,
            "todo"=>$todo
        ];
}

/* echo "<pre>";
print_r($monthDays);
echo "</pre>"; */


//建立外框及標題
echo "<div class='box-container'>";
     
echo "<div class='th-box'>日</div>";
echo "<div class='th-box'>一</div>";
echo "<div class='th-box'>二</div>";
echo "<div class='th-box'>三</div>";
echo "<div class='th-box'>四</div>";
echo "<div class='th-box'>五</div>";
echo "<div class='th-box'>六</div>";
     

//使用foreach迴圈,印出日期
foreach($monthDays as $day){
 
    echo "<div class='box'>";
    echo "<div class='day-info'>";
        echo "<div class='day-num'>";
        if(isset($day['day'])){

            echo $day["day"];
        }else{
            echo "&nbsp;";
        }
        echo "</div>";
        echo "<div class='day-week'>";
        if(isset($day['weekOfYear'])){
            echo $day["weekOfYear"];
        }else{
            echo "&nbsp;";
        }

        echo "</div>";
    echo "</div>";


    echo "<div class='holiday-info'>";
    if(isset($day['holiday'])){
        echo "<div class='holiday'>";
        echo $day['holiday'];
        echo "</div>";
    }else{
        echo "&nbsp;";
    }
    echo "</div>";
    echo "<div class='todo-info'>";
    if(isset($day['todo']) && !empty($day['todo'])){
        
            echo "<div class='todo'>";
            echo $day['todo'];
            echo "</div>";
        
    }else{
        echo "&nbsp;";
    }
    echo "</div>";
    echo "</div>";
}
echo "</div>";
?>
<h2 style='text-align:center;'><?=date("Y 年 m 月"); ?></h2>
 <table>
     <tr>
         <td>日</td>
         <td>一</td>
         <td>二</td>
         <td>三</td>
         <td>四</td>
         <td>五</td>
         <td>六</td>
     </tr>
<?php
for($i=0;$i<6;$i++){
    echo "<tr>";
    
    for($j=0;$j<7;$j++){
        $day=$j+($i*7)-$firstDayWeek;
        $timestamp = strtotime(" $day days", strtotime($firstDay));
        $date=date("Y-m-d", $timestamp);
        $class="";

        if(date("N",$timestamp)>5){
            $class=$class . " holiday";
        }

        if($today==$date){
            
            $class=$class . " today";
        }else if(date("m",$timestamp)!=date("m",strtotime($firstDay))){

            $class=$class ." other-month";
        }

        if($timestamp<strtotime($today)){
            $class=$class . " pass-date";
        }
        echo "<td class='$class' data-date='$date'>";
            echo "<div class='date-num'>";
                echo date("d",$timestamp);
            echo "</div>";
            echo "<div class='date-event'>";
                if(isset($spDate[$date])){
                    echo $spDate[$date];
                }
            echo "</div>";
        echo "</td>";
    }

    echo "</tr>";

}


?>
</table>

</body>
</html>