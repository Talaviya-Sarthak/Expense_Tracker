<!-- handle_income -->
<?php
session_start();
include_once 'config.php';  // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='../FRONTEND/PAGES/2login.html';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if income form is submitted
    if (isset($_POST['income_category'])) {
        $user_id = $_SESSION['user_id'];
        $income_category = trim($_POST['income_category'] ?? '');
        $income_rupees = trim($_POST['income_rupees'] ?? 0);
        $income_description = trim($_POST['income_description'] ?? '');
        $income_date = trim($_POST['income_date'] ?? '');

        // Validate required fields
        if (empty($income_category) || $income_rupees <= 0 || empty($income_date)) {
            echo "<script>alert('Please fill all required fields with valid values.'); window.history.back();</script>";
            exit();
        }
        $income_rupees = floatval($income_rupees);

        // Prepare and execute INSERT query
        $stmt = $conn->prepare("INSERT INTO incomes (user_id, category, amount, description, date) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isdss", $user_id, $income_category, $income_rupees, $income_description, $income_date);
        if ($stmt->execute()) {
            echo "<script>alert('Income entry added successfully.'); window.location.href='../FRONTEND/PAGES/4dashboard.html';</script>";
        } else {
            echo "<script>alert('Error adding income entry: " . $stmt->error . "'); window.history.back();</script>";
        }

        $stmt->close();
        $conn->close();
    } else {
        // Not an income form submission; handle other cases or redirect
        header("Location: ../FRONTEND/PAGES/4dashboard.html");
        exit();
    }
} else {
    header("Location: ../FRONTEND/PAGES/4dashboard.html");
    exit();
}
?>