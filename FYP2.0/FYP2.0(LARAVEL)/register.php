<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = sanitize_input($_POST['fullname']);
    $username = sanitize_input($_POST['student-id']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $user_type = 'student'; // Default user type
    
    // Validate input
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required";
    } elseif ($password != $confirm_password) {
        $error_message = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username already exists";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Email already exists";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username, $hashed_password, $fullname, $email, $user_type);
                
                if ($stmt->execute()) {
                    $success_message = "Registration successful! You can now login.";
                    
                    // Log registration activity
                    $user_id = $conn->insert_id;
                    $stmt = $conn->prepare("INSERT INTO system_logs (log_type, log_title, user_id, ip_address) VALUES (?, ?, ?, ?)");
                    $log_type = "registration";
                    $log_title = "New user registration";
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $stmt->bind_param("ssis", $log_type, $log_title, $user_id, $ip_address);
                    $stmt->execute();
                    
                    // Redirect to login page after 3 seconds
                    header("refresh:3;url=login.php");
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
            }
        }
        
        $stmt->close();
    }
}

// Include the HTML structure
include 'student-register.html';

// Display messages if any
if (!empty($error_message)) {
    echo "<script>alert('$error_message');</script>";
}
if (!empty($success_message)) {
    echo "<script>alert('$success_message');</script>";
}
?> 