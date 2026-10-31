<?php
include 'config.php';
session_start();

// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit();
}

// Function for random unique username
function generateRandomUsername($pdo) {
    $adjectives = ['Cool', 'Swift', 'Blue', 'Red', 'Smart', 'Quick', 'Happy', 'Brave'];
    $nouns = ['Fox', 'Eagle', 'River', 'Star', 'Wolf', 'Bear', 'Lion', 'Tiger'];
    $max_retries = 20;
    $attempt = 0;
    
    while ($attempt < $max_retries) {
        $adj = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        $num = rand(100, 999);
        $username = $adj . $noun . $num;
        
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $attempt++;
    }
    // Fallback
    return 'User ' . uniqid();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password'] ?? '');
    
    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } else {
        try {
            $pdo = getPDO();
            
            // Check existing user
            $stmt = $pdo->prepare("SELECT id, username, password, is_default_password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // A blank password used to skip password_verify when
                // is_default_password was set, which signed anyone in.
                // Always require the password and check it against the hash.
                $matches = password_verify($password, $user['password']);
                if ($password === '' || !$matches) {
                    $error = $password === '' ? "Password is required!" : "Incorrect password!";
                    goto display_form;
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_default'] = $user['is_default_password'];
                
                // Update last_active
                $update_stmt = $pdo->prepare("UPDATE users SET last_active = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->execute([$user['id']]);
                
                if ((int) $user['is_default_password'] === 1) {
                    // Default password 12345 matched. Send them to change it
                    // before the chat opens.
                    $_SESSION['message'] = "You are using the default password. Please change it before using the chat.";
                    header('Location: profile.php');
                    exit();
                }
                
                $_SESSION['message'] = "Welcome back!";
                header('Location: chat.php');
                exit();
            } else {
                // New user: Register with defaults
                $random_username = generateRandomUsername($pdo);
                $default_hash = password_hash('12345', PASSWORD_DEFAULT);
                $pass_hash = empty($password) ? $default_hash : password_hash($password, PASSWORD_DEFAULT);
                $is_default = empty($password) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO users (email, password, username, is_default_password, last_active) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $stmt->execute([$email, $pass_hash, $random_username, $is_default]);
                
                if ($pdo->lastInsertId()) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['email'] = $email;
                    $_SESSION['username'] = $random_username;
                    $_SESSION['is_default'] = $is_default;
                    $_SESSION['message'] = "Welcome! Username: $random_username. " . (empty($password) ? "Default password: 12345. " : "") . "Change it in profile.";
                    header('Location: chat.php');
                    exit();
                } else {
                    $error = "Registration failed. Try again.";
                }
            }
        } catch (PDOException $e) {
            error_log('index.php: ' . $e->getMessage());
            $error = "A database error occurred. Please try again.";
        }
    }
}

display_form:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="chat.css">
    <meta charset="UTF-8">
    <title>Simple Chatroom - Sign In</title>
</head>
<body>
    <div class="login-header">
        <button class="mode-toggle" onclick="toggleMode()" id="mode-toggle">☀️</button>
    </div>
    
    <div class="login-container">
        <h1>Welcome to Dennie's Chatroom</h1>
        <p>Enter your Gmail to sign in or register.</p>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Gmail Address:</label>
            <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Leave blank for default '12345' (new users)">
            
            <label><input type="checkbox" name="use_default" <?php echo empty($_POST['password']) ? 'checked' : ''; ?>> Use default password '12345' (for new users)</label>
            
            <br><br>
            <input type="submit" value="Sign In / Register">
        </form>
        <p><small>New users get a random username.</small></p>
    </div>
    
    <script>
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