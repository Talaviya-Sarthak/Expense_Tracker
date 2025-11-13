<?php
// BACKEND/config.php
// Database connection
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "finovatex_db";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// If your project is accessible at http://localhost/project/
// set BASE_URL accordingly. Adjust to your environment.
define('BASE_URL', '/project/'); // <- change if needed, lowercase recommended
define('FRONTEND_PAGES_URL', BASE_URL . 'FRONTEND/PAGES/');

// Helper redirect (relative/absolute)
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Helper: check login quickly (returns boolean)
function is_logged_in() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
