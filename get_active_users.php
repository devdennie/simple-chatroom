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
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>