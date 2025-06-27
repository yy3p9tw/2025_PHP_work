<?php
class DB {
    private $dsn = "mysql:host=localhost;dbname=store;charset=utf8mb4";
    private $user = "root"; // 本機預設帳號
    private $pw = "";      // 本機預設密碼（XAMPP/MAMP 通常為空）
    private $pdo;
    private $table;

    public function __construct($table) {
        $this->table = $table;
        $this->pdo = new PDO($this->dsn, $this->user, $this->pw, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * 取得全部資料
     * @param array $where 條件陣列，例如 ['column' => 'value'] 或 ['column >' => 'value']
     * @param string $orderBy 排序字串，例如 'id DESC'
     * @return array
     */
    public function all($where = [], $orderBy = "") {
        $sql = "SELECT * FROM `$this->table`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                if (strpos($key, ' ') !== false) { // 處理帶有運算符的條件，例如 'column >'
                    list($col, $op) = explode(' ', $key, 2);
                    $conditions[] = "`$col` $op :" . str_replace(' ', '', $col);
                    $params[str_replace(' ', '', $col)] = $value;
                } else {
                    $conditions[] = "`$key` = :$key";
                    $params[$key] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 取得單筆資料
     * @param array $where 條件陣列，例如 ['id' => $id]
     * @return array|null
     */
    public function find($where = []) {
        $sql = "SELECT * FROM `$this->table`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                if (strpos($key, ' ') !== false) {
                    list($col, $op) = explode(' ', $key, 2);
                    $conditions[] = "`$col` $op :" . str_replace(' ', '', $col);
                    $params[str_replace(' ', '', $col)] = $value;
                } else {
                    $conditions[] = "`$key` = :$key";
                    $params[$key] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " LIMIT 1"; // 只取一筆

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
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

    /**
     * 刪除資料
     * @param int|array $condition ID 或條件陣列，例如 $id 或 ['column' => 'value']
     * @return bool
     */
    public function delete($condition) {
        $sql = "DELETE FROM `{$this->table}`";
        $params = [];

        if (is_array($condition)) {
            $conditions = [];
            foreach ($condition as $key => $value) {
                if (strpos($key, ' ') !== false) {
                    list($col, $op) = explode(' ', $key, 2);
                    $conditions[] = "`$col` $op :" . str_replace(' ', '', $col);
                    $params[str_replace(' ', '', $col)] = $value;
                } else {
                    $conditions[] = "`$key` = :$key";
                    $params[$key] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        } else { // 假設是 ID
            $sql .= " WHERE id = :id";
            $params['id'] = $condition;
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>