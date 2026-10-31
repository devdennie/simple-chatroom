<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Update last_active on page load
try {
    $pdo = getPDO();
    $update_stmt = $pdo->prepare("UPDATE users SET last_active = CURRENT_TIMESTAMP WHERE id = ?");
    $update_stmt->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Silent fail
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch current user data
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
$is_default = (int)$current_user['is_default_password'];

// Sign-in and the chat gate store the "change the default password" prompt here.
$flash = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_pass'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';
    $new_username = trim($_POST['new_username'] ?? $current_username);
    
    // Validation
    if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = "All password fields are required!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_pass) < 5) {
        $error = "New password must be at least 5 characters!";
    } elseif (!password_verify($old_pass, $stored_hash)) {
        $error = "Incorrect old password!";
    } elseif ($new_username !== $current_username && strlen($new_username) < 3) {
        $error = "New username must be at least 3 characters!";
    } else {
        try {
            // Check if username changed and is unique (only if editing and default)
            $update_username = ($new_username !== $current_username && $is_default);
            if ($update_username) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$new_username, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $error = "Username already taken!";
                }
            } elseif ($new_username !== $current_username) {
                $error = "Username can only be changed if using default password!";
            }
            
            if (empty($error)) {
                // Hash new password
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $new_flag = 0;  // No longer default after change
                
                // Update DB
                if ($update_username) {
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, username = ?, is_default_password = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $new_username, $new_flag, $_SESSION['user_id']]);
                    $_SESSION['username'] = $new_username;  // Update session
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, is_default_password = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $new_flag, $_SESSION['user_id']]);
                }
                
                if ($update_username) {
                    session_regenerate_id(true);  // Security
                }
                
                $success = "Profile updated successfully!";
                if ($update_username) {
                    $success .= " Username is now locked for further edits.";
                }
                $_SESSION['message'] = $success;
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
    <link rel="stylesheet" href="chat.css">
    <meta charset="UTF-8">
    <title>Profile - Simple Chatroom</title>
</head>
<body>
    <div class="navbar">
        <a href="chat.php">Back to Chat</a>
        <a href="logout.php">Logout</a>
        <button class="mode-toggle" onclick="toggleMode()" id="mode-toggle">🌙</button>
    </div>
    
    <div class="profile-container">
        <h1>Profile Settings</h1>
        <?php if ($flash): ?>
            <p class="flash"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <p>Current Username: <strong><?php echo htmlspecialchars($current_username, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Old Password:</label>
            <input type="password" name="old_pass" required>
            
            <label>New Password:</label>
            <input type="password" name="new_pass" required minlength="5">
            
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_pass" required>
            
            <div id="username_field">
                <label>New Username (optional, if default password):</label>
                <?php /* Username is user-controlled, so escape it inside this attribute. */ ?>
                <input type="text" name="new_username" value="<?php echo htmlspecialchars($current_username, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100">
                <small>Leave blank to keep current.</small>
            </div>
            
            <br>
            <button type="submit">Update Profile</button>
        </form>
    </div>
    
    <script>
        // AJAX to check if default password (show/hide username field)
        fetch('is_default.php')
            .then(response => response.json())
            .then(data => {
                if (data.is_default) {
                    document.getElementById('username_field').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error checking default password:', error);
            });
        
        // Theme Toggle Functionality
        function toggleMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.getElementById('mode-toggle').textContent = isDark ? '☀️' : '🌙';
        }
        
        // Load saved theme on page load
        window.addEventListener('load', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('mode-toggle').textContent = '☀️';
            } else {
                document.getElementById('mode-toggle').textContent = '🌙';
            }
        });
    </script>
</body>
</html>