<?php
require_once 'includes/database.php';

$db = new Database();

echo "Testing database queries for transactions...\n";

// Test 1: Basic transaction count
try {
    echo "Test 1: Basic transaction count\n";
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM transactions");
    echo "Result: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 2: Count by type
try {
    echo "\nTest 2: Count by type (buy)\n";
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE type = 'buy'");
    echo "Result: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 3: Count by type (sell)
try {
    echo "\nTest 3: Count by type (sell)\n";
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE type = 'sell'");
    echo "Result: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 4: Total amount
try {
    echo "\nTest 4: Total amount\n";
    $result = $db->fetchOne("SELECT SUM(total_amount) as total FROM transactions");
    echo "Result: " . ($result['total'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 5: Total volume
try {
    echo "\nTest 5: Total volume\n";
    $result = $db->fetchOne("SELECT SUM(quantity) as total FROM transactions");
    echo "Result: " . ($result['total'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 6: Join query
try {
    echo "\nTest 6: Join query\n";
    $result = $db->fetchOne("
        SELECT COUNT(*) as count 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN stocks s ON t.stock_code = s.code
    ");
    echo "Result: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed.\n";
?>
