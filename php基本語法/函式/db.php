<?php 
$dsn="mysql:host=localhost;dbname=store;charset=utf8";
$pdo=new PDO($dsn,'root','');

/* all($table);                        // 查詢全部資料
   all($table,$array=[]);              // 查詢有條件的資料（陣列條件）
   all($table,$array,$str);            // 查詢有條件且有額外SQL字串（如排序）的資料
   all($table,$str);                   // 查詢有SQL字串條件的資料
*/

/**
 * 查詢資料表的所有資料，或依條件查詢
 * @param string $table 資料表名稱
 * @param array|string|null $array 查詢條件（陣列：欄位=值，或SQL字串）
 * @param string|null $str 額外SQL字串（如排序、LIMIT等）
 * @return array 回傳查詢結果的關聯式陣列
 */
function all($table,$array=null,$str=null){
    global $pdo; // 使用全域的 PDO 連線物件
   
    $sql="SELECT * FROM $table "; // 基本查詢語法

    if(is_array($array)){ // 如果條件是陣列
        $tmp=array2sql($array); // 轉換陣列為SQL片段
        $sql = $sql ." WHERE ".join(" AND ", $tmp); // 加入WHERE條件
    }else{
        $sql .= $array; // 如果是字串，直接加到SQL語法
    }

    $sql .= $str; // 加入額外SQL字串（如排序）

    // echo $sql; // 除錯用，顯示SQL語法
    $rows=$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); // 執行查詢並取得所有資料
    return $rows; // 回傳查詢結果
}

/**
 * 格式化輸出陣列內容（除錯用）
 * @param array $array 要輸出的陣列
 * @return void
 */
function dd($array){
    echo "<pre>"; // 格式化輸出
    print_r($array); // 輸出陣列內容
    echo "</pre>";
}

/**
 * 查詢單筆資料，可用id或多條件陣列
 * @param string $table 資料表名稱
 * @param int|array $id id值或多條件陣列
 * @return array|false 回傳單筆資料的關聯式陣列，查無資料回傳false
 */
function find($table,$id){
    global $pdo; // 使用全域的 PDO 連線物件

    if(is_array($id)){ // 如果$id是陣列（多條件查詢）
        $tmp=array2sql($id); // 轉換為SQL片段
        $sql="SELECT * FROM $table WHERE ".join(" AND ",$tmp); // 組合SQL語法
    }else{
        $sql="SELECT * FROM $table WHERE id='$id'"; // 以id查詢
    }

    //echo  $sql; // 除錯用

    return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC); // 回傳單筆資料
}

/**
 * 更新資料表的資料（依id欄位）
 * @param string $table 資料表名稱
 * @param array $data 欲更新的資料（必須包含id欄位）
 * @return int|false 回傳影響的資料筆數，失敗回傳false
 */
function update($table,$data){
    global $pdo; // 使用全域的 PDO 連線物件
    $tmp=array2sql($data); // 轉換資料為SQL SET片段

    $sql="UPDATE $table SET ".join(" , ",$tmp)."
                      WHERE id='{$data['id']}'"; // 組合SQL更新語法
    
    echo $sql; // 除錯用，顯示SQL語法
    return $pdo->exec($sql); // 執行更新，回傳影響筆數
}

/**
 * 新增資料到資料表
 * @param string $table 資料表名稱
 * @param array $data 欲新增的資料（欄位=>值）
 * @return int|false 回傳影響的資料筆數，失敗回傳false
 */
function insert($table,$data){
    global $pdo; // 使用全域的 PDO 連線物件
    $keys=array_keys($data); // 取得欄位名稱

    $sql="INSERT INTO $table (`".join("`,`",$keys)."`) values('".join("','",$data)."');"; // 組合SQL新增語法
    echo $sql; // 除錯用，顯示SQL語法
    return $pdo->exec($sql); // 執行新增，回傳影響筆數
}

/**
 * 新增或更新資料（有id欄位則更新，否則新增）
 * @param string $table 資料表名稱
 * @param array $data 欲新增或更新的資料
 * @return void
 */
function save($table,$data){
    if(isset($data['id'])){ // 如果有id欄位
        update($table,$data); // 執行更新
    }else{
        insert($table,$data); // 執行新增
    }
}

/**
 * 刪除資料（可用id或多條件陣列）
 * @param string $table 資料表名稱
 * @param int|array $id id值或多條件陣列
 * @return int|false 回傳影響的資料筆數，失敗回傳false
 */
function del($table,$id){
    global $pdo; // 使用全域的 PDO 連線物件
    $sql="DELETE FROM $table WHERE "; // 基本刪除語法
    if(is_array($id)){ // 如果$id是陣列（多條件刪除）
        $tmp=array2sql($id); // 轉換為SQL片段
        $sql .= join(" AND ",$tmp); // 組合多條件
    }else{
        $sql .= "id='$id'"; // 以id刪除
    }

    //echo  $sql; // 除錯用

    return $pdo->exec($sql); // 執行刪除，回傳影響筆數
}

/**
 * 將陣列轉換為SQL片段（欄位=值）
 * @param array $array 欲轉換的陣列（欄位=>值）
 * @return array 回傳SQL片段陣列
 */
function array2sql($array){
    $tmp=[]; // 建立一個空陣列，用來存放SQL片段
    foreach($array as $key=>$value){ // 遍歷陣列的每個鍵值對
        $tmp[]="`$key`='$value'"; // 將每個鍵值對轉成 `欄位`='值' 的格式，加入陣列
    }
    return $tmp; // 回傳組合好的SQL片段陣列
}

/**
 * 執行任意SQL查詢，回傳所有資料
 * @param string $sql 欲執行的SQL語法
 * @return array 回傳查詢結果的關聯式陣列
 */
function q($sql){
    global $pdo; // 使用全域的 PDO 連線物件
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); // 執行查詢並回傳所有資料（關聯式陣列）
}