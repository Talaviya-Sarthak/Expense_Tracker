<?php
session_start();
include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone"]);
    $country  = trim($_POST["country"]);
    $gender   = trim($_POST["gender"]);
    $income   = trim($_POST["income"]);

    // Handle profile picture upload
    $profilePicPath = NULL;
    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] === UPLOAD_ERR_OK) {
        $targetDir = "../FRONTEND/images/uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
            $profilePicPath = $fileName;
        }
    }

    // Insert user without password first
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, country, gender, income, profile_pic) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $username, $email, $phone, $country, $gender, $income, $profilePicPath);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // get newly created user id
        $_SESSION["user_id"] = $user_id;

        // Redirect to create password page
        header("Location: ../FRONTEND/PAGES/8password.html");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }

    $stmt->close();
  
}
?>
