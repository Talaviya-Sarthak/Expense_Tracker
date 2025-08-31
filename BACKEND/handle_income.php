<?php
include 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to add income.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['income_category'];
    $amount = $_POST['income_rupees'];
    $description = $_POST['income_description'] ?? null;
    $date = $_POST['income_date'];

    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['error_message'] = "Amount must be a positive number.";
        redirect(FRONTEND_PAGES_URL . '7income.html');
    }

    $stmt = $conn->prepare("INSERT INTO incomes (user_id, category, amount, description, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $category, $amount, $description, $date);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Income added successfully!";
        redirect(FRONTEND_PAGES_URL . '4dashboard.html');
    } else {
        $_SESSION['error_message'] = "Error adding income: " . $stmt->error;
        redirect(FRONTEND_PAGES_URL . '7income.html');
    }
    $stmt->close();
} else {
    redirect(FRONTEND_PAGES_URL . '7income.html');
}

$conn->close();
?>
