<?php
require_once 'config.php';

$error_message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['student-id']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id, username, password, full_name, user_type FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Log login activity
                $stmt = $conn->prepare("INSERT INTO system_logs (log_type, log_title, user_id, ip_address) VALUES (?, ?, ?, ?)");
                $log_type = "login";
                $log_title = "User login";
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $stmt->bind_param("ssis", $log_type, $log_title, $user['id'], $ip_address);
                $stmt->execute();
                
                // Redirect based on user type
                if ($user['user_type'] == 'admin') {
                    header("location: admin-dashboard.php");
                } else {
                    header("location: index.php");
                }
                exit();
            } else {
                $error_message = "Invalid password";
            }
        } else {
            $error_message = "Username not found";
        }
        
        $stmt->close();
    }
}

// Include the HTML structure
include 'student-login.html';

// Display error message if any
if (!empty($error_message)) {
    echo "<script>alert('$error_message');</script>";
}
?> 