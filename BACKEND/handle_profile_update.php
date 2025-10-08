<?php
include 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to update your profile.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $country = $_POST['country'] ?? '';
    $monthly_income = $_POST['income'] ?? '';

    if (empty($username) || empty($email) || empty($country) || !is_numeric($monthly_income) || $monthly_income < 0) {
        $_SESSION['error_message'] = "All fields are required and income must be a non-negative number.";
        redirect(FRONTEND_PAGES_URL . '11profileupdate.html');
    }

    $stmt_check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt_check->bind_param("ssi", $username, $email, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $_SESSION['error_message'] = "Username or Email already taken by another user.";
    } else {
        $_SESSION[""] = "";
    }
}