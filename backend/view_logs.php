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

// Handle delete log
if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    try {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("DELETE FROM guest_logs WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        header('Location: view_logs.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $error = "Failed to delete log";
    }
}

$conn = getDatabaseConnection();

$sql = "SELECT id, email, visit_date, visit_time, device_type, ip_address, created_at FROM guest_logs ORDER BY created_at DESC LIMIT 10000";
$result = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$totalVisits = count($result);
$deviceCounts = ['Mobile' => 0, 'Desktop' => 0, 'Tablet' => 0];
$uniqueEmails = [];

foreach ($result as $row) {
    $device = $row['device_type'];
    if (isset($deviceCounts[$device])) {
        $deviceCounts[$device]++;
    }
    $uniqueEmails[$row['email']] = true;
}

$uniqueVisitors = count($uniqueEmails);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Logs - GRAPIKA</title>
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
        .container { max-width: 1200px; }
        table { width: 100%; border-collapse: collapse; background: rgba(240,236,227,0.02); border: 1px solid rgba(240,236,227,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(240,236,227,0.08); }
        th { background: rgba(212,255,0,0.1); color: #d4ff00; font-weight: bold; }
        tr:hover { background: rgba(212,255,0,0.05); }
        .device-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .device-mobile { background: #ff3d00; color: #fff; }
        .device-desktop { background: #00f0ff; color: #0a0a0a; }
        .device-tablet { background: #9b00ff; color: #fff; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-box { background: rgba(212,255,0,0.05); border: 1px solid rgba(212,255,0,0.2); padding: 1.5rem; text-align: center; clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px)); }
        .stat-num { font-size: 2.5rem; color: #d4ff00; font-weight: bold; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.8rem; color: rgba(240,236,227,0.6); text-transform: uppercase; letter-spacing: 0.1em; }
        .no-data { text-align: center; padding: 3rem; color: rgba(240,236,227,0.4); }
        .delete-btn { background: rgba(255,61,0,0.15); color: #ff3d00; border: 1px solid rgba(255,61,0,0.3); padding: 0.4rem 0.8rem; border-radius: 3px; cursor: pointer; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; }
        .delete-btn:hover { background: rgba(255,61,0,0.3); border-color: #ff3d00; }
        .alert-success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: #00ff88; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: static; border-right: none; border-bottom: 2px solid rgba(212,255,0,0.2); padding: 1rem; }
            .sidebar-footer { position: static; border-top: 1px solid rgba(212,255,0,0.1); margin-top: 1rem; padding: 1rem; }
            .main-content { margin-left: 0; padding: 1rem; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            table { font-size: 0.9rem; }
            th, td { padding: 0.7rem; }
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
            <li><a href="view_messages.php">📨 Messages</a></li>
            <li><a href="view_logs.php" class="active">👥 Visitor Logs</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert-success">✓ Log entry deleted successfully!</div>
            <?php endif; ?>
            <h1 style="color: #d4ff00; text-shadow: 2px 2px 0 #ff3d00; margin-bottom: 2rem;">📊 Guest Visit Logs</h1>

            <?php if ($totalVisits > 0): ?>
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-num"><?php echo $totalVisits; ?></div>
                        <div class="stat-label">Total Visits</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num"><?php echo $uniqueVisitors; ?></div>
                        <div class="stat-label">Unique Visitors</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num"><?php echo $deviceCounts['Desktop']; ?></div>
                        <div class="stat-label">Desktop Visits</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num"><?php echo $deviceCounts['Mobile']; ?></div>
                        <div class="stat-label">Mobile Visits</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>Logged At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $row): ?>
                            <?php $deviceClass = 'device-' . strtolower($row['device_type']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['visit_date']; ?></td>
                                <td><?php $time = DateTime::createFromFormat('H:i:s', $row['visit_time']); echo $time ? $time->format('g:i A') : htmlspecialchars($row['visit_time']); ?></td>
                                <td><span class="device-badge <?php echo $deviceClass; ?>"><?php echo $row['device_type']; ?></span></td>
                                <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                                <td><?php $tz = new DateTimeZone('Asia/Manila'); $dt = new DateTime($row['created_at'], new DateTimeZone('UTC')); $dt->setTimezone($tz); echo $dt->format('g:i A'); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this log entry? This cannot be undone.')">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="delete-btn">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No guest logs yet. Visitors will be logged when they submit the form.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>