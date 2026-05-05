<?php
require_once 'config.php';

requireAdminAuth();

if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

$_SESSION['admin_login_time'] = time();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Handle delete message
if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    try {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        header('Location: view_messages.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $error = "Failed to delete message";
    }
}

$conn = getDatabaseConnection();

$result = $conn->query("SELECT id, name, email, project_type, message, created_at FROM messages ORDER BY created_at DESC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);

$total_messages = count($result);
$unique_contacts = $conn->query("SELECT COUNT(DISTINCT email) as count FROM messages")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - GRAPIKA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #0a0a0a 0%, #1a0a2e 100%); color: #f0ece3; font-family: 'Space Mono', monospace; min-height: 100vh; }
        .sidebar { position: fixed; left: 0; top: 0; width: 250px; height: 100vh; background: rgba(10,10,10,0.95); border-right: 2px solid rgba(212,255,0,0.2); padding: 2rem 0; overflow-y: auto; z-index: 1000; }
        .sidebar-logo { padding: 0 1.5rem 2rem; border-bottom: 1px solid rgba(212,255,0,0.1); margin-bottom: 2rem; }
        .logo-text { font-size: 1.8rem; font-weight: bold; color: #d4ff00; text-shadow: 2px 2px 0 #ff3d00; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu a { display: block; padding: 1rem 1.5rem; color: rgba(240,236,227,0.7); text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: #d4ff00; background: rgba(212,255,0,0.05); border-left-color: #d4ff00; }
        .sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; border-top: 1px solid rgba(212,255,0,0.1); }
        .logout-btn { width: 100%; padding: 0.8rem; background: rgba(255,61,0,0.1); border: 1px solid rgba(255,61,0,0.3); color: #ff3d00; text-decoration: none; display: block; text-align: center; border-radius: 4px; transition: all 0.2s; }
        .logout-btn:hover { background: rgba(255,61,0,0.2); border-color: #ff3d00; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .messages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
        .message-card { background: rgba(240,236,227,0.02); border: 1px solid rgba(240,236,227,0.1); padding: 1.5rem; border-radius: 8px; transition: all 0.3s; clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px)); }
        .message-card:hover { background: rgba(212,255,0,0.05); border-color: rgba(212,255,0,0.3); }
        .message-name { font-size: 1.1rem; font-weight: bold; color: #d4ff00; margin-bottom: 0.3rem; }
        .message-email { font-size: 0.8rem; color: rgba(240,236,227,0.6); margin-bottom: 0.5rem; }
        .message-project { display: inline-block; background: rgba(212,255,0,0.1); color: #d4ff00; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-bottom: 1rem; }
        .message-content { margin-bottom: 1rem; color: rgba(240,236,227,0.8); line-height: 1.6; word-wrap: break-word; }
        .message-date { font-size: 0.7rem; color: rgba(240,236,227,0.4); border-top: 1px solid rgba(240,236,227,0.08); padding-top: 0.8rem; }
        .stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-box { background: rgba(212,255,0,0.1); border: 1px solid rgba(212,255,0,0.2); padding: 1.5rem; text-align: center; border-radius: 8px; clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 8px 100%, 0 calc(100% - 8px)); }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #d4ff00; }
        .stat-label { font-size: 0.75rem; color: rgba(240,236,227,0.6); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.5rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: rgba(240,236,227,0.4); }
        .delete-btn { background: rgba(255,61,0,0.15); color: #ff3d00; border: 1px solid rgba(255,61,0,0.3); padding: 0.4rem 0.8rem; border-radius: 3px; cursor: pointer; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; width: 100%; margin-top: 1rem; }
        .delete-btn:hover { background: rgba(255,61,0,0.3); border-color: #ff3d00; }
        .alert-success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: #00ff88; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: static; border-right: none; border-bottom: 2px solid rgba(212,255,0,0.2); padding: 1rem; }
            .sidebar-footer { position: static; border-top: 1px solid rgba(212,255,0,0.1); margin-top: 1rem; padding: 1rem; }
            .main-content { margin-left: 0; padding: 1rem; }
            .messages-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>
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

    <div class="main-content">
        <div class="container">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert-success">✓ Message deleted successfully!</div>
            <?php endif; ?>
            <h1 style="color: #d4ff00; text-shadow: 2px 2px 0 #ff3d00; margin-bottom: 2rem;">📨 MESSAGES RECEIVED</h1>

            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_messages; ?></div>
                    <div class="stat-label">Total Messages</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $unique_contacts; ?></div>
                    <div class="stat-label">Unique Contacts</div>
                </div>
            </div>

            <?php if ($total_messages > 0): ?>
                <div class="messages-grid">
                    <?php foreach ($result as $row): ?>
                        <div class="message-card">
                            <div class="message-name"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="message-email"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="message-project"><?php echo htmlspecialchars($row['project_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="message-content"><?php echo nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')); ?></div>
                            <div class="message-date">📅 <?php echo htmlspecialchars(date('M d, Y · H:i', strtotime($row['created_at']))); ?></div>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message? This cannot be undone.')">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-btn">🗑️ Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
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