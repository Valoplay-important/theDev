<?php
require_once 'config.php';

// Require admin authentication
requireAdminAuth();

// Check session timeout
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Update last activity time
$_SESSION['admin_login_time'] = time();

// Get secure database connection
$conn = getDatabaseConnection();

// Get all messages
$sql = "SELECT id, name, email, project_type, message, created_at FROM messages ORDER BY created_at DESC LIMIT 1000";
$result = $conn->query($sql);

if (!$result) {
    die('Database error');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - GRAPIKA</title>
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
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: rgba(10, 10, 10, 0.95);
            border-right: 2px solid rgba(212, 255, 0, 0.2);
            padding: 2rem 0;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-logo {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(212, 255, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: #d4ff00;
            text-shadow: 2px 2px 0 #ff3d00;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(240, 236, 227, 0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: #d4ff00;
            background: rgba(212, 255, 0, 0.05);
            border-left-color: #d4ff00;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            border-top: 1px solid rgba(212, 255, 0, 0.1);
        }
        
        .logout-btn {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 61, 0, 0.1);
            border: 1px solid rgba(255, 61, 0, 0.3);
            color: #ff3d00;
            text-decoration: none;
            display: block;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 61, 0, 0.2);
            border-color: #ff3d00;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            margin-bottom: 2rem;
            color: #d4ff00;
            text-shadow: 2px 2px 0 #ff3d00;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #d4ff00;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: #ff3d00;
        }
        
        .messages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .message-card {
            background: rgba(240, 236, 227, 0.02);
            border: 1px solid rgba(240, 236, 227, 0.1);
            padding: 1.5rem;
            border-radius: 8px;
            transition: all 0.3s;
            clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px));
        }
        
        .message-card:hover {
            background: rgba(212, 255, 0, 0.05);
            border-color: rgba(212, 255, 0, 0.3);
        }
        
        .message-header {
            margin-bottom: 1rem;
        }
        
        .message-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #d4ff00;
            margin-bottom: 0.3rem;
        }
        
        .message-email {
            font-size: 0.8rem;
            color: rgba(240, 236, 227, 0.6);
            margin-bottom: 0.5rem;
        }
        
        .message-project {
            display: inline-block;
            background: rgba(212, 255, 0, 0.1);
            color: #d4ff00;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .message-content {
            margin-bottom: 1rem;
            color: rgba(240, 236, 227, 0.8);
            line-height: 1.6;
            word-wrap: break-word;
        }
        
        .message-date {
            font-size: 0.7rem;
            color: rgba(240, 236, 227, 0.4);
            border-top: 1px solid rgba(240, 236, 227, 0.08);
            padding-top: 0.8rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: rgba(212, 255, 0, 0.1);
            border: 1px solid rgba(212, 255, 0, 0.2);
            padding: 1.5rem;
            text-align: center;
            border-radius: 8px;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px));
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d4ff00;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: rgba(240, 236, 227, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: rgba(240, 236, 227, 0.4);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-right: none;
                border-bottom: 2px solid rgba(212, 255, 0, 0.2);
                padding: 1rem;
            }
            
            .sidebar-footer {
                position: static;
                border-top: 1px solid rgba(212, 255, 0, 0.1);
                margin-top: 1rem;
                padding: 1rem;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .messages-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-text">🎨 GK</div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="view_messages.php" class="active">📨 Messages</a></li>
            <li><a href="view_logs.php">👥 Visitor Logs</a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 style="color: #d4ff00; text-shadow: 2px 2px 0 #ff3d00; margin-bottom: 2rem;">📨 MESSAGES RECEIVED</h1>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo htmlspecialchars($result->num_rows); ?></div>
                    <div class="stat-label">Total Messages</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php 
                        $email_result = $conn->query("SELECT COUNT(DISTINCT email) as count FROM messages");
                        $row = $email_result->fetch_assoc();
                        echo htmlspecialchars($row['count']);
                    ?></div>
                    <div class="stat-label">Unique Contacts</div>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="messages-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="message-card">
                            <div class="message-header">
                            <div class="message-name"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="message-email"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="message-project"><?php echo htmlspecialchars($row['project_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                        <div class="message-date">
                            📅 <?php echo htmlspecialchars(date('M d, Y · H:i', strtotime($row['created_at']))); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No messages yet. Check back soon!</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

$conn->close();
?>
