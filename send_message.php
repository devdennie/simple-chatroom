<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid request method';
    exit();
}

$message = trim($_POST['message'] ?? '');
if (empty($message) || strlen($message) > 500) {
    echo 'Invalid message';
    exit();
}

try {
    $pdo = getPDO();
    
    // Get current username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        echo 'User  not found';
        exit();
    }
    
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO messages (user_id, username, message, room_id) 
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$_SESSION['user_id'], $user['username'], $message]);
    
    // Update last_active
    $update_stmt = $pdo->prepare("UPDATE users SET last_active = CURRENT_TIMESTAMP WHERE id = ?");
    $update_stmt->execute([$_SESSION['user_id']]);
    
    echo 'success';
} catch (PDOException $e) {
    error_log('Send message error: ' . $e->getMessage());
    echo 'Database error';
}
?>