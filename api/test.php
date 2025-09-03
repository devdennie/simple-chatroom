<?php
require "config.php";   // import the DB connection
$stmt = $pdo->query("SELECT NOW() AS time");
$row = $stmt->fetch();
echo "Database connection successful! Server time: " . $row['time'];
