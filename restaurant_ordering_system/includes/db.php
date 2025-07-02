<?php
// Unified and secure database connection using PDO

$host = '127.0.0.1';
$db   = 'restaurant_ordering_system'; // Correct database name
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // For security, don't show detailed errors in production.
    // Log the error and show a generic message.
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>