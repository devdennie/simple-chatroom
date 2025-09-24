<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch current user data
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT password, username, is_default_password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
if (!$current_user) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$stored_hash = $current_user['password'];
$current_username = $current_user['username'];
$is_default = $current_user['is_default_password'];

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_pass'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';
    $new_username = $_POST['new_username'] ?? $current_username;
    
    // Validation
    if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = "All password fields are required!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters!";
    } elseif (!password_verify($old_pass, $stored_hash)) {
        $error = "Incorrect old password!";
    } else {
        try {
            // Check if username changed and is unique (only if default)
            $update_username = false;
            if ($is_default && $new_username !== $current_username) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$new_username, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $error = "Username already taken!";
                } else {
                    $update_username = true;
                }
            }
            
            if (empty($error)) {
                // Hash new password
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                
                // Update DB
                if ($update_username) {
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, username = ?, is_default_password = 0 WHERE id = ?");
                    $stmt->execute([$new_hash, $new_username, $_SESSION['user_id']]);
                    $_SESSION['username'] = $new_username;  // Update session
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, is_default_password = 0 WHERE id = ?");
                    $stmt->execute([$new_hash, $_SESSION['user_id']]);
                }
                
                $success = "Profile updated successfully! Flag set to custom.";
                $_SESSION['message'] = $success;  // Flash for chat.php
                header('Location: chat.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Simple Chatroom</title>
    <style>
        body { font-family: Arial; margin: 20px; max-width: 400px; }
        input { width: 100%; padding: 8px; margin: 5px 0; box-sizing: border-box; }
        button { padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
        #username_field { display: none; }  /* Hidden initially */
    </style>
</head>
<body>
    <h1>Profile Settings</h1>
    <p>Current Username: <strong><?php echo htmlspecialchars($current_username); ?></strong></p>
    <p><a href="chat.php">Back to Chat</a> | <a href="logout.php">Logout</a></p>
    
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label>Old Password:</label>
        <input type="password" name="old_pass" required>
        
        <label>New Password:</label>
        <input type="password" name="new_pass" required minlength="6">
        
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_pass" required>
        
        <div id="username_field">
            <label>New Username (optional, if default password):</label>
            <input type="text" name="new_username" value="<?php echo htmlspecialchars($current_username); ?>" maxlength="100">
        </div>
        
        <br>
        <button type="submit">Update Profile</button>
    </form>
    
    <script>
        // AJAX to check if default password (show/hide username field)
        fetch('is_default.php')
            .then(response => response.json())
            .then(data => {
                if (data.is_default) {
                    document.getElementById('username_field').style.display = 'block';
                }
            })
            .catch(error => console.error('Error checking default:', error));
    </script>
</body>
</html>