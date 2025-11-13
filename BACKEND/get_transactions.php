<?php
// BACKEND/get_transactions.php (DEBUG version - use locally only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
session_start();

// require DB
require_once 'config.php';

// simple helper to bind params dynamically
function stmt_bind_params(mysqli_stmt $stmt, string $types, array $values) {
    if ($types === '' || empty($values)) return true;
    // bind_param requires references
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($values); $i++) {
        $bind_name = 'param' . $i;
        $$bind_name = $values[$i];
        $bind_names[] = &$$bind_name;
    }
    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// ---- quick checks ----
if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection ($conn) not found or invalid. Check config.php.']);
    exit;
}

// AUTH check
if (!isset($_SESSION['user_id'])) {
    // For debug convenience you can temporarily uncomment the next line to test with a specific user id:
    // $_SESSION['user_id'] = 1;
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - session user_id not set. Are you logged in?']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Basic input / filters
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(500, max(10, (int)($_GET['limit'] ?? 200)));
$offset = ($page - 1) * $limit;

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

$where = " WHERE user_id = ? ";
$types = "i";
$params = [$user_id];

if ($type === 'Income' || $type === 'Expense') {
    $where .= " AND type = ? ";
    $types .= "s";
    $params[] = $type;
}
if ($start_date) {
    $where .= " AND date >= ? ";
    $types .= "s";
    $params[] = $start_date . " 00:00:00";
}
if ($end_date) {
    $where .= " AND date <= ? ";
    $types .= "s";
    $params[] = $end_date . " 23:59:59";
}

// COUNT
$countSql = "SELECT COUNT(*) FROM transactions" . $where;
$stmt = $conn->prepare($countSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed (count)', 'detail' => $conn->error, 'sql' => $countSql, 'params' => $params]);
    exit;
}
if (!stmt_bind_params($stmt, $types, $params)) {
    http_response_code(500);
    echo json_encode(['error' => 'bind_param failed (count)']);
    exit;
}
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed (count)', 'detail' => $stmt->error]);
    exit;
}
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total = (int)$total;

// FETCH
$dataSql = "SELECT id, type, category, amount, quantity, description, date
            FROM transactions
            $where
            ORDER BY date DESC
            LIMIT ? OFFSET ?";

$types2 = $types . "ii";
$params2 = $params;
$params2[] = $limit;
$params2[] = $offset;

$stmt = $conn->prepare($dataSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed (data)', 'detail' => $conn->error, 'sql' => $dataSql]);
    exit;
}
if (!stmt_bind_params($stmt, $types2, $params2)) {
    http_response_code(500);
    echo json_encode(['error' => 'bind_param failed (data)', 'types' => $types2, 'params' => $params2]);
    exit;
}
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed (data)', 'detail' => $stmt->error]);
    exit;
}

$res = $stmt->get_result();
if ($res === false) {
    http_response_code(500);
    echo json_encode(['error' => 'get_result failed', 'detail' => $stmt->error]);
    exit;
}

$rows = [];
while ($r = $res->fetch_assoc()) {
    $r['id'] = (int)$r['id'];
    $r['amount'] = (float)$r['amount'];
    $r['quantity'] = isset($r['quantity']) ? (int)$r['quantity'] : null;
    $rows[] = $r;
}
$stmt->close();

echo json_encode([
    'page' => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => $total > 0 ? (int)ceil($total / $limit) : 0,
    'data' => $rows
]);
exit;
