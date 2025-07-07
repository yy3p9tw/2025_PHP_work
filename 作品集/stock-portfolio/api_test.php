<?php
// 測試 API 回應
echo "=== Testing API Endpoints ===" . PHP_EOL;

// 測試 market-indices API
echo "Testing market-indices API..." . PHP_EOL;
$response = file_get_contents('http://localhost:8000/api/market-indices.php');
echo "Response: " . $response . PHP_EOL . PHP_EOL;

// 測試 stocks API
echo "Testing stocks API..." . PHP_EOL;
$response = file_get_contents('http://localhost:8000/api/stocks.php?action=hot');
echo "Response: " . $response . PHP_EOL . PHP_EOL;

// 測試 news API
echo "Testing news API..." . PHP_EOL;
$response = file_get_contents('http://localhost:8000/api/news.php?action=latest');
echo "Response: " . $response . PHP_EOL . PHP_EOL;

echo "=== API Testing Complete ===" . PHP_EOL;
?>
