<!-- handle_download.php -->
<?php
session_start();
require_once 'config.php';

function bind_dynamic_params(mysqli_stmt $stmt, string $types, array $values): bool {
    if ($types === '' || empty($values)) {
        return true;
    }

    $refs = [$types];
    foreach ($values as $key => $value) {
        $refs[] = &$values[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

if (!is_logged_in()) {
    $_SESSION['error_message'] = "Please login to download history.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user_id = (int)$_SESSION['user_id'];
$format = strtolower($_POST['format'] ?? 'csv'); // Default to CSV
$filter_duration = strtolower($_POST['duration'] ?? '');

// Build WHERE clause for transactions table
$where_clause = "WHERE user_id = ?";
$types = "i";
$values = [$user_id];

$today = date('Y-m-d');

switch ($filter_duration) {
    case 'day':
        $where_clause .= " AND date = ?";
        $types .= "s";
        $values[] = $today;
        break;
    case 'week':
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $end_of_week = date('Y-m-d', strtotime('sunday this week'));
        $where_clause .= " AND date BETWEEN ? AND ?";
        $types .= "ss";
        $values[] = $start_of_week;
        $values[] = $end_of_week;
        break;
    case 'month':
        $start_of_month = date('Y-m-01');
        $end_of_month = date('Y-m-t');
        $where_clause .= " AND date BETWEEN ? AND ?";
        $types .= "ss";
        $values[] = $start_of_month;
        $values[] = $end_of_month;
        break;
    case 'year':
        $start_of_year = date('Y-01-01');
        $end_of_year = date('Y-12-31');
        $where_clause .= " AND date BETWEEN ? AND ?";
        $types .= "ss";
        $values[] = $start_of_year;
        $values[] = $end_of_year;
        break;
    default:
        // No additional filtering
        break;
}

$sql = "SELECT date, type, category, amount, description
        FROM transactions
        $where_clause
        ORDER BY date DESC, id DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo "Failed to prepare download statement.";
    exit;
}

if (!bind_dynamic_params($stmt, $types, $values)) {
    http_response_code(500);
    echo "Failed to bind download parameters.";
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

$filename = "FinovateX_History_" . date('Ymd_His');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Type', 'Category', 'Amount (INR)', 'Description']);

    foreach ($transactions as $row) {
        fputcsv($output, [
            $row['date'],
            $row['type'],
            $row['category'],
            number_format((float)$row['amount'], 2, '.', ''),
            $row['description']
        ]);
    }
    fclose($output);
} elseif ($format === 'pdf') {
    require_once __DIR__ . '/lib/simple_pdf.php';

    $pdf = new SimplePDF('FinovateX Transaction History', 'FinovateX');

    $pdf->addLine('FinovateX Transaction History');
    $pdf->addBlankLine();

    if (empty($transactions)) {
        $pdf->addLine('No transactions found for the selected duration.');
    } else {
        $headerLine = sprintf('%-12s %-8s %-18s %-12s %s', 'Date', 'Type', 'Category', 'Amount', 'Description');
        $divider = str_repeat('-', strlen($headerLine));
        $pdf->addLine($headerLine);
        $pdf->addLine($divider);

        foreach ($transactions as $row) {
            $description = $row['description'] ?? '';
            $description = preg_replace('/\s+/', ' ', $description);
            $description = substr($description, 0, 50);

            $line = sprintf(
                '%-12s %-8s %-18s %-12s %s',
                $row['date'],
                $row['type'],
                substr($row['category'] ?? '', 0, 18),
                number_format((float)$row['amount'], 2, '.', ''),
                $description
            );
            $pdf->addLine($line);
        }
    }

    $pdf->output($filename . '.pdf');
    exit;
} else {
    $_SESSION['error_message'] = "Invalid download format.";
    redirect(FRONTEND_PAGES_URL . '12DownloadExpenses.html');
}

$conn->close();
exit;
?>
