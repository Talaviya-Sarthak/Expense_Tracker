<?php
session_start();
include 'config.php';

if (!isLoggedIn()) {
    header("Location: ../FRONTEND/PAGES/2login.html?error=" . urlencode('Please login.'));
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, country, gender, monthly_income, profile_pic_path FROM users WHERE id = ?");
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB error']);
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc() ?: [];
$stmt->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
