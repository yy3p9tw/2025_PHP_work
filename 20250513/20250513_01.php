<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>字串處理</title>
    <style>
        h1 {
            text-align: center;
            color: lightblue;
            font-size: 3em;
            border-bottom: 1px solid lightblue;
            padding-bottom: 10px;
        }

        h2 {
            text-align: center;
            color: lightgreen;
            font-size: 2em;
            border-bottom: 1px solid lightgreen;
            padding-bottom: 10px;
        }

        p,
        li {
            text-align: center;
            color: lightcoral;
            font-size: 1.5em;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <h1>字串處理</h1>
    <h2>自串取代</h2>
    <p>將”aaddw1123”改成”*********”</p>
    <?php
    $a = "aaddw123512351321123";
    // $a= str_replace("a","*",$a);
    // $a = str_replace("d","*",$a);
    // $a = str_replace("w","*",$a);
    // $a = str_replace("1","*",$a);
    // $a = str_replace("2","*",$a);
    // $a = str_replace("3","*",$a);
    // $a = str_replace("aaddw1123", "*********", $a);
    // $a=str_replace(['a','d','w','1','2','3'],'*',$a);
    // $a=str_replace(str_split($a,1),'*',$a);
    $a = str_repeat('*', strlen($a));
    echo $a;
    ?>
    <h2>字串分割</h2>
    <p>將”this,is,a,book”依”,”切割後成為陣列</p>
    <?php
    $a = "this,is,a,book";
    $a = explode(",", $a);
    echo $a[3];
    ?>
    <h2>字串組合</h2>
    <p>將上例陣列重新組合成“this is a book”</p>
    <?php
    // $a = "this,is,a,book";
    // $a = explode(",", $a);
    // echo implode(" ", $a);
    $a = join(" ", $a);
    echo $a;
    ?>
    <h2>子字串取用</h2>
    <p>將” The reason why a great man is great is that he resolves to be a great man”只取前十字成為” The reason…”</p>
    <?php
    $a = " The reason why a great man is great is that he resolves to be a great man";
    $a = mb_substr($a, 0, 10, 'utf-8');
    echo $a;
    ?>
    <h2>尋找字串與HTML、css整合應用</h2>
    <ul>
        <li>給定一個句子，將指定的關鍵字放大</li>
        <li>學會PHP網頁<span style='font-size: 28px;color:aquamarine'>程式設計</span>，薪水會加倍，工作會好找</li>
        <li>請將上句中的 “程式設計” 放大字型或變色.</li>
    </ul>
    <?php
    $str = "受到風向變化影響，本週全台天氣逐步轉變。氣象署指出，13日（週二）以多雲到晴為主，降雨不多，但從14日起降雨機率將逐日增加，預估16日至19日（週五至下週一）午後雷陣雨機率上升，東半部及山區尤為明顯。氣象署提醒民眾留意降雨趨勢，而本週中南部白天仍偏熱，北部與東部則有明顯的溫差。

氣象署說明，13日全台大致穩定，僅東半部與中南部山區零星降雨。隨後14日至15日（週三至週四）在東南風影響下，午後降雨增加，尤以中部以北與東北部降雨較明顯，其中14日東北部更可能有局部性大雨。至於16日至19日（週五至下週一），風向轉為偏南至西南風，加上熱對流作用，午後雷陣雨機率提高，並擴及東半部及西部山區，提醒民眾留意突發性短時降雨。

氣象署進一步表示，未來幾日各地白天氣溫高、日夜溫差大，北部、宜蘭與花東清晨偏涼，但白天高溫普遍落在28至32度之間，南部甚至可達33度。整體來看，各地以晴時多雲為主，清晨與夜間可能感受微涼，建議早出晚歸的民眾適時增減衣物。

根據氣象署繪製的降雨趨勢圖，從14日起，降雨範圍由中部山區逐步擴展，至週末期間涵蓋全台大部分地區，色階顯示局部區域累積雨量可達中雨等級。氣象署補充，雖然目前仍屬春末季節，但隨著午後對流旺盛發展，山區與迎風面地區的短時強降雨需格外注意。

氣象署也觀察到，從5月12日至19日，台灣將持續受不同風向影響，前段週期以偏東風與東南風為主，後段轉為偏南至西南風，有利水氣增多。若午後出現雷陣雨，容易伴隨短時強降雨，外出活動建議攜帶雨具備用。

從最新氣溫趨勢圖來看，未來一週的高低溫落差明顯，氣象署提醒民眾特別注意清晨與夜間的氣溫變化，尤其是北部與花東地區最為明顯，日夜溫差甚至達到10度以上。對於即將迎接週末活動的民眾，氣象署建議應留意天氣轉變訊息，避免在午後雷陣雨期間安排登山、戶外活動。如遇強降雨或雷擊，也務必暫避開闢空曠地區，以策安全。";

    $keywords = [['content' => "天氣", 'style' => "font-size: 28px;color:blue", 'url' => "https://www.cwb.gov.tw/V8/C/W/index.html"], ['content' => "降雨", 'style' => "font-size: 28px;color:red", 'url' => ""], ['content' => "雷陣雨", 'style' => "font-size: 28px;color:green", 'url' => "https://www.cwb.gov.tw/V8/C/W/ALL/ALL.html"], ['content' => "風向", 'style' => "font-size: 28px;color:purple", 'url' => "https://www.cwb.gov.tw/V8/C/W/ALL/ALL.html"]];
    foreach ($keywords as $keyword) {
        $a = "<a href='{$keyword['url']}' style='{$keyword['style']}'>{$keyword['content']}</a>";
        $span = "<span style='{$keyword['style']}'>{$keyword['content']}</span>";
        if ($keyword['url'] != "") {
            $str = str_replace($keyword['content'], $a, $str);
        } else {
            $str = str_replace($keyword['content'], $span, $str);
        }
    }

    echo $str;

    ?>
    <hr>

</body>

</html>