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
            width:50px;
            height:50px;
            background-color:lightblue;
            display:inline-block;
            border:1px solid blue;
            box-sizing:border-box;
            margin-left:-1px;
            margin-top:-1px;
        }
        .box-container{
            width:350px;
            margin:0 auto;
            box-sizing:border-box;
            padding-left:1px;    
            padding-top:1px;    
        }
        .th-box{
            height:25px;
            text-align:center;
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
$month=7;
$today = date("Y-$month-d");
$firstDay = date("Y-$month-01");
$firstDayWeek = date("w", strtotime($firstDay));
$theDaysOfMonth=date("t", strtotime($firstDay));


$spDate=[
    '2025-05-11'=>'母親節',
    '2025-05-01'=>'勞動節',
    '2025-05-30'=>'端午節'
];

$monthDays=[];

//填入空白日期
for($i=0;$i<$firstDayWeek;$i++){
    $monthDays[]="&nbsp;";
}

//填入當日日期
for($i=0;$i<$theDaysOfMonth;$i++){
        $timestamp = strtotime(" $i days", strtotime($firstDay));
        $date=date("d", $timestamp);
        $monthDays[]=$date;
}


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
    echo $day;
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