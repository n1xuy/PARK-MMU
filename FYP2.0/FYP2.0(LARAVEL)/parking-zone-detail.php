<?php
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get zone ID from URL
$zone_id = isset($_GET['zone']) ? intval($_GET['zone']) : 1;

// Get zone information
$stmt = $conn->prepare("SELECT * FROM parking_zones WHERE id = ?");
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Zone not found, redirect to index
    redirect('index.php');
}

$zone = $result->fetch_assoc();

// Handle status report submission
$report_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    if (!in_array($status, ['EMPTY', 'HALF-FULL', 'FULL'])) {
        $report_message = "Invalid status reported";
    } else {
        // Record the report
        $stmt = $conn->prepare("INSERT INTO reports (zone_id, user_id, status) VALUES (?, ?, ?)");
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("iis", $zone_id, $user_id, $status);
        
        if ($stmt->execute()) {
            // Update zone status
            $stmt = $conn->prepare("UPDATE parking_zones SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $zone_id);
            $stmt->execute();
            
            // Log the report
            $stmt = $conn->prepare("INSERT INTO system_logs (log_type, log_title, user_id, ip_address) VALUES (?, ?, ?, ?)");
            $log_type = "report";
            $log_title = "Parking status report for Zone P{$zone_id}";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("ssis", $log_type, $log_title, $user_id, $ip_address);
            $stmt->execute();
            
            $report_message = "Thank you for reporting! The status has been updated to {$status}";
        } else {
            $report_message = "Error submitting report. Please try again.";
        }
    }
}

// Get report count for today
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reports WHERE zone_id = ? AND DATE(report_time) = ?");
$stmt->bind_param("is", $zone_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$report_count = $row['count'];

// Get the last report time
$stmt = $conn->prepare("SELECT report_time FROM reports WHERE zone_id = ? ORDER BY report_time DESC LIMIT 1");
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_report_time = date('h:i A', strtotime($row['report_time']));
    $last_report_date = date('M j, Y', strtotime($row['report_time']));
} else {
    $last_report_time = "-";
    $last_report_date = "-";
}

// Include the HTML template
$zone_name = "PARKING ZONE {$zone_id}";
$current_status = $zone['status'];

// Parse coordinates for Google Maps
$coordinates = explode(',', $zone['coordinates']);
$latitude = isset($coordinates[0]) ? trim($coordinates[0]) : '2.9290';
$longitude = isset($coordinates[1]) ? trim($coordinates[1]) : '101.7774';

// Output any JavaScript messages
$js_messages = '';
if (!empty($report_message)) {
    $js_messages = "<script>alert('" . addslashes($report_message) . "');</script>";
}

// Include the HTML template
include 'parking-zone-detail.html';

// Output JavaScript messages
echo $js_messages;

// Add script to update dynamic content
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update zone name
    document.getElementById('zoneName').textContent = '{$zone_name}';
    
    // Update report counts
    document.getElementById('totalReports').textContent = '{$report_count}';
    
    // Update times
    document.getElementById('lastReportTime').textContent = '{$last_report_time}';
    document.getElementById('lastReportDate').textContent = '{$last_report_date}';
    
    // Override the reportStatus function
    window.reportStatus = function() {
        if (!selectedStatus) {
            alert('Please select a status (green, orange, or red)');
            return;
        }
        
        // Submit form via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'parking-zone-detail.php?zone={$zone_id}', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Reload page to show updated status
                window.location.reload();
            }
        };
        xhr.send('status=' + encodeURIComponent(selectedStatus));
    };
    
    // Override the Google Maps function
    window.openGoogleMaps = function() {
        window.open('https://www.google.com/maps/search/?api=1&query=Multimedia+University+Faculty+of+Computing+and+Informatics', '_blank');
    };
});
</script>";
?> 