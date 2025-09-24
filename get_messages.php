<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT username, message, DATE_FORMAT(timestamp, '%H:%i:%s') AS timestamp FROM messages WHERE room_id = 1 ORDER BY timestamp DESC LIMIT 50");
    $stmt->execute();
    $messages = $stmt->fetchAll();
    // Reverse for chronological order
    $messages = array_reverse($messages);
    echo json_encode($messages);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>