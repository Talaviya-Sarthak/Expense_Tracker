<!-- handle_expense.php -->
<?php
session_start();
include_once 'config.php';  // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='../FRONTEND/PAGES/2login.html';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = trim($_POST['category'] ?? '');
    $custom_category = trim($_POST['custom_category'] ?? '');
    $rupees = trim($_POST['rupees'] ?? 0);
    $quantity = trim($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');

    // If category is "Other", use the custom category
    if ($category === "Other" && !empty($custom_category)) {
        $category = $custom_category;
    }

    // Validate required fields
    if (empty($category) || $rupees <= 0 || $quantity <= 0 || empty($date)) {
        echo "<script>alert('Please fill all required fields with valid values.'); window.history.back();</script>";
        exit();
    }
    $rupees = floatval($rupees);
    $quantity = intval($quantity);

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, quantity, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // user_id (i), category (s), amount (d), quantity (i), description (s), date (s)
    $stmt->bind_param("isdiss", $user_id, $category, $rupees, $quantity, $description, $date);

    if ($stmt->execute()) {
        echo "<script>alert('Expense added successfully.'); window.location.href='../FRONTEND/PAGES/4dashboard.html';</script>";
    } else {
        echo "<script>alert('Error adding expense: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../FRONTEND/PAGES/4dashboard.html");
    exit();
}
?>