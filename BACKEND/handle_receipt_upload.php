<?php
include 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to upload receipts.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['receipt_file'])) {
    $user_id = $_SESSION['user_id'];
    $receipt_description = $_POST['receipt_description'] ?? null;

    $file = $_FILES['receipt_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "File upload error: " . $file['error'];
        redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
    }

    $file_name = basename($file['name']);
    $file_tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = mime_content_type($file_tmp_name);

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.";
        redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
    }

    if ($file_size > $max_file_size) {
        $_SESSION['error_message'] = "File size exceeds 5MB limit.";
        redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
    }

    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = uniqid('receipt_') . '.' . $file_ext;
    $upload_path = UPLOAD_DIR . $new_file_name;

    if (move_uploaded_file($file_tmp_name, $upload_path)) {
        $receipt_path_db = 'IMAGES/uploads/' . $new_file_name; // Path to store in DB

        // Store receipt info in a new 'receipts' table
        $stmt = $conn->prepare("INSERT INTO receipts (user_id, file_path, description, upload_date) VALUES (?, ?, ?, CURDATE())");
        $stmt->bind_param("iss", $user_id, $receipt_path_db, $receipt_description);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Receipt uploaded successfully!";
            redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
        } else {
            unlink($upload_path); // Delete the uploaded file if DB insert fails
            $_SESSION['error_message'] = "Error saving receipt info to database: " . $stmt->error;
            redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Failed to move uploaded file.";
        redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
    }
} else {
    $_SESSION['error_message'] = "No file uploaded or invalid request.";
    redirect(FRONTEND_PAGES_URL . '14RecieptScanner.html');
}

$conn->close();
?>
