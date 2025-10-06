<?php
function getPDO() {
    $host = 'localhost';
    $dbname = 'chatroom_db';
    $username = 'root';
    $password = '';  // Default XAMPP empty password

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>