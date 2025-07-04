<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $error;

    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';

        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        // Create a new PDO instance
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e){
            $this->error = $e->getMessage();
            error_log("資料庫連接錯誤: " . $this->error);
            die("資料庫連接失敗，請聯繫系統管理員");
        }
    }

    public function getConnection(){
        return $this->dbh;
    }

    // 執行查詢
    public function query($sql, $params = []){
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("SQL執行錯誤: " . $e->getMessage());
            throw new Exception("資料庫操作失敗");
        }
    }

    // 獲取單筆記錄
    public function fetchOne($sql, $params = []){
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // 獲取多筆記錄
    public function fetchAll($sql, $params = []){
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // 獲取記錄數量
    public function count($sql, $params = []){
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    // 插入記錄並返回ID
    public function insert($sql, $params = []){
        $this->query($sql, $params);
        return $this->dbh->lastInsertId();
    }

    // 更新記錄並返回影響行數
    public function update($sql, $params = []){
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // 刪除記錄並返回影響行數
    public function delete($sql, $params = []){
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
?>
