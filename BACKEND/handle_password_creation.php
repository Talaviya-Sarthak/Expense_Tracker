<?php
session_start();
include 'config.php'; // DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure session is active
    if (!isset($_SESSION['user_id'])) {
        die("Session expired. Please sign up again.");
    }

    $user_id = $_SESSION['user_id'];
    $password = trim($_POST['createpassword'] ?? '');
    $confirmPassword = trim($_POST['confirmpassword'] ?? '');

    // Validate
    if (empty($password) || empty($confirmPassword)) {
        die("Password fields cannot be empty.");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match. Please try again.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password for this user
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user_id);

    if ($stmt->execute()) {
        // Destroy signup session (optional security)
        unset($_SESSION['user_id']);
        unset($_SESSION['email']);

        // Redirect to login
        header("Location: ../FRONTEND/PAGES/2login.html");
        exit();
    } else {
        die("Error updating password: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    die("Invalid request.");
}
