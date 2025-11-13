<?php
session_start();
include 'config.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/8password.html");
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['createpassword'] ?? '');
    $confirm = trim($_POST['confirmpassword'] ?? '');

    // Check if passwords match
    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    // Validate password strength (example: min 8 chars, at least 1 number)
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
        die("Password must be at least 8 characters long and contain at least one number.");
    }

    // Hash the password
    $hash = password_hash($password, PASSWORD_DEFAULT);



    // Check connection
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $hash, $user_id);

    if ($stmt->execute()) {
        // Password updated successfully, destroy session and redirect
        session_destroy();
        header("Location: ../FRONTEND/PAGES/2login.html");

    } else {
        die("Error updating password: " . $stmt->error);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>