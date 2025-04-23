<?php
require_once 'config.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Log logout activity
    $stmt = $conn->prepare("INSERT INTO system_logs (log_type, log_title, user_id, ip_address) VALUES (?, ?, ?, ?)");
    $log_type = "logout";
    $log_title = "User logout";
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ssis", $log_type, $log_title, $user_id, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("location: login.php");
exit();
?> 