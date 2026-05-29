<?php
session_start();
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (!$user): ?>
    <div class="auth-page" id="auth-root">
        <div class="auth-card">
            <div class="auth-intro">
                <h1>AI Chatbot Access</h1>
                <p>Login or sign up to start chatting with your AI assistant.</p>
            </div>

            <div class="auth-tabs">
                <button class="auth-tab active" id="login-tab" onclick="switchAuthTab('login')">Login</button>
                <button class="auth-tab" id="signup-tab" onclick="switchAuthTab('signup')">Sign Up</button>
            </div>

            <div class="auth-panel active" id="login-panel">
                <div id="auth-message" class="auth-message"></div>
                <label>
                    Email
                    <input id="login-email" type="email" placeholder="you@example.com" autocomplete="email">
                </label>
                <label>
                    Password
                    <input id="login-password" type="password" placeholder="Enter your password" autocomplete="current-password">
                </label>
                <button class="primary-btn" onclick="submitAuth('login')">Login</button>
            </div>

            <div class="auth-panel" id="signup-panel">
                <div id="auth-message-signup" class="auth-message"></div>
                <label>
                    Username
                    <input id="signup-username" type="text" placeholder="Choose a username" autocomplete="username">
                </label>
                <label>
                    Email
                    <input id="signup-email" type="email" placeholder="you@example.com" autocomplete="email">
                </label>
                <label>
                    Password
                    <input id="signup-password" type="password" placeholder="Choose a password" autocomplete="new-password">
                </label>
                <label>
                    Confirm Password
                    <input id="signup-confirm-password" type="password" placeholder="Repeat your password" autocomplete="new-password">
                </label>
                <button class="primary-btn" onclick="submitAuth('signup')">Create Account</button>
            </div>

            <div class="divider"><span>or</span></div>
            <button id="google-btn" class="google-btn" onclick="handleGoogleAuth()">
                <span class="google-icon">G</span>
                Continue with Google
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="app-shell" id="chat-root">
        <aside class="sidebar">
            <div class="sidebar-top">
                <div>
                    <p class="sidebar-label">🔴</p>
                    <h2><?=htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8')?></h2>
                </div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>

            <button class="new-chat-btn" id="new-chat-btn">+ New Chat</button>
            <div class="conversation-list" id="conversation-list"></div>
        </aside>

        <main class="chat-container">
            <div class="chat-header">
                <div>
                    <h1>🤖AI Chatbot</h1>
                    <p id="chat-subtitle">Your previous chats appear here automatically.</p>
                </div>
                
            </div>

            <div class="chat-box" id="chat-box">
                <div class="placeholder-text">Loading your conversations... please wait.</div>
            </div>

            <div class="input-area">
                <input
                    type="text"
                    id="user-input"
                    placeholder="Ask your assistant anything..."
                    onkeypress="handleKeyPress(event)"
                    autocomplete="off"
                >
                <button id="send-btn" onclick="sendMessage()">Send</button>
            </div>
        </main>
    </div>
<?php endif; ?>

<script src="app.js"></script>
</body>
</html>
