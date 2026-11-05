<?php
require_once 'session.php';
session_destroy();
header('Location: index.php');
exit();
?>