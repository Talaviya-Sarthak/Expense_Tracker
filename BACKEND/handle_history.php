<?php
include 'config.php';

if (!isLoggedIn()) {
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user = getLoggedInUser ($conn);

$transactions = [];
$expense_data = [];
$income_data = [];
$labels = [];

if ($user) {
    $user_id = $user['id'];

    // Fetch all expenses for chart and table
    $stmt_exp = $conn->prepare("SELECT date, category, amount, description, 'Expense' as type FROM expenses WHERE user_id = ? ORDER BY date ASC");
    $stmt_exp->bind_param("i", $user_id);
    $stmt_exp->execute();
    $result_exp = $stmt_exp->get_result();
    while ($row = $result_exp->fetch_assoc()) {
        $transactions[] = $row;
        $expense_data[$row['date']] = ($expense_data[$row['date']] ?? 0) + $row['amount'];
        if (!in_array($row['date'], $labels)) $labels[] = $row['date'];
    }
    $stmt_exp->close();

    // Fetch all incomes for chart and table
    $stmt_inc = $conn->prepare("SELECT date, category, amount, description, 'Income' as type FROM incomes WHERE user_id = ? ORDER BY date ASC");
    $stmt_inc->bind_param("i", $user_id);
    $stmt_inc->execute();
    $result_inc = $stmt_inc->get_result();
    while ($row = $result_inc->fetch_assoc()) {
        $transactions[] = $row;
        $income_data[$row['date']] = ($income_data[$row['date']] ?? 0) + $row['amount'];
        if (!in_array($row['date'], $labels)) $labels[] = $row['date'];
    }
    $stmt_inc->close();

    // Sort transactions by date for the table
    usort($transactions, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']); // Descending order for table
    });

    // Sort labels (dates) for the chart
    sort($labels);

    // Prepare chart data arrays, filling missing dates with 0
    $chart_expenses = [];
    $chart_incomes = [];
    foreach ($labels as $date) {
        $chart_expenses[] = $expense_data[$date] ?? 0;
        $chart_incomes[] = $income_data[$date] ?? 0;
    }

    // Store data in session to display on the history page
    $_SESSION['transactions'] = $transactions;
    $_SESSION['labels'] = $labels;
    $_SESSION['chart_expenses'] = $chart_expenses;
    $_SESSION['chart_incomes'] = $chart_incomes;
}

header("Location: ../FRONTEND/PAGES/13histroy.html");
exit();
?>
