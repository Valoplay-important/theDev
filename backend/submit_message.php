<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$client_id = getClientIP() . '_' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
if (!checkRateLimit($client_id)) {
    http_response_code(429);
    exit(json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']));
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$project_type = isset($_POST['project_type']) ? trim($_POST['project_type']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($name) || strlen($name) < 2) {
    exit(json_encode(['success' => false, 'message' => 'Name is required (minimum 2 characters)']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid email address']));
}

if (empty($project_type)) {
    exit(json_encode(['success' => false, 'message' => 'Project type is required']));
}

if (empty($message) || strlen($message) < 10) {
    exit(json_encode(['success' => false, 'message' => 'Message must be at least 10 characters long']));
}

if (!validateInputLength($name, 255) || !validateInputLength($project_type, 100) || !validateInputLength($message, MAX_MESSAGE_LENGTH)) {
    exit(json_encode(['success' => false, 'message' => 'Input is too long']));
}

try {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("INSERT INTO messages (name, email, project_type, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $project_type, $message]);
    exit(json_encode(['success' => true, 'message' => 'Message sent successfully! I\'ll get back to you soon.']));
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Error sending message: ' . $e->getMessage()]));
}
?>