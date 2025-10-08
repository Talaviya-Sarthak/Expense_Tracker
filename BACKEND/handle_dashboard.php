<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/2login.html?error=" . urlencode('Please login.'));
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);

// Calculate totals from separate tables: incomes and expenses
$totals = [
    'total_income' => 0.0,
    'total_expense' => 0.0,
    'balance' => 0.0,
];

// Total income
$stmtInc = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_income FROM incomes WHERE user_id = ?");
if ($stmtInc) {
    $stmtInc->bind_param("i", $user_id);
    $stmtInc->execute();
    $resInc = $stmtInc->get_result();
    if ($resInc) {
        $row = $resInc->fetch_assoc();
        $totals['total_income'] = (float)($row['total_income'] ?? 0);
    }
    $stmtInc->close();
}

// Total expense
$stmtExp = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_expense FROM expenses WHERE user_id = ?");
if ($stmtExp) {
    $stmtExp->bind_param("i", $user_id);
    $stmtExp->execute();
    $resExp = $stmtExp->get_result();
    if ($resExp) {
        $row = $resExp->fetch_assoc();
        $totals['total_expense'] = (float)($row['total_expense'] ?? 0);
    }
    $stmtExp->close();
}

$totals['balance'] = $totals['total_income'] - $totals['total_expense'];

header('Content-Type: application/json');
echo json_encode($totals);
?>
