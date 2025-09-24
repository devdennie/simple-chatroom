<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo 'Unauthorized';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if (strlen($message) > 500 || strlen($message) < 1) {
        echo 'Invalid message length';
        exit();
    }
    
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, username, message, room_id) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], $message]);
        echo 'success';
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request';
}
?>