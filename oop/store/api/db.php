<?php 

/*
dd($array)
$array: 要輸出的陣列 (array)
功能: 以pre格式印出陣列內容，方便除錯
*/
function dd($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}


/*
q($sql)
$sql: 欲執行的SQL語法 (string)
回傳: 查詢結果的關聯式陣列
*/
function q($sql){
    $dsn="mysql:host=localhost;dbname=store;charset=utf8";
    $pdo=new PDO($dsn,'root','');

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
}

/**
 * DB類別
 * 用於簡化資料庫操作
 * 提供常用的CRUD方法
 * @package DB
 * @version 1.0
 * 
 */

class DB{
    private $pdo;
    private $dsn="mysql:host=localhost;dbname=store;charset=utf8";
    private $table;
    public function __construct($table){
        $this->table=$table;
        $this->pdo=new PDO($this->dsn,'root','');
    }

/*
all($table, $array = null, $str = null)
$table: 資料表名稱 (string)
$array: 條件陣列 (array|null)，可選，若為陣列則產生 WHERE 條件
$str: 其他SQL語法 (string|null)，可選，附加在SQL語句後
回傳: 查詢結果的關聯式陣列
*/
function all($array=null,$str=null){
   
    $sql="SELECT * FROM $this->table ";

        if(is_array($array)){
            $tmp=$this->array2sql($array);
            $sql = $sql ." WHERE ".join(" AND ", $tmp);
        }else{
            $sql .= $array;
        }

        $sql .= $str;
 
    // echo $sql;
    $rows=$this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    return $rows;

}

/*
find($table, $id)
$table: 資料表名稱 (string)
$id: 主鍵值或條件陣列 (int|string|array)
    - 若為陣列，則產生多條件查詢
    - 若為單一值，則以id欄位查詢
回傳: 查詢到的單筆資料 (關聯式陣列)
*/
function find($id){
    if(is_array($id)){
        $tmp=$this->array2sql($id);
        $sql="SELECT * FROM $this->table WHERE ".join(" AND ",$tmp);
    }else{
        $sql="SELECT * FROM $this->table WHERE id='$id'";
    }

    //echo  $sql;

    return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

/*
update($table, $data)
$table: 資料表名稱 (string)
$data: 欲更新的資料陣列，必須包含id欄位 (array)
回傳: 執行結果 (成功筆數)
*/
function update($data){
    $tmp=$this->array2sql($data);

    $sql="UPDATE $this->table SET ".join(" , ",$tmp)."
                      WHERE id='{$data['id']}'";
    
    //echo $sql;
    return $this->pdo->exec($sql);

}

/*
insert($table, $data)
$table: 資料表名稱 (string)
$data: 欲新增的資料陣列 (array)
回傳: 執行結果 (成功筆數)
*/
function insert($data){

    $keys=array_keys($data);
    $sql="INSERT INTO $this->table (`".join("`,`",$keys)."`) values('".join("','",$data)."');";
    //echo $sql;
    return $this->pdo->exec($sql);
}

/*
save($table, $data)
$table: 資料表名稱 (string)
$data: 欲儲存的資料陣列 (array)
功能: 若$data有id欄位則執行update，否則執行insert
*/
function save($data){
    if(isset($data['id'])){
        $this->update($data);
    }else{
        $this->insert($data);
    }
}

/*
del($table, $id)
$table: 資料表名稱 (string)
$id: 主鍵值或條件陣列 (int|string|array)
    - 若為陣列，則產生多條件刪除
    - 若為單一值，則以id欄位刪除
回傳: 執行結果 (成功筆數)
*/
function del($id){
    $sql="DELETE FROM $this->table WHERE ";
    if(is_array($id)){
        $tmp=$this->array2sql($id);
        $sql .= join(" AND ",$tmp);
    }else{
        $sql .= "id='$id'";
    }

    //echo  $sql;

    return $this->pdo->exec($sql);
}

/*
array2sql($array)
$array: 欲轉換的條件或資料陣列 (array)
回傳: 轉換成SQL語法片段的陣列 (array)
*/
private function array2sql($array){
    $tmp=[];
    foreach($array as $key=>$value){
        $tmp[]="`$key`='$value'";
    }

    return $tmp;
}


}

$Item=new DB('items');
$Sales=new DB('sales');


