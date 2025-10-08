<?php
session_start();
include_once 'config.php';  // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/2login.html?error=" . urlencode('Please login.'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id         = $_SESSION['user_id'];
    $category        = trim($_POST['category'] ?? '');
    $custom_category = trim($_POST['custom_category'] ?? '');
    $rupees         = trim($_POST['rupees'] ?? 0);
    $quantity       = trim($_POST['quantity'] ?? 0);
    $description    = trim($_POST['description'] ?? '');
    $date          = trim($_POST['date'] ?? '');

    // If category is "Other", use the custom category
    if ($category === "Other" && !empty($custom_category)) {
        $category = $custom_category;
    }

    // Validate required fields
    if (empty($category) || $rupees <= 0 || $quantity <= 0 || empty($date)) {
        header("Location: ../FRONTEND/PAGES/6expense.html?error=" . urlencode('Please fill all required fields with valid values.'));
        exit();
    }

    // Prepare and execute INSERT query
    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, quantity, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isidss", $user_id, $category, $rupees, $quantity, $description, $date);

    if ($stmt->execute()) {
        header("Location: ../FRONTEND/PAGES/4dashboard.html?success=" . urlencode('Expense added successfully!'));
    } else {
        header("Location: ../FRONTEND/PAGES/6expense.html?error=" . urlencode('Error adding expense.'));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../FRONTEND/PAGES/4dashboard.html");
    exit();
}
?>
