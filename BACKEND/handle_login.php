<!-- handle_login.php -->
<?php
session_start();
include_once 'config.php';  // Ensure config.php sets up the database connection ($conn)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "<script>alert('Both username and password are required.'); window.history.back();</script>";
        exit();
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    if (!$stmt) {
        echo "<script>alert('Database error: Prepare failed.'); window.history.back();</script>";
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Valid credentials: start session and redirect
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;

            header("Location: ../FRONTEND/PAGES/4dashboard.html");
            exit();
        } else {
            echo "<script>alert('Invalid username or password.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid username or password.'); window.history.back();</script>";
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../FRONTEND/PAGES/2login.html");
    exit();
}
?>
