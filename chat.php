<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
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
    <meta charset="UTF-8">
    <title>Simple Chatroom</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        #messages { border: 1px solid #ccc; height: 400px; overflow-y: scroll; padding: 10px; margin-bottom: 10px; }
        #message_input { width: 70%; padding: 10px; }
        button { padding: 10px; }
        .message { margin: 5px 0; }
        .username { font-weight: bold; color: blue; }
        .timestamp { font-size: small; color: gray; }
        .flash { color: green; text-align: center; }
    </style>
</head>
<body>
    <h1>Chatroom</h1>
    <p>Welcome, <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>! 
       <a href="profile.php">Profile (Change Password)</a> | 
       <a href="logout.php">Logout</a></p>
    
    <?php if ($message): ?>
        <p class="flash"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <div id="messages"></div>
    <input type="text" id="message_input" placeholder="Type a message..." maxlength="500">
    <button onclick="sendMessage()">Send</button>
    
    <script>
        // Load initial messages
        loadMessages();
        
        // Poll for new messages every 3 seconds
        setInterval(loadMessages, 3000);
        
        function loadMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';  // Clear and rebuild
                    data.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'message';
                        msgDiv.innerHTML = `<span class="username">${msg.username}:</span> ${msg.message} <span class="timestamp">${msg.timestamp}</span>`;
                        messagesDiv.appendChild(msgDiv);
                    });
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;  // Auto-scroll to bottom
                })
                .catch(error => console.error('Error loading messages:', error));
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
            .catch(error => console.error('Error:', error));
        }
        
        // Send on Enter key
        document.getElementById('message_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>