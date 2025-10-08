<?php
include 'config.php';

if (!isLoggedIn()) {
    redirect(FRONTEND_PAGES_URL . '2login.html?error=' . urlencode('Please login.'));
}

$user_id = (int)$_SESSION['user_id'];

// Support GET for JSON preview, POST for downloads
$method = $_SERVER['REQUEST_METHOD'];
$format = ($method === 'GET') ? ($_GET['format'] ?? '') : ($_POST['format'] ?? 'csv');
$filter_duration = ($method === 'GET') ? ($_GET['duration'] ?? '') : ($_POST['duration'] ?? '');

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

// Fetch expees
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

// Output formats
$filename = "FinovateX_History_" . date('Ymd_His');

if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode($transactions);
} elseif ($format === 'csv') {
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
    // Generate a very simple PDF (one page) without external libraries
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');

    $escapePdfText = function ($text) {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(['(', ')'], ['\\(', '\\)'], $text);
        $text = preg_replace('/\r?\n/', ' ', $text);
        return $text;
    };

    $lines = [];
    $lines[] = 'FinovateX Transaction History';
    $lines[] = '';
    $lines[] = 'Date                Type      Category            Amount (INR)    Description';
    $lines[] = '--------------------------------------------------------------------------------';
    foreach ($transactions as $row) {
        $date = $row['date'];
        $type = $row['type'];
        $cat  = $row['category'];
        $amt  = number_format((float)$row['amount'], 2);
        $desc = $row['description'] ?? '';
        $lines[] = sprintf('%-18s %-9s %-18s %14s    %s', $date, $type, mb_substr($cat,0,18), $amt, mb_substr($desc,0,60));
    }

    // Build PDF content stream
    $content = "BT\n/F1 12 Tf\n";
    $x = 50; $y = 760; $leading = 16;
    $content .= sprintf('%d %d Td\n', $x, $y);
    foreach ($lines as $i => $text) {
        $content .= sprintf('(%s) Tj\n', $escapePdfText($text));
        if ($i < count($lines) - 1) {
            $content .= sprintf('0 -%d Td\n', $leading);
        }
    }
    $content .= "ET\n";

    $len = strlen($content);

    // Assemble minimal PDF
    $pdf  = "%PDF-1.4\n";
    $pdf .= "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
    $pdf .= "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
    $pdf .= "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
    $pdf .= "4 0 obj<< /Length $len >>stream\n" . $content . "endstream endobj\n";
    $pdf .= "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    // Calculate byte offsets for objects (rough but effective by recomputing)
    $offsets = [];
    $cursor = 0;
    $parts = [];
    $parts[1] = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
    $parts[2] = "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
    $parts[3] = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
    $parts[4] = "4 0 obj<< /Length $len >>stream\n" . $content . "endstream endobj\n";
    $parts[5] = "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
    $rebuild = "%PDF-1.4\n"; $cursor = strlen($rebuild);
    for ($i=1; $i<=5; $i++) { $offsets[$i] = $cursor; $rebuild .= $parts[$i]; $cursor = strlen($rebuild); }
    // Now build xref with actual offsets
    $xref  = "xref\n0 6\n0000000000 65535 f \n";
    for ($i=1; $i<=5; $i++) {
        $xref .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $trailer = "trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n" . strlen($rebuild) . "\n%%EOF";
    echo $rebuild . $xref . $trailer;
} else {
    redirect(FRONTEND_PAGES_URL . '12DownloadExpenses.html?error=' . urlencode('Invalid format.'));
}

$conn->close();
exit();
?>
