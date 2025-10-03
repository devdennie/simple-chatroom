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
    
    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } else {
        try {
            $pdo = getPDO();
            
            // Check existing user
            $stmt = $pdo->prepare("SELECT id, username, is_default_password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Existing user: Login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_default'] = $user['is_default_password'];
                $_SESSION['message'] = "Welcome back!";
                header('Location: chat.php');
                exit();
            } else {
                // New user: Register with defaults
                $random_username = generateRandomUsername($pdo);
                $default_hash = password_hash('12345', PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (email, password, username, is_default_password) VALUES (?, ?, ?, 1)");
                $stmt->execute([$email, $default_hash, $random_username]);
                
                if ($pdo->lastInsertId()) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['email'] = $email;
                    $_SESSION['username'] = $random_username;
                    $_SESSION['is_default'] = 1;
                    $_SESSION['message'] = "Welcome! Username: $random_username. Default password: 12345. Change it in profile.";
                    header('Location: chat.php');
                    exit();
                } else {
                    $error = "Registration failed. Try again.";
                }
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
    <title>Simple Chatroom - Sign In</title>
    <style>body { font-family: Arial; text-align: center; margin: 50px; }</style>
</head>
<body>
    <h1>Welcome to Simple Chatroom</h1>
    <p>Enter your Gmail and password to sign in or register.</p>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="email">Gmail Address:</label><br><br>
        <input type="email" id="email" name="email" placeholder="example@gmail.com" required style="padding: 10px; width: 250px;"><br><br>
        
        <label for="password">Password:</label><br><br>
        <input type="password" id="password" name="password" placeholder="Enter password (new: leave blank for default '12345')" style="padding: 10px; width: 250px;"><br><br>
        
        <label><input type="checkbox" name="use_default" <?php echo empty($_POST['password']) ? 'checked' : ''; ?>> Use default password '12345' (for new users)</label><br><br>
        
        <input type="submit" value="Sign In / Register" style="padding: 10px 20px;">
    </form>
    <p><small>New users get a random username. Change password/username in profile!</small></p>
</body>
</html>