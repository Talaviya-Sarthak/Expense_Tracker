<?php
// Database connection
$host = "sql103.infinityfree.com";
$user = "if0_40119885";
$pass = "PgwhNTM5uLmk";
$db = "if0_40119885_finovatex_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define base URL for frontend pages
define("FRONTEND_PAGES_URL", "https://finovatex.infinityfreeapp.com/FinovateX/FRONTEND/PAGES/");

// Redirect helper function
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

// Session helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}
?>