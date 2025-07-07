<?php
require_once 'includes/database.php';

try {
    $db = new Database();
    $result = $db->fetchAll('SHOW TABLES');
    echo 'Tables in database: ' . PHP_EOL;
    foreach ($result as $table) {
        echo '- ' . implode(', ', $table) . PHP_EOL;
    }
    
    // 檢查是否有市場指數資料
    $indices = $db->fetchAll('SELECT COUNT(*) as count FROM market_indices');
    echo 'Market indices count: ' . $indices[0]['count'] . PHP_EOL;
    
    // 檢查是否有股票資料
    $stocks = $db->fetchAll('SELECT COUNT(*) as count FROM stocks');
    echo 'Stocks count: ' . $stocks[0]['count'] . PHP_EOL;
    
    // 檢查是否有新聞資料
    $news = $db->fetchAll('SELECT COUNT(*) as count FROM news');
    echo 'News count: ' . $news[0]['count'] . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
