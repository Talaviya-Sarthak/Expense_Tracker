<?php
include 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to download history.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user_id = $_SESSION['user_id'];
$format = $_POST['format'] ?? 'csv'; // Default to CSV
$filter_duration = $_POST['duration'] ?? '';

// Prepare the SQL query based on the filter duration
$transactions = [];
$where_clause = "WHERE user_id = ?";
$params = "i";
$param_values = [$user_id];

$current_date = date('Y-m-d');
if ($filter_duration === 'day') {
    $where_clause .= " AND date = ?";
    $params .= "s";
    $param_values[] = $current_date;
} elseif ($filter_duration === 'week') {
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $end_of_week = date('Y-m-d', strtotime('sunday this week'));
    $where_clause .= " AND date BETWEEN ? AND ?";
    $params .= "ss";
    $param_values[] = $start_of_week;
    $param_values[] = $end_of_week;
} elseif ($filter_duration === 'month') {
    $start_of_month = date('Y-m-01');
    $end_of_month = date('Y-m-t');
    $where_clause .= " AND date BETWEEN ? AND ?";
    $params .= "ss";
    $param_values[] = $start_of_month;
    $param_values[] = $end_of_month;
} elseif ($filter_duration === 'year') {
    $start_of_year = date('Y-01-01');
    $end_of_year = date('Y-12-31');
    $where_clause .= " AND date BETWEEN ? AND ?";
    $params .= "ss";
    $param_values[] = $start_of_year;
    $param_values[] = $end_of_year;
}

// Fetch expenses
$stmt_exp = $conn->prepare("SELECT date, category, amount, description, 'Expense' as type FROM expenses " . $where_clause . " ORDER BY date DESC");
$stmt_exp->bind_param($params, ...$param_values);
$stmt_exp->execute();
$result_exp = $stmt_exp->get_result();
while ($row = $result_exp->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt_exp->close();

// Fetch incomes
$stmt_inc = $conn->prepare("SELECT date, category, amount, description, 'Income' as type FROM incomes " . $where_clause . " ORDER BY date DESC");
$stmt_inc->bind_param($params, ...$param_values);
$stmt_inc->execute();
$result_inc = $stmt_inc->get_result();
while ($row = $result_inc->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt_inc->close();

// Sort transactions by date
usort($transactions, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Generate the file based on the selected format
$filename = "FinovateX_History_" . date('Ymd_His');

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Type', 'Category', 'Amount (INR)', 'Description']); // CSV Header

    foreach ($transactions as $row) {
        fputcsv($output, [
            $row['date'],
            $row['type'],
            $row['category'],
            $row['amount'],
            $row['description']
        ]);
    }
    fclose($output);
} elseif ($format === 'pdf') {
    // For PDF generation, you'll need a library like FPDF or TCPDF.
    // This is a placeholder. You'd install and use the library here.
    // Example using a hypothetical PDF library:
    // require('path/to/fpdf/fpdf.php');
    // $pdf = new FPDF();
    // $pdf->AddPage();
    // $pdf->SetFont('Arial','B',16);
    // $pdf->Cell(40,10,'FinovateX Transaction History');
    // // Add table data
    // $pdf->Output('D', $filename . '.pdf');

    // For now, just output a message if PDF library is not set up
    echo "PDF generation requires a PDF library (e.g., FPDF). Please set it up.";
    // You might redirect back or show an error
    // $_SESSION['error_message'] = "PDF generation not yet implemented.";
    // redirect(FRONTEND_PAGES_URL . '12DownloadExpenses.php');
} else {
    $_SESSION['error_message'] = "Invalid download format.";
    redirect(FRONTEND_PAGES_URL . '12DownloadExpenses.php');
}

$conn->close();
exit();
?>
