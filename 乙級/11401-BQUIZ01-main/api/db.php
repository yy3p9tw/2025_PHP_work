<?php
session_start();
date_default_timezone_set("Asia/Taipei");
function dd(){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}
function q($sql){
    $dsn = 'mysql:host=localhost;dbname=db09;charset=utf8';
    $pdo = new PDO($dsn, 'root', '');
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function to($url){
    header("Location: $url");
}
class DB {
    private $dsn = 'mysql:host=localhost;dbname=db09;charset=utf8';
    protected $table;
    protected $pdo;

    function __construct($table) {
        $this->table = $table;
        $this->pdo = new PDO($this->dsn, 'root', '');
    }

    function all(...$arg) {
        $sql = "SELECT * FROM {$this->table} " ;
        if(isset($arg[0])){
            if(is_array($arg[0])){
                $tmp = $this->arraytosql($arg[0]);
                $sql=$sql . "WHERE " . join(" AND ", $tmp);
            } else {
                $sql .= $arg[0];
            }
        }
        if(isset($arg[1])){
            $sql .=  $arg[1];
        }
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    function find($id) {
        
    }
   function save($array) {

    }
   function del($id) {

    }
    private function arraytosql($array) {
        $tmp = [];
        foreach ($array as $key => $value) {
            $tmp[] = "`$key` = '$value'";
        }
        return $tmp;  
}
}
?>