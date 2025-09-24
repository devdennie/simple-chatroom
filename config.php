<?php
/**
 * Database configuration for Simple Chatroom Project
 * Uses PDO for secure connections to MySQL in XAMPP.
 * Supports: User registration (Gmail/email unique), password hashing/verification,
 *           conditional username edits (via is_default_password flag),
 *           and message storage/retrieval.
 */

// Database connection details (XAMPP defaults)
$host = "localhost";   
$db   = "chatroom_db";   
$user = "root";       
$pass = "";            
$charset = "utf8mb4";  // Supports emojis/special chars in usernames/messages

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors for debugging
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return results as associative arrays (e.g., ['id' => 1])
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements for security
];

function getPDO() {
    global $dsn, $user, $pass, $options;  // Use globals for simplicity; could pass as params for advanced use
    
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        // Optional: Test query to verify tables exist (uncomment for initial setup)
        // $pdo->query("SELECT 1 FROM users LIMIT 1");  // Or from messages if preferred
        return $pdo;
    } catch (\PDOException $e) {
        // Log error in production; die for local dev
        die("Database connection failed: " . $e->getMessage() . "<br>Check XAMPP MySQL is running and DB/tables exist.");
    }
}

// For quick global access (if not using the function), but prefer getPDO() in files
// $pdo = getPDO();  // Uncomment if you want a global $pdo
?>