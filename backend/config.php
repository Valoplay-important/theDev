<?php
// Security Configuration File

// Database configuration
define('DB_SERVERNAME', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USERNAME', getenv('MYSQLUSER') ?: 'root');
define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'grapika_logs');
$port = getenv('MYSQLPORT') ?: 3306;

// Security settings
define('ADMIN_PASSWORD', 'grapika2026'); // Change this to a strong password!
define('MAX_INPUT_LENGTH', 500);
define('MAX_MESSAGE_LENGTH', 5000);
define('RATE_LIMIT_REQUESTS', 5); // Max requests per minute
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Set secure headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; style-src \'self\' \'unsafe-inline\'; script-src \'self\'');

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Function to get secure database connection
function getDatabaseConnection() {
    $port = intval(getenv('MYSQLPORT') ?: 3306);
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $dbname = getenv('MYSQLDATABASE') ?: 'grapika_logs';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        exit(json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]));
    }
}

// Function to validate input length
function validateInputLength($input, $maxLength = MAX_INPUT_LENGTH) {
    return strlen($input) <= $maxLength;
}

// Function to sanitize text input
function sanitizeInput($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

// Function to check rate limiting
function checkRateLimit($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    $count = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;
    
    if ($count >= RATE_LIMIT_REQUESTS) {
        return false;
    }
    
    $_SESSION[$key] = $count + 1;
    return true;
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to get client IP address
function getClientIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

// Function for admin authentication
function isAdminAuthenticated() {
    return isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true;
}

// Function to require admin access
function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        header('Location: admin_login.php');
        exit;
    }
}
?>
