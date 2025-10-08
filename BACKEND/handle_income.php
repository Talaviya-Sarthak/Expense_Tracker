<?php
session_start();
include_once 'config.php';  // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/2login.html?error=" . urlencode('Please login.'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if income form is submitted
    if (isset($_POST['income_category'])) {
        $user_id            = $_SESSION['user_id'];
        $income_category    = trim($_POST['income_category'] ?? '');
        $income_rupees      = trim($_POST['income_rupees'] ?? 0);
        $income_description = trim($_POST['income_description'] ?? '');
        $income_date        = trim($_POST['income_date'] ?? '');

        // Validate required fields
        if (empty($income_category) || $income_rupees <= 0 || empty($income_date)) {
            header("Location: ../FRONTEND/PAGES/7income.html?error=" . urlencode('Please fill all required fields with valid values.'));
            exit();
        }

        // Prepare and execute INSERT query
        $stmt = $conn->prepare("INSERT INTO incomes (user_id, category, amount, description, date) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isdss", $user_id, $income_category, $income_rupees, $income_description, $income_date);

        if ($stmt->execute()) {
            header("Location: ../FRONTEND/PAGES/4dashboard.html?success=" . urlencode('Income added successfully!'));
        } else {
            header("Location: ../FRONTEND/PAGES/7income.html?error=" . urlencode('Error adding income entry.'));
        }

        $stmt->close();
        $conn->close();
    } else {
        // Not an income form submission; handle other cases or redirect
        header("Location: ../FRONTEND/PAGES/4dashboard.html");
        exit();
    }
} else {
    header("Location: ../FRONTEND/PAGES/4dashboard.html");
    exit();
}
?>
