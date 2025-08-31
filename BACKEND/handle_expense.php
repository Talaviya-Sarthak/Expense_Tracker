<?php
include 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to add expenses.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'];
    $custom_category = $_POST['custom_category'] ?? null;
    $amount = $_POST['rupees'];
    $quantity = $_POST['quantity'] ?? 1;
    $description = $_POST['description'] ?? null;
    $date = $_POST['date'];

    if ($category === 'Other' && !empty($custom_category)) {
        $category = $custom_category;
    } elseif ($category === 'Other' && empty($custom_category)) {
        $_SESSION['error_message'] = "Please specify a custom category if 'Other' is selected.";
        redirect(FRONTEND_PAGES_URL . '6expense.html');
    }

    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['error_message'] = "Amount must be a positive number.";
        redirect(FRONTEND_PAGES_URL . '6expense.html');
    }
    if (!is_numeric($quantity) || $quantity < 0) {
        $_SESSION['error_message'] = "Quantity must be a non-negative number.";
        redirect(FRONTEND_PAGES_URL . '6expense.html');
    }

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, quantity, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdiss", $user_id, $category, $amount, $quantity, $description, $date);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Expense added successfully!";
        redirect(FRONTEND_PAGES_URL . '4dashboard.html');
    } else {
        $_SESSION['error_message'] = "Error adding expense: " . $stmt->error;
        redirect(FRONTEND_PAGES_URL . '6expense.html');
    }
    $stmt->close();
} else {
    redirect(FRONTEND_PAGES_URL . '6expense.html');
}

$conn->close();
?>
