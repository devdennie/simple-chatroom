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
    // Silent fail for now
}

// Display flash message if set
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="chat.css">
    <meta charset="UTF-8">
    <title>Simple Chatroom</title>
</head>
<body>
    <div class="navbar">
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
        <button class="mode-toggle" onclick="toggleMode()" id="mode-toggle">🌙</button>
    </div>
    
    <div class="main-chat">
        <h1>Chatroom</h1>
        <?php if ($message): ?>
            <p class="flash"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        
        <div id="chat-container">
            <div class="messages-section">
                <div id="messages"></div>
                <div class="input-row">
                    <input type="text" id="message_input" placeholder="Type a message..." maxlength="500">
                    <button onclick="sendMessage()">Send</button>
                </div>
            </div>
            
            <div id="user-activity">
                <h3>Online Users</h3>
                <div id="online-list"></div>
            </div>
        </div>
    </div>
    
    <script>
        let pollInterval;
        
        // Load initial messages and users
        loadMessages();
        loadActiveUsers();
        
        // Poll for new messages every 3 seconds
        pollInterval = setInterval(loadMessages, 3000);
        setInterval(loadActiveUsers, 5000); // Poll users every 5s
        
        function loadMessages(offset = 0) {
            fetch(`get_messages.php?offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById('messages');
                    if (data.error) {
                        alert('Error loading messages: ' + data.error);
                        return;
                    }
                    messagesDiv.innerHTML = '';  // Clear and rebuild
                    data.forEach(msg => {
                        // textContent, not innerHTML: username and message are stored
                        // user input, and innerHTML would run that HTML (stored XSS).
                        const username = msg.username == null ? '' : String(msg.username);
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'message';

                        const avatar = document.createElement('div');
                        avatar.className = 'avatar';
                        avatar.textContent = username.charAt(0).toUpperCase();

                        const usernameEl = document.createElement('span');
                        usernameEl.className = 'username';
                        usernameEl.textContent = username + ':';

                        const content = document.createElement('div');
                        content.className = 'message-content';
                        content.textContent = msg.message == null ? '' : String(msg.message);

                        const timestamp = document.createElement('span');
                        timestamp.className = 'timestamp';
                        timestamp.textContent = msg.timestamp == null ? '' : String(msg.timestamp);

                        msgDiv.append(avatar, usernameEl, content, timestamp);
                        messagesDiv.appendChild(msgDiv);
                    });
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;  // Auto-scroll to bottom
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    alert('Failed to load messages. Please refresh the page.');
                });
        }
        
        function loadActiveUsers() {
            fetch('get_active_users.php')
                .then(response => response.json())
                .then(data => {
                    const listDiv = document.getElementById('online-list');
                    if (data.error) {
                        listDiv.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">Error loading users</p>';
                        return;
                    }
                    listDiv.innerHTML = '';
                    if (data.length === 0) {
                        listDiv.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No users online</p>';
                        return;
                    }
                    data.forEach(user => {
                        // Same as messages: display names are user-controlled.
                        const username = user.username == null ? '' : String(user.username);
                        const userDiv = document.createElement('div');
                        userDiv.className = 'online-user';

                        const avatar = document.createElement('div');
                        avatar.className = 'avatar';
                        avatar.textContent = username.charAt(0).toUpperCase();

                        const name = document.createElement('span');
                        name.textContent = username;

                        userDiv.append(avatar, name);
                        listDiv.appendChild(userDiv);
                    });
                })
                .catch(error => {
                    console.error('Error loading active users:', error);
                    document.getElementById('online-list').innerHTML = '<p style="text-align: center; color: var(--text-secondary);">Error loading users</p>';
                });
        }
        
        function sendMessage() {
            const input = document.getElementById('message_input');
            const message = input.value.trim();
            if (!message) return;
            
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(message)
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    input.value = '';
                    loadMessages();  // Refresh immediately
                } else {
                    alert('Error sending message: ' + result);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Failed to send message. Check connection and try again.');
            });
        }
        
        // Send on Enter key
        document.getElementById('message_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
        
        // Clear interval on page unload (optional, for cleanup)
        window.addEventListener('beforeunload', () => clearInterval(pollInterval));
        
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