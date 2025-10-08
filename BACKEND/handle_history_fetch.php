<?php
session_start();
include_once 'config.php';

if (!isLoggedIn()) {
    header("Location: ../FRONTEND/PAGES/2login.html?error=" . urlencode('Please login.'));
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$transactions = [];

// Expenses
$stmtExp = $conn->prepare("SELECT date, category, amount, description, 'Expense' AS type FROM expenses WHERE user_id = ?");
if ($stmtExp) {
    $stmtExp->bind_param("i", $user_id);
    $stmtExp->execute();
    $resExp = $stmtExp->get_result();
    while ($row = $resExp->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmtExp->close();
}

// Incomes
$stmtInc = $conn->prepare("SELECT date, category, amount, description, 'Income' AS type FROM incomes WHERE user_id = ?");
if ($stmtInc) {
    $stmtInc->bind_param("i", $user_id);
    $stmtInc->execute();
    $resInc = $stmtInc->get_result();
    while ($row = $resInc->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmtInc->close();
}

// Sort by date DESC
usort($transactions, function($a, $b) { return strtotime($b['date']) <=> strtotime($a['date']); });

header('Content-Type: application/json');
echo json_encode($transactions);
?>


