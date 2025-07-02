<?php
// D:\YU\church_website\backend\api\search.php

header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = $_GET['query'] ?? '';

    if (empty($query)) {
        echo json_encode([]);
        exit();
    }

    $results = [
        'sermons' => [],
        'events' => [],
        'news' => []
    ];

    $search_term = '%' . $query . '%';

    // 搜尋講道 (sermons)
    $stmt = $conn->prepare("SELECT id, title, date, speaker, content FROM sermons WHERE title LIKE ? OR content LIKE ?");
    $stmt->bind_param('ss', $search_term, $search_term);
    $stmt->execute();
    $sermons_result = $stmt->get_result();
    while ($row = $sermons_result->fetch_assoc()) {
        $results['sermons'][] = $row;
    }
    $stmt->close();

    // 搜尋活動 (events)
    $stmt = $conn->prepare("SELECT id, title, date, location, description FROM events WHERE title LIKE ? OR description LIKE ?");
    $stmt->bind_param('ss', $search_term, $search_term);
    $stmt->execute();
    $events_result = $stmt->get_result();
    while ($row = $events_result->fetch_assoc()) {
        $results['events'][] = $row;
    }
    $stmt->close();

    // 搜尋新聞 (news)
    $stmt = $conn->prepare("SELECT id, title, content, published_at FROM news WHERE title LIKE ? OR content LIKE ?");
    $stmt->bind_param('ss', $search_term, $search_term);
    $stmt->execute();
    $news_result = $stmt->get_result();
    while ($row = $news_result->fetch_assoc()) {
        $results['news'][] = $row;
    }
    $stmt->close();

    echo json_encode($results);

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => '不允許的請求方法。']);
}

$conn->close();
?>