<?php
class DB {
    private $dsn = "mysql:host=sql306.infinityfree.com;dbname=if0_39295983_store;charset=utf8mb4";
    private $user = "if0_39295983"; // 請依您的 MySQL 帳號調整
    private $pw = "3jxu7ucRkSI";       // 請依您的 MySQL 密碼調整
    private $pdo;
    private $table;

    public function __construct($table) {
        $this->table = $table;
        $this->pdo = new PDO($this->dsn, $this->user, $this->pw, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    // 取得全部資料
    public function all($where = "") {
        $sql = "SELECT * FROM `$this->table`";
        if ($where) $sql .= " WHERE $where";
        return $this->pdo->query($sql)->fetchAll();
    }

    // 新增資料
    public function insert($data) {
        $keys = array_keys($data);
        $sql = "INSERT INTO `$this->table` (`" . implode('`,`', $keys) . "`) VALUES (:" . implode(',:', $keys) . ")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 更新資料
    public function update($id, $data) {
        $set = [];
        foreach ($data as $k => $v) {
            $set[] = "`$k` = :$k";
        }
        $sql = "UPDATE `$this->table` SET ".implode(',', $set)." WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 刪除資料（依 id）
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>