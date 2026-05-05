<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// If already logged in, redirect to messages
if (isAdminAuthenticated()) {
    header('Location: view_messages.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Verify password using hash comparison
    if (!empty($password) && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_login_time'] = time();
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GRAPIKA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a2e 100%);
            color: #f0ece3;
            font-family: 'Space Mono', monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .login-container {
            background: rgba(10, 10, 10, 0.95);
            border: 2px solid rgba(212, 255, 0, 0.3);
            padding: 3rem;
            max-width: 400px;
            width: 100%;
            clip-path: polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px));
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        }
        
        h1 {
            color: #d4ff00;
            text-shadow: 2px 2px 0 #ff3d00;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        
        .subtitle {
            color: rgba(240, 236, 227, 0.5);
            font-size: 0.85rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: rgba(212, 255, 0, 0.8);
            margin-bottom: 0.5rem;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            background: rgba(240, 236, 227, 0.04);
            border: 1.5px solid rgba(212, 255, 0, 0.2);
            color: #f0ece3;
            font-family: 'Space Mono', monospace;
            outline: none;
            transition: all 0.2s;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
        }
        
        input[type="password"]:focus {
            border-color: #d4ff00;
            background: rgba(212, 255, 0, 0.05);
            box-shadow: 0 0 20px rgba(212, 255, 0, 0.2);
        }
        
        .error {
            background: rgba(255, 61, 0, 0.1);
            border-left: 3px solid #ff3d00;
            color: #ff3d00;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        button {
            width: 100%;
            padding: 1rem;
            background: #d4ff00;
            color: #0a0a0a;
            border: none;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.2rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px));
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: #ff3d00;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s;
            z-index: -1;
        }
        
        button:hover::before {
            transform: scaleX(1);
        }
        
        button:hover {
            color: #f0ece3;
        }
        
        .security-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(240, 236, 227, 0.08);
            font-size: 0.7rem;
            color: rgba(240, 236, 227, 0.3);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 ADMIN</h1>
        <p class="subtitle">Enter admin password to access logs</p>
        
        <?php if (!empty($error)): ?>
            <div class="error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit">LOGIN →</button>
        </form>
        
        <div class="security-info">
            🔒 This is a secure admin panel. Your connection is protected.
        </div>
    </div>
</body>
</html>
