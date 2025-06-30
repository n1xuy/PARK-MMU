<!DOCTYPE html>
<html>
<head>
    <title>Parking Zone Detail - MMU Parking Finder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/announcement-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/parkingdetails.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <header>
        <div class="logo-section">
            <a href ="{{ route('home') }}">
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="logo">
            </a>
        </div>
    </header>

    <div class="zone-detail-container">
        <div class="zone-name-container">
            <h1 class="zone-name" id="zoneName">  {{ $coordinates['name'] ?? 'Zone '. $zoneNumber}} </h1>
        </div>
        
        <div>
            <p class="report-label">Report Status</p>
        </div>
        
        <div class="zone-info">
            <div class="info-row">
                <div class="info-label">CURRENT STATUS</div>
                <div class="info-value">:</div>
                <div class="info-value" style="color: {{ $status_color }}" id="currentStatus">{{ strtoupper($currentStatus) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">LAST REPORT TIME</div>
                <div class="info-value">:</div>
                <div class="info-value" id="lastReportTime">{{ $lastReport ? $lastReport->created_at->format('h:i A') : 'Never' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">LAST REPORT DATE</div>
                <div class="info-value">:</div>
                <div class="info-value" id="lastReportDate"> {{ $lastReport ? $lastReport->created_at->format('M d, Y') : 'Never' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT TODAY</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalReports">{{ $totals['total'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (FULL)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalFull">{{ $totals['full'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (HALF-FULL)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalHalfFull">{{ $totals['half_full'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (EMPTY)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalEmpty">{{ $totals['empty'] }}</div>
            </div>
        </div>
        
        <div class="button-container">
            <button class="action-button return-btn" onclick="window.location.href='{{ route('home') }}'">
                <img src="images/arrow-back.png" alt="Back" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><path d=\'M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z\' fill=\'%23333333\'/></svg>'">
            </button>
            <button class="action-button location-btn" onclick="openGoogleMaps()">
                <img src="images/location.png" alt="Location" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><path d=\'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z\' fill=\'%23333333\'/></svg>'">
            </button>
        </div>

        <div id="loginNotification" class="login-notification">
            Please login to submit a report
        </div>
        
        <div class="report-section">
            <p class="report-label">Report Your Parking Zone</p>
            
            <div class="report-buttons">
                <button class="color-button green-btn" data-status="1" onclick="submitReport(1, event)">Empty</button>
                <button class="color-button orange-btn" data-status="2" onclick="submitReport(2, event)">Half-Full</button>
                <button class="color-button red-btn" data-status="3" onclick="submitReport(3, event)">Full</button>
            </div>

            <div class="delete-report-container" id="deleteReportContainer" style="display: none;">
                <button class="btn-danger" onclick="deleteReport()">Retrieve</button>
            </div>
        </div>
        
        <div class="logo-watermark">    
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="footer-logo">
        </div>

    <script>
    const currentZone = {
        zone_number: {{ $zoneNumber }},
        zone_type: "{{ $zone->zone_type }}",
        zone_name: "{{ $coordinates['name'] ?? 'Zone ' . $zoneNumber }}"    
    };
    const currentZoneNumber = currentZone.zone_number;
    const initialStatus = "{{ $currentStatus }}";
    const initialStatusColor = "{{ $status_color }}";
    const currentZoneName = currentZone.zone_name;

    let currentUserStatus = null;

    async function updateStats() {
        try {
            const response = await fetch(`{{ route('zone.stats') }}?zone_id=${currentZoneNumber}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            document.getElementById('zoneName').textContent = currentZone.zone_name;
            document.getElementById('currentStatus').textContent = data.status.toUpperCase();
            document.getElementById('currentStatus').style.color = data.status_color;
            document.getElementById('totalReports').textContent = data.total_today || '0';
            document.getElementById('totalEmpty').textContent = data.total_empty || '0';
            document.getElementById('totalHalfFull').textContent = data.total_half_full || '0';
            document.getElementById('totalFull').textContent = data.total_full || '0';
            
            if (data.last_report) {
                document.getElementById('lastReportTime').textContent = data.last_report.time || '-';
                document.getElementById('lastReportDate').textContent = data.last_report.date || '-';
            } else {
                document.getElementById('lastReportTime').textContent = 'Never';
                document.getElementById('lastReportDate').textContent = 'Never';
            }
            
        } catch (error) {
            console.error('Update error:', error);
        }
    }

    window.addEventListener('DOMContentLoaded', () => { 
        setInterval(updateStats, 30000); // Update every 30 seconds
        
        // Check user report status on page load
        checkUserReport();
    });

    function showLoginNotification() {
        const notification = document.getElementById('loginNotification');
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000); // Hide after 3 seconds
    }

    async function submitReport(status, event) {
        // First check if user is authenticated
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

        if (!isAuthenticated) {
            showLoginNotification();
            setTimeout(() => {
                window.location.href = '{{ route("student.login") }}?redirect=' + encodeURIComponent(window.location.href);
            }, 1000);
            return;
        }

        const button = event.target;
        const allButtons = document.querySelectorAll('.color-button');
        
        // Disable all buttons during submission
        if (currentUserStatus === status) {
        
        // Disable all buttons during submission
        allButtons.forEach(btn => {
            btn.disabled = false;
            btn.classList.add('disabled');
        });
        return;
        }
        try {
            let endpoint, method;
            
            if (currentUserStatus !== null) {
                // User is switching their report - use update endpoint
                endpoint = '/update-report';
                method = 'PUT';
            } else {
                // User is submitting a new report
                endpoint = '/submit-report';
                method = 'POST';
            }

            const response = await fetch(endpoint, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    zone_id: currentZoneNumber,
                    status: status
                })
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to submit report');
            }

            // Update all UI elements with the new data
            document.getElementById('currentStatus').textContent = data.status.toUpperCase();
            document.getElementById('currentStatus').style.color = data.status_color;
            document.getElementById('totalReports').textContent = data.totals.total;
            document.getElementById('totalEmpty').textContent = data.totals.empty;
            document.getElementById('totalHalfFull').textContent = data.totals.half_full;
            document.getElementById('totalFull').textContent = data.totals.full;
            
            if (data.last_report) {
                document.getElementById('lastReportTime').textContent = data.last_report.time;
                document.getElementById('lastReportDate').textContent = data.last_report.date;
            }
            
            updateButtonSelection(status);
            const previousStatus = currentUserStatus;
            currentUserStatus = status;
            
            document.getElementById('deleteReportContainer').style.display = 'block';

            const actionText = currentUserStatus === null ? 'submitted' : 'updated';
            showNotification('Report submitted successfully!', 'success');
            
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message || 'Failed to submit report', 'error');
        } finally {
            // Re-enable buttons
            allButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('disabled');
            });
        }
    }

    function updateButtonSelection(selectedStatus) {
        const allButtons = document.querySelectorAll('.color-button');
        allButtons.forEach(btn => {
            btn.classList.remove('selected');
        });

        const selectedButton = document.querySelector(`[data-status="${selectedStatus}"]`);
        if (selectedButton) {
            selectedButton.classList.add('selected');
        }
    }

    async function checkUserReport() {
        try {
            const response = await fetch(`/check-report/${currentZoneNumber}`);
            if (!response.ok) throw new Error('Failed to check report status');
            
            const data = await response.json();
            
            if (data.hasReport && data.status) {
                currentUserStatus = data.status;
                updateButtonSelection(data.status);
                document.getElementById('deleteReportContainer').style.display = 'block';
            } else {
                currentUserStatus = null;
                document.querySelectorAll('.color-button').forEach(btn => {
                    btn.classList.remove('selected');
                });
                document.getElementById('deleteReportContainer').style.display = 'none';
            }
        } catch (error) {
            console.error('Error checking report:', error);

            currentUserStatus = null;
            document.querySelectorAll('.color-button').forEach(btn => {
            btn.classList.remove('selected');
            });
            document.getElementById('deleteReportContainer').style.display = 'none';
        }
    }

    async function deleteReport() {
        if (!confirm('Are you sure you want to delete your report?')) return;
        
        try {
            const response = await fetch(`/report/delete/${currentZoneNumber}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete report');
            }

            // Update stats and UI after deletion
            await updateStats();
            currentUserStatus = null;

            document.querySelectorAll('.color-button').forEach(btn => {
                btn.classList.remove('selected');
            });

            document.getElementById('deleteReportContainer').style.display = 'none';
            
            showNotification('Report deleted successfully!', 'success');

        } catch (error) {
            console.error('Error deleting report:', error);
            showNotification(error.message || 'Failed to delete report', 'error');
        }
    }

    // Notification styles  
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.3s forwards;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #F44336;
        }
        @keyframes fadeIn {
            to { opacity: 1; top: 30px; }
        }
        .fade-out {
            animation: fadeOut 0.3s forwards;
        }
        @keyframes fadeOut {
            to { opacity: 0; top: 20px; }
        }
    `;
    document.head.appendChild(style);

    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function openGoogleMaps() {
        // You'll need to add actual coordinates for each zone
        const zoneCoordinates = {
            1: "2.92824,101.64086",
            2: "2.92951,101.64075",
            3: "2.93038,101.64132",
            4: "2.93016,101.64275",
            5: "2.92776,101.64315",
            6: "2.92797,101.64350", 
            7: "2.92689,101.64457",
            8: "2.92440,101.64567",
            9: "2.92554,101.64111",
            10: "2.92742,101.64112",  
            11: "2.92758,101.64005", 
            12: "2.9274014,101.6426726", 
            13: "2.925160,101.6458500",     
            14: "2.92475,101.6431829", 
            15: "2.92595,101.6437733", 
            16: "2.92611,101.64039", 
            17: "2.92900,101.6428055", 
            18: "2.92551,101.64541", 
            19: "2.92842,101.63918", 
            20: "2.9251259,101.642052",  
            21: "2.9265186,101.6430174", 
            22: "2.9264535,101.6409169", 
            23: "2.92770,101.63925", 
        };
        
        const coords = zoneCoordinates[currentZoneNumber];
        window.open(`https://www.google.com/maps/search/?api=1&query=${coords}`);
    }

    // Initialize everything when page loads
    updateStats();
    checkUserReport();


    </script>
</body>
</html>