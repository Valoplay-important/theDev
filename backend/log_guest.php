<?php
header('Content-Type: application/json');
require_once 'config.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get data from POST request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$visit_date = isset($_POST['visit_date']) ? $_POST['visit_date'] : date('Y-m-d');
$visit_time = isset($_POST['visit_time']) ? $_POST['visit_time'] : date('H:i:s');
$device_type = isset($_POST['device_type']) ? trim($_POST['device_type']) : 'Unknown';
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
$ip_address = getClientIP();

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid email address']));
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid date format']));
}

// Validate time format
if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $visit_time)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid time format']));
}

// Validate input lengths
if (!validateInputLength($device_type, 50)) {
    exit(json_encode(['success' => false, 'message' => 'Device type is too long']));
}

// Get secure database connection
$conn = getDatabaseConnection();

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO guest_logs (email, visit_date, visit_time, device_type, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt->bind_param("ssssss", $email, $visit_date, $visit_time, $device_type, $user_agent, $ip_address);

// Execute
if ($stmt->execute()) {
    exit(json_encode(['success' => true, 'message' => 'Guest log recorded successfully!']));
} else {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Error recording log']));
}

$stmt->close();
$conn->close();
?>
