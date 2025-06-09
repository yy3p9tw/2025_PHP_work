<?php 
$dsn = "mysql:host=localhost;dbname=store;charset=utf8"; // 設定資料庫連線字串（資料來源名稱）
$pdo = new PDO($dsn, 'root', ''); // 建立 PDO 資料庫連線物件，使用 root 帳號，密碼為空

// 查詢全部資料（可加條件）
function all($table, $where = null){ // 定義一個 all 函式，用來查詢資料表全部資料，可加條件
    global $pdo; // 使用全域的 PDO 連線物件
    $sql = "SELECT * FROM $table $where"; // 組合 SQL 查詢語法，$where 可加查詢條件
    //echo $sql; // （除錯用）顯示 SQL 語法
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); // 執行查詢並回傳所有資料（關聯式陣列）
    
}

// 查詢指定 id 的單筆資料
function find($table, $id) {
    global $pdo; // 使用全域的 PDO 連線物件
    $sql = "SELECT * FROM $table WHERE id = '$id'"; // 組合 SQL 查詢語法
    //echo $sql; // （除錯用）顯示 SQL 語法
    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC); // 執行查詢並回傳單筆資料（關聯式陣列）
// 美化輸出陣列內容
}
function dd($array){
    echo "<pre>"; // 用 <pre> 標籤讓輸出格式化
    print_r($array); // 輸出陣列內容
    echo "</pre>";
}

// 執行任意 SQL 查詢，回傳所有資料
function q($sql){
    global $pdo; // 使用全域的 PDO 連線物件
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); // 執行查詢並回傳所有資料（關聯式陣列）
}

// 更新資料表資料（根據條件）
function update($table, $cols, $where) {
    global $pdo; // 使用全域的 PDO 連線物件
    $set = [];
    foreach($cols as $col => $val){
        if($col == 'id') continue; // 如果欄位是 id，則跳過（不更新 id 欄位）
        $set[] = "`$col` = '$val'"; // 組合欄位與值
    }
    $setStr = implode(', ', $set); // 用逗號串接
    $sql = "UPDATE $table SET $setStr $where"; // 組合 SQL 更新語法
    //echo $sql; // （除錯用）顯示 SQL 語法
    // return $pdo->exec($sql); // 執行更新，回傳影響的資料筆數
}

// 新增資料到資料表
function insert($table, $cols) {
    global $pdo; // 使用全域的 PDO 連線物件
    $keys = array_keys($cols); // 取得所有欄位名稱
    $vals = array_values($cols); // 取得所有欄位值
    $keyStr = "`" . implode("`, `", $keys) . "`"; // 欄位名稱字串
    $valStr = "'" . implode("', '", $vals) . "'"; // 欄位值字串
    $sql = "INSERT INTO $table ($keyStr) VALUES ($valStr)"; // 組合 SQL 新增語法
    //echo $sql; // （除錯用）顯示 SQL 語法
    return $pdo->exec($sql); // 執行新增，回傳影響的資料筆數
}