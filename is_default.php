<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_default' => false]);
    exit();
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT is_default_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    echo json_encode(['is_default' => $user ? $user['is_default_password'] : false]);
} catch (PDOException $e) {
    echo json_encode(['is_default' => false]);
}
?>