<?php
// This file is now located in the includes directory
function get_pdo() {
    $host = 'localhost';
    $dbname = 'if0_39295983_store';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // For a real application, you would log this error and show a generic message
        die("資料庫連線失敗: " . $e->getMessage());
    }
}
?>