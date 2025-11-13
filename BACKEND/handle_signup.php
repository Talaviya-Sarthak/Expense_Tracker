<!-- handle_Signup.php -->
<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $income = trim($_POST['income'] ?? '');

    // Profile picture upload
    $profile_pic_path = '';
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../FRONTEND/IMAGES/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_pic_path = $target_dir . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $profile_pic_path);
    }

    // Insert user WITHOUT password first
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, country, gender, monthly_income, profile_pic_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $email, $phone, $country, $gender, $income, $profile_pic_path);

    if ($stmt->execute()) {
        // Save email in session for password step
        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: ../FRONTEND/PAGES/8password.html");

    } else {
        echo "Error during signup: " . $stmt->error;
    }
    $stmt->close();
}