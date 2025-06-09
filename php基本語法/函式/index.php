
<!-- <?php include 'db.php'; ?> // 引入 db.php，載入資料庫連線與常用函式 -->
<style>
    *{
        font-family:'Courier New', Courier, monospace; /* 設定全站字型為等寬字型 */
    }
</style>
<?php

$all = all('sales'); // 查詢 sales 資料表的所有資料
dd($all); // 美化輸出 $all 陣列內容

$all_2 = all('sales', " where quantity >=2"); // 查詢 sales 資料表中數量大於等於2的資料
dd($all_2); // 美化輸出 $all_2 陣列內容

$q = q("select name from items order by price"); // 執行 SQL 查詢，取得 items 資料表依價格排序的 name 欄位
dd($q); // 美化輸出 $q 陣列內容

$find = find('items', 8); // 查詢 items 資料表中 id=8 的那一筆資料
dd($find); // 美化輸出 $find 陣列內容

$update = update('items', ['name' => '仰望星空派', 'price' => 80], "WHERE id = 10");
dd($update); // 更新 items 資料表中 id=10 的那一筆資料
