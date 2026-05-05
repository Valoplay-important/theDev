<?php
header('Content-Type: application/json');
require_once 'config.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Check rate limiting
$client_id = getClientIP() . '_' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
if (!checkRateLimit($client_id)) {
    http_response_code(429);
    exit(json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']));
}

// Get data from POST request
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$project_type = isset($_POST['project_type']) ? trim($_POST['project_type']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate inputs
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

// Validate input lengths
if (!validateInputLength($name, 255) || !validateInputLength($project_type, 100) || !validateInputLength($message, MAX_MESSAGE_LENGTH)) {
    exit(json_encode(['success' => false, 'message' => 'Input is too long']));
}

// Get secure database connection
$conn = getDatabaseConnection();

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO messages (name, email, project_type, message) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt->bind_param("ssss", $name, $email, $project_type, $message);

// Execute
if ($stmt->execute()) {
    exit(json_encode(['success' => true, 'message' => 'Message sent successfully! I\'ll get back to you soon.']));
} else {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Error sending message']));
}

$stmt->close();
$conn->close();
?>
