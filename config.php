<?php
/**
 * Opens the chatroom database.
 *
 * Defaults match a stock XAMPP MariaDB install (user root, empty password).
 * To use a different account, copy config.example.php to config.local.php
 * and return the keys to override. config.local.php is gitignored.
 */
function getPDO() {
    $host = 'localhost';
    $dbname = 'chatroom_db';
    $username = 'root';
    $password = '';

    $localFile = __DIR__ . '/config.local.php';
    if (is_file($localFile)) {
        $local = require $localFile;
        if (!is_array($local)) {
            die('config.local.php must return an array of database settings.');
        }
        $host = $local['host'] ?? $host;
        $dbname = $local['dbname'] ?? $dbname;
        $username = $local['username'] ?? $username;
        // array_key_exists so an explicit empty password (XAMPP) is kept.
        $password = array_key_exists('password', $local) ? $local['password'] : $password;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
