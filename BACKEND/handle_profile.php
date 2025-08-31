<?php
include 'config.php';

if (!isLoggedIn()) {
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

$user = getLoggedInUser ($conn);
if (!$user) {
    $_SESSION['error_message'] = "User  profile not found.";
    redirect(FRONTEND_PAGES_URL . '2login.html');
}

// Store user data in session to display on the profile page
$_SESSION['user_data'] = $user;

header("Location: ../FRONTEND/PAGES/10profile.html");
exit();
?>
