<?php
include 'config.php';
require_once 'session.php';
require_once 'csrf.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT username, is_default_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(403);
        echo 'User not found';
        exit();
    }
    if ((int) $user['is_default_password'] === 1) {
        http_response_code(403);
        echo 'Change the default password before sending messages.';
        exit();
    }
} catch (PDOException $e) {
    error_log('Send message error: ' . $e->getMessage());
    echo 'Database error';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid request method';
    exit();
}

if (!csrf_validate()) {
    http_response_code(403);
    echo 'Invalid CSRF token';
    exit();
}

$message = trim($_POST['message'] ?? '');
$message_length = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
if (empty($message) || $message_length > 500) {
    echo 'Invalid message';
    exit();
}

try {
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
