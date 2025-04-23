<?php
require_once 'config.php';

// Get all parking zones
$stmt = $conn->prepare("SELECT * FROM parking_zones ORDER BY id");
$stmt->execute();
$zones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get the latest announcement
$stmt = $conn->prepare("SELECT title, content FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$announcement = '';
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $announcement = $row['title'];
}

// Include the HTML template
include 'index.html';

// Output dynamic script to update parking zones
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update parking zone statuses
    const zones = " . json_encode($zones) . ";
    
    zones.forEach(zone => {
        const zoneElement = document.querySelector(`.parking-zone[data-zone='${zone.id}']`);
        if (zoneElement) {
            const statusElement = zoneElement.querySelector('.zone-status');
            if (statusElement) {
                // Remove all status classes
                statusElement.classList.remove('empty', 'h-full', 'full', 'staff');
                
                // Add the current status class
                statusElement.classList.add(zone.status.toLowerCase());
                
                // Update the text
                statusElement.textContent = zone.status;
            }
        }
    });
    
    // Make sure click handlers are set up
    const parkingZones = document.querySelectorAll('.parking-zone');
    parkingZones.forEach(zone => {
        zone.style.cursor = 'pointer';
        zone.addEventListener('click', function() {
            const zoneId = this.getAttribute('data-zone');
            window.location.href = 'parking-zone-detail.php?zone=' + zoneId;
        });
    });
});
</script>";

// Check if user is logged in and display appropriate information
if (is_logged_in()) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update login button to show logout
        const loginButton = document.querySelector('.login-btn');
        if (loginButton) {
            loginButton.textContent = 'LOGOUT';
            loginButton.onclick = function() {
                window.location.href = 'logout.php';
            };
        }
        
        // Add welcome message
        const header = document.querySelector('header');
        if (header) {
            const welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'welcome-message';
            welcomeDiv.style.marginRight = '20px';
            welcomeDiv.style.color = '#333';
            welcomeDiv.textContent = 'Welcome, " . htmlspecialchars($_SESSION['full_name']) . "';
            
            const loginButtons = document.querySelector('.login-buttons');
            if (loginButtons) {
                loginButtons.prepend(welcomeDiv);
            }
        }
    });
    </script>";
}
?> 