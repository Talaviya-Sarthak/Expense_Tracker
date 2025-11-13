<?php
// BACKEND/handle_profile.php
session_start();
require_once 'config.php'; // must define $conn and FRONTEND_PAGES_URL

// fallback helper if not present in config.php
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

// redirect helper fallback
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . $url);
        exit();
    }
}

if (!is_logged_in()) {
    // If this endpoint is intended to be consumed by AJAX, return 401 JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user_id = (int) $_SESSION['user_id'];

// Select user fields you need
$stmt = $conn->prepare("SELECT username, email, phone, country, gender, monthly_income, profile_pic_path FROM users WHERE id = ?");
if ($stmt === false) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Server error']);
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc() ?: null;
$stmt->close();

// If requested as JSON (AJAX), return JSON
header('Content-Type: application/json; charset=utf-8');
if ($user === null) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

// Cast numeric fields
if (isset($user['monthly_income'])) {
    $user['monthly_income'] = is_numeric($user['monthly_income']) ? (float)$user['monthly_income'] : null;
}

// Return user object (safe, do not include password)
echo json_encode(['user' => $user]);
exit();
