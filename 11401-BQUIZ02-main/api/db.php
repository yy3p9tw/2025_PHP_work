<?php
session_start();
date_default_timezone_set("Asia/Taipei");

$Type=[
1=>'健康新知',
2=>'菸害防治',
3=>'癌症防治',
4=>'慢性病防治',
];

function dd($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

function q($sql){
    $dsn='mysql:host=localhost;dbname=db18;charset=utf8';
    $pdo=new PDO($dsn,'root','');
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function to($url){
    header("location:".$url);
}

class DB{
private $dsn="mysql:host=localhost;dbname=db18;charset=utf8";
private $pdo;   
private $table;

function __construct($table){
    $this->table=$table;
    $this->pdo=new PDO($this->dsn,"root",'');
}

function all(...$arg){
    $sql="select * from $this->table ";
    if(isset($arg[0])){
        if(is_array($arg[0])){
            $tmp=$this->arraytosql($arg[0]);
            $sql=$sql." where ".join(" AND " , $tmp);

        }else{
            $sql .= $arg[0];
        }
    }

    if(isset($arg[1])){
        $sql .= $arg[1];
    }

    return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function count(...$arg){
    $sql="select count(*) from $this->table ";
    if(isset($arg[0])){
        if(is_array($arg[0])){
            $tmp=$this->arraytosql($arg[0]);
            $sql=$sql." where ".join(" AND " , $tmp);

        }else{
            $sql .= $arg[0];
        }
    }

    if(isset($arg[1])){
        $sql .= $arg[1];
    }

    return $this->pdo->query($sql)->fetchColumn();
}

function sum($col,...$arg){
    $sql="select sum($col) from $this->table ";
    if(isset($arg[0])){
        if(is_array($arg[0])){
            $tmp=$this->arraytosql($arg[0]);
            $sql=$sql." where ".join(" AND " , $tmp);

        }else{
            $sql .= $arg[0];
        }
    }

    if(isset($arg[1])){
        $sql .= $arg[1];
    }

    return $this->pdo->query($sql)->fetchColumn();
}

function find($id){
    $sql="select * from $this->table ";
    
    if(is_array($id)){
        $tmp=$this->arraytosql($id);
        $sql=$sql." where ".join(" AND " , $tmp);

    }else{
        $sql .= " WHERE `id`='$id'";
    }
    //echo $sql;
    return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

function save($array){
    if(isset($array['id'])){
        //update
        $sql="update $this->table set ";
        $tmp=$this->arraytosql($array);
        $sql.= join(" , ",$tmp) . "where `id`= '{$array['id']}'";
    }else{
        //insert
        $cols=join("`,`",array_keys($array));
        $values=join("','",$array);
        $sql="insert into $this->table (`$cols`) values('$values')";
    }

    return $this->pdo->exec($sql);
}

function del($id){
    $sql="delete  from $this->table ";
    
    if(is_array($id)){
        $tmp=$this->arraytosql($id);
        $sql=$sql." where ".join(" AND " , $tmp);

    }else{
        $sql .= " WHERE `id`='$id'";
    }
    //echo $sql;
    return $this->pdo->exec($sql);
}


private function arraytosql($array){
    $tmp=[];
    foreach($array as $key => $value){
        $tmp[]="`$key`='$value'";
    }

    return $tmp;
}

}


$User=new DB('users');
$Visit=new DB("visit");
$News=new DB("news");
$Que=new DB("que");
$Log=new DB("log");
/* 測試資料庫連線及功能正常
$User->save(['acc'=>'test','pw'=>'5678','email'=>'test@labor.gov.tw']);
$User->save(['acc'=>'mem01','pw'=>'mem01','email'=>'mem01@labor.gov.tw']);
$User->save(['acc'=>'mem02','pw'=>'mem02','email'=>'mem02@labor.gov.tw'])


 */




if(!isset($_SESSION['visit'])){
    //第一次來訪
    $today=$Visit->find(['date'=>date("Y-m-d")]);
    if(empty($today)){
        //沒有今天的資料
        $Visit->save(['date'=>date("Y-m-d"),'visit'=>1]);
    }else{
        $today['visit']++;
        $Visit->save($today);
    }

    $_SESSION['visit']=1;
}





?>