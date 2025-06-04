<?php
function getPDO() {
    $dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
    return new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}
?>