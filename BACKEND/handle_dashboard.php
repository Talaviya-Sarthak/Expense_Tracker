<?php
// BACKEND/handle_dashboard.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php'; // must set $conn = new mysqli(...)

// ensure logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];

// sum income and expense (amount column numeric)
$sql = "SELECT
          IFNULL(SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END), 0) AS total_income,
          IFNULL(SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END), 0) AS total_expense
        FROM transactions
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_income, $total_expense);
$stmt->fetch();
$stmt->close();

$total_income = (float)$total_income;
$total_expense = (float)$total_expense;
$balance = $total_income - $total_expense;

echo json_encode([
    'total_income' => $total_income,
    'total_expense' => $total_expense,
    'balance' => $balance
]);
