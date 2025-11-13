<?php
// BACKEND/handle_profile_update.php
session_start();
require_once 'config.php'; // must define $conn and FRONTEND_PAGES_URL

// fallback helper if not present in config.php
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

// redirect helper fallback (should exist in config.php, but safe to declare)
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . $url);
        exit();
    }
}

if (!is_logged_in()) {
    $_SESSION['error_message'] = "Please login to update your profile.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only accept POST for updates
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}

$user_id = (int) $_SESSION['user_id'];
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$country = trim($_POST['country'] ?? '');
$monthly_income = $_POST['income'] ?? '';

// Basic validation
if ($username === '' || $email === '' || $country === '' || $monthly_income === '') {
    $_SESSION['error_message'] = "All fields are required.";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Please enter a valid email address.";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}

if (!is_numeric($monthly_income) || $monthly_income < 0) {
    $_SESSION['error_message'] = "Monthly income must be a non-negative number.";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}

// Check username/email uniqueness (exclude current user)
$stmt_check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
if ($stmt_check === false) {
    $_SESSION['error_message'] = "Server error (validation).";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}
$stmt_check->bind_param("ssi", $username, $email, $user_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check && $res_check->num_rows > 0) {
    $stmt_check->close();
    $_SESSION['error_message'] = "Username or email already taken by another user.";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}
$stmt_check->close();

// Perform update
// types: username (s), email (s), country (s), monthly_income (d), id (i)
$monthly_income_val = (float)$monthly_income;
$stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, country = ?, monthly_income = ? WHERE id = ?");
if ($stmt === false) {
    $_SESSION['error_message'] = "Server error (update).";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}
$stmt->bind_param("sssdi", $username, $email, $country, $monthly_income_val, $user_id);

if ($stmt->execute()) {
    // Update session username if you store it
    $_SESSION['username'] = $username;
    $_SESSION['success_message'] = "Profile updated successfully.";
    $stmt->close();
    // Redirect to profile page after success
    redirect(FRONTEND_PAGES_URL . '10profile.html');
} else {
    $stmt->close();
    $_SESSION['error_message'] = "Failed to update profile. Please try again.";
    redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
}
