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

// Get statistics
$messages_result = $conn->query("SELECT COUNT(*) as total FROM messages");
$messages_count = $messages_result->fetch_assoc()['total'];

$guests_result = $conn->query("SELECT COUNT(*) as total FROM guest_logs");
$guests_count = $guests_result->fetch_assoc()['total'];

$unique_visitors = $conn->query("SELECT COUNT(DISTINCT email) as total FROM guest_logs");
$unique_count = $unique_visitors->fetch_assoc()['total'];

$unique_contacts = $conn->query("SELECT COUNT(DISTINCT email) as total FROM messages");
$unique_contacts_count = $unique_contacts->fetch_assoc()['total'];

// Get recent messages
$recent_messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");

// Get recent logs
$recent_logs = $conn->query("SELECT * FROM guest_logs ORDER BY created_at DESC LIMIT 5");

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GRAPIKA</title>
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
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(212, 255, 0, 0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            color: #d4ff00;
            text-shadow: 2px 2px 0 #ff3d00;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: rgba(10, 10, 10, 0.6);
            border: 1px solid rgba(212, 255, 0, 0.2);
            padding: 1.5rem;
            border-radius: 8px;
            clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px));
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            background: rgba(212, 255, 0, 0.05);
            border-color: rgba(212, 255, 0, 0.4);
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d4ff00;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: rgba(240, 236, 227, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .content-section {
            background: rgba(10, 10, 10, 0.6);
            border: 1px solid rgba(212, 255, 0, 0.2);
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px));
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #d4ff00;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .section-title-icon {
            font-size: 1.8rem;
        }
        
        .view-all-btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: #d4ff00;
            color: #0a0a0a;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.2s;
            float: right;
            margin-top: -2.5rem;
        }
        
        .view-all-btn:hover {
            background: #ff3d00;
            color: #f0ece3;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th {
            background: rgba(212, 255, 0, 0.1);
            color: #d4ff00;
            padding: 1rem;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid rgba(212, 255, 0, 0.2);
        }
        
        td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(212, 255, 0, 0.08);
        }
        
        tr:hover {
            background: rgba(212, 255, 0, 0.03);
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(212, 255, 0, 0.1);
            color: #d4ff00;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: rgba(240, 236, 227, 0.4);
        }
        
        .timestamp {
            font-size: 0.85rem;
            color: rgba(240, 236, 227, 0.5);
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
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .view-all-btn {
                float: none;
                display: block;
                margin-top: 1rem;
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
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="view_messages.php">📨 Messages</a></li>
            <li><a href="view_logs.php">👥 Visitor Logs</a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div style="font-size: 0.9rem; color: rgba(240, 236, 227, 0.6);">
                Welcome back! 👋
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📨</div>
                <div class="stat-value"><?php echo htmlspecialchars($messages_count); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?php echo htmlspecialchars($guests_count); ?></div>
                <div class="stat-label">Total Visitors</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?php echo htmlspecialchars($unique_count); ?></div>
                <div class="stat-label">Unique Visitors</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-value"><?php echo htmlspecialchars($unique_contacts_count); ?></div>
                <div class="stat-label">Unique Contacts</div>
            </div>
        </div>
        
        <!-- Recent Messages Section -->
        <div class="content-section">
            <div class="section-title">
                <span class="section-title-icon">📨</span>
                Recent Messages
                <a href="view_messages.php" class="view-all-btn">View All →</a>
            </div>
            
            <?php if ($recent_messages->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Project Type</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($msg = $recent_messages->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($msg['project_type'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars(substr($msg['message'], 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</td>
                                    <td><span class="timestamp"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($msg['created_at']))); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No messages yet. Check back soon! 📭</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Visitors Section -->
        <div class="content-section">
            <div class="section-title">
                <span class="section-title-icon">👥</span>
                Recent Visitors
                <a href="view_logs.php" class="view-all-btn">View All →</a>
            </div>
            
            <?php if ($recent_logs->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Device</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $recent_logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge">
                                            <?php if ($log['device_type'] === 'Mobile'): ?>
                                                📱
                                            <?php elseif ($log['device_type'] === 'Tablet'): ?>
                                                📱
                                            <?php else: ?>
                                                💻
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($log['device_type'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['visit_date']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($log['visit_time'], 0, 5)); ?></td>
                                    <td><span class="timestamp"><?php echo htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No visitor logs yet. 👀</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
