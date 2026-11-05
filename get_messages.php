<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $pdo = getPDO();
    
    // Fetch last 50 messages (most recent first)
    $stmt = $pdo->prepare("
        SELECT m.id, m.username, m.message, m.timestamp 
        FROM messages m 
        WHERE m.room_id = 1 
        ORDER BY m.timestamp DESC 
        LIMIT 50
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Reverse to show oldest first (chronological order)
    $messages = array_reverse($messages);
    
    echo json_encode($messages);
    
} catch (PDOException $e) {
    // Driver text includes SQLSTATE and schema details. Keep it in the log.
    error_log('get_messages.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Unable to load messages.']);
}
?>