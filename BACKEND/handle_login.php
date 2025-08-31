<?php
session_start();
include 'config.php'; // DB connection + redirect function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
        redirect(FRONTEND_PAGES_URL . '2login.html');
    }

    // Fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['success_message'] = "Login successful! Welcome, " . $row['username'] . ".";
            redirect(FRONTEND_PAGES_URL . '4dashboard.html');
        } else {
            $_SESSION['error_message'] = "Invalid username or password.";
            redirect(FRONTEND_PAGES_URL . '2login.html');
        }
    } else {
        $_SESSION['error_message'] = "Invalid username or password.";
        redirect(FRONTEND_PAGES_URL . '2login.html');
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['error_message'] = "Access denied. Please use the login form.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}
?>
