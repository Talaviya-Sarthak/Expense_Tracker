
<!-- handle_receipt_upload.php -->
<?php

session_start();
include_once 'config.php'; // your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: ../FRONTEND/PAGES/2login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['upload_receipt']) && !empty($_FILES['receipt_files'])) {
    $files = $_FILES['receipt_files'];
    $totalFiles = count($files['name']);

    $stmt = $conn->prepare("INSERT INTO receipts (user_id, file_name, file_type, file_data, uploaded_at) VALUES (?, ?, ?, ?, NOW())");

    for ($i = 0; $i < $totalFiles; $i++) {
        $fileName = $files['name'][$i];
        $fileType = $files['type'][$i];
        $fileData = file_get_contents($files['tmp_name'][$i]);

        $stmt->bind_param("isss", $user_id, $fileName, $fileType, $fileData);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    echo "<script>alert('File(s) uploaded successfully.'); window.location.href='../FRONTEND/PAGES/14RecieptScanner.html';</script>";
} else {
    echo "<script>alert('No file selected.'); window.history.back();</script>";
}
?>
