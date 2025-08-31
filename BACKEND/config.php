<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "finovatex_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define base URL for frontend pages
define("FRONTEND_PAGES_URL", "/PROJECT/FRONTEND/PAGES/");

// Redirect helper function
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
