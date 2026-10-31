<?php
include 'config.php';

header('Content-Type: application/json');

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT id, username 
        FROM users 
        WHERE last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY last_active DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
} catch (PDOException $e) {
    // Do not send the driver message to the browser; it can reveal the schema.
    error_log('get_active_users.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Unable to load users.']);
}
?>