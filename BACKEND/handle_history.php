<!-- handle_history.php -->
<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/2login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Determine type and category
$type = '';
$category = '';

if (isset($_POST['income_category'])) {
    $type = 'Income';
    $category = trim($_POST['income_category']);
} elseif (isset($_POST['category'])) {
    $type = 'Expense';
    $category = trim($_POST['category']);
} else {
    echo "<script>alert('Category is required.'); window.history.back();</script>";
    exit();
}

// Handle "Other" category
$custom_category = trim($_POST['custom_category'] ?? '');
if (!empty($custom_category)) {
    $category = $custom_category;
}

$amount = $_POST['rupees'] ?? $_POST['income_rupees'] ?? 0;
$quantity = $_POST['quantity'] ?? 1; // Optional for income
$description = trim($_POST['description'] ?? $_POST['income_description'] ?? '');
$date = $_POST['date'] ?? $_POST['income_date'] ?? date('Y-m-d');
$stmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, quantity, description, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issdiss", $user_id, $type, $category, $amount, $quantity, $description, $date);


if ($stmt->execute()) {
    echo "<script>alert('Transaction added successfully.'); window.location.href='../FRONTEND/PAGES/13histroy.html';</script>";
} else {
    echo "<script>alert('Error saving transaction.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>