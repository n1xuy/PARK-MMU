<!DOCTYPE html>
<html>
<head>
    <title>Parking Zone Detail - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/announcement-styles.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background-color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
        }

        .logo {
            height: 35px;
        }
        
        @keyframes slide {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }
        
        .zone-detail-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        
        .zone-name-container {
            background-color: #e0e0e0;
            border-radius: 25px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .zone-name {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .zone-info {
            margin: 20px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }
        
        .info-label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .info-value {
            font-size: 14px;
        }
        
        .report-label {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
        }
        
        .button-container {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #e0e0e0;
            padding: 10px;
            border-radius: 30px;
        }
        
        .action-button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .action-button img {
            width: 25px;
            height: 25px;
        }
        
        .report-section {
            margin-top: 150px;
            background-color: #e0e0e0;
            border-radius: 25px;
            padding: 15px;
            overflow: hidden;
        }
        
        .report-buttons {
            display: flex;
            justify-content: space-around;
            padding: 20px 0;
        }
        
        .color-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            text-indent: -9999px;
            overflow: hidden;
            position: relative;
        }
        
        .green-btn {
            background-color: #4CAF50;
        }
        
        .orange-btn {
            background-color: #FF9800;
        }
        
        .red-btn {
            background-color: #F44336;
        }
        
        .report-btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            background-color: #e0e0e0;
            color: #333;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-top: 1px solid #ccc;
            cursor: pointer;
        }
        
        .footer-logo {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 70px;
            opacity: 0.5;
        }

        .color-button.active {
            border: 3px solid #000 !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .color-button:hover {
            opacity: 0.9;
        }

        .status-highlight {
        font-weight: bold;
        animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
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
            <h1 class="zone-name" id="zoneName">PARKING ZONE 1</h1>
        </div>
        
        <div>
            <p class="report-label">Report Status</p>
        </div>
        
        <div class="zone-info">
            <div class="info-row">
                <div class="info-label">CURRENT STATUS</div>
                <div class="info-value">:</div>
                <div class="info-value" id="currentStatus">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">LAST REPORT TIME</div>
                <div class="info-value">:</div>
                <div class="info-value" id="lastReportTime">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">LAST REPORT DATE</div>
                <div class="info-value">:</div>
                <div class="info-value" id="lastReportDate">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT TODAY</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalReports">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (FULL)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalFull">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (HALF-FULL)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalHalfFull">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">TOTAL REPORT (EMPTY)</div>
                <div class="info-value">:</div>
                <div class="info-value" id="totalEmpty">-</div>
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
        
        <div class="report-section">
            <p class="report-label">Report Your Parking Zone</p>
            
        <div class="report-buttons">
            <button class="color-button green-btn" onclick="submitReport(1)">Empty</button>
            <button class="color-button orange-btn" onclick="submitReport(2)">Half-Full</button>
            <button class="color-button red-btn" onclick="submitReport(3)">Full</button>
            </div>
        <div class="logo-watermark">    
        <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="footer-logo">
    </div>
    <?php
    $currentZoneId = request()->query('zone', 1); //zoneid
    $currentZone = $currentZone ?? \App\Models\ParkingZone::where('zone_number', 1)->first();
    ?>
    <script>
        // Zone data
        const currentZone = @json($currentZone ?? ['zone_number' => 1]);
        const currentZoneNumber = currentZone.zone_number;
        async function updateStats() {
            try {
                const response = await fetch(`/zone-stats?zone_id=${currentZoneNumber}`);
                const data = await response.json();
                console.log(data); 
                const majority = getMajorityStatus(data);
                let statusText = '-';

                switch (majority) {
                    case 'empty':
                        statusText = 'Empty';
                        break;
                    case 'halfFull':
                        statusText = 'Half-Full';
                        break;
                    case 'full':
                        statusText = 'Full';
                        break;
                }

                document.getElementById('currentStatus').textContent = statusText;
                document.getElementById('totalReports').textContent = data.total_today;
                document.getElementById('totalEmpty').textContent = data.total_empty;
                document.getElementById('totalHalfFull').textContent = data.total_half_full;
                document.getElementById('totalFull').textContent = data.total_full;
                
                if (data.last_report) {
                    const lastReport = new Date(data.last_report.created_at);
                    document.getElementById('lastReportTime').textContent = lastReport.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    document.getElementById('lastReportDate').textContent = lastReport.toLocaleDateString([], {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                    });
                } else {
                    document.getElementById('lastReportTime').textContent = 'Never';
                    document.getElementById('lastReportDate').textContent = 'Never';
                }
                
                setInterval(updateStats, 30000);
                
            } catch (error) {
                console.error('Update error:', error);
                setTimeout(updateStats, 5000);
            }
        }

            function getMajorityStatus(data) {
                const counts = {
                    empty: data.total_empty,
                    halfFull: data.total_half_full,
                    full: data.total_full
                };
                return Object.keys(counts).reduce((a, b) => counts[a] > counts[b] ? a : b);
            }
        // Report submission
        async function submitReport(status) {
            if (!@json(auth()->check())) {
                window.location.href = '/student-login?redirect=' + encodeURIComponent(window.location.pathname);
                return;
            }

            const button = event.target;
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Sending...';

            try {
                const response = await fetch('/report-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        zone_id: currentZoneNumber,
                        status: status
                    })
                });

                if (!response.ok) throw new Error('Failed to submit');
                await updateStats();
                alert('Report submitted!');
            } catch (error) {
                console.error(error);
                alert('Error: ' + error.message);
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }

            let statusId;
            if (status === 1) statusId = 'totalEmpty';
            else if (status === 2) statusId = 'totalHalfFull';
            else if (status === 3) statusId = 'totalFull';

            if (statusId) {
                const element = document.getElementById(statusId);
                element.classList.add('status-highlight');
                setTimeout(() => {
                    element.classList.remove('status-highlight');
                }, 3000);
            }

        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.parking-zone').forEach(zone => {
                zone.style.cursor = 'pointer';
                zone.addEventListener('click', function() {
                    window.location.href = `/parking-detail/${this.getAttribute('data-zone')}`;
                });
            });

            // Dropdown menu
            const dropdownBtn = document.getElementById('userDropdownBtn');
            if (dropdownBtn) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.getElementById('dropdownContent').classList.toggle('show');
                });
            }
        });
            updateStats();

        window.onclick = function(event) {
            if (!event.target.matches('#userDropdownBtn')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html> 