<?php
include 'config.php';

if (!isLoggedIn()) {
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user = getLoggedInUser ($conn);

$total_expense = 0;
$total_income = 0;
$total_balance = 0;

if ($user) {
    $stmt_exp = $conn->prepare("SELECT SUM(amount) AS total_exp FROM expenses WHERE user_id = ?");
    $stmt_exp->bind_param("i", $user['id']);
    $stmt_exp->execute();
    $result_exp = $stmt_exp->get_result();
    $row_exp = $result_exp->fetch_assoc();
    $total_expense = $row_exp['total_exp'] ?? 0;
    $stmt_exp->close();

    $stmt_inc = $conn->prepare("SELECT SUM(amount) AS total_inc FROM incomes WHERE user_id = ?");
    $stmt_inc->bind_param("i", $user['id']);
    $stmt_inc->execute();
    $result_inc = $stmt_inc->get_result();
    $row_inc = $result_inc->fetch_assoc();
    $total_income = $row_inc['total_inc'] ?? 0;
    $stmt_inc->close();

    $total_balance = $total_income - $total_expense;
}

// Store data in session to display on the dashboard
$_SESSION['total_expense'] = $total_expense;
$_SESSION['total_income'] = $total_income;
$_SESSION['total_balance'] = $total_balance;

header("Location: ../FRONTEND/PAGES/4dashboard.html");
exit();
?>
