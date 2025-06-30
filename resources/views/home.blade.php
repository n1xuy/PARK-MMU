<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MMU Parking Finder</title>
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        @if (session('cleared'))
            <div class="clear-notice">{{ session('cleared') }}</div>
        @endif
        
        <header>
            <div class="logo">
                    <img src="{{ asset('images/(1)LOGO.png') }}" alt="MMU Parking Logo" class="logo-img">
            </div>
            @auth
            <div class="user-dropdown">
                <button class="login-btn" id="userDropdownBtn">
                    {{ Auth::user()->fullname }} ▼
                </button>
                <div class="dropdown-content" id="dropdownContent">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
            @else
            <button onclick="window.location='{{ route('student.login') }}'" class="login-btn">Login</button>
            @endauth
        </header>

        <div class="announcement-bar">
            <div class="announcement-content">
                @if ($announcement)
                    <div class="announcement-display">
                        <h3>{{ $announcement->title }}</h3><br> 
                        {{ $announcement->date }} at {{ \Carbon\Carbon::parse($announcement->time)->format('h:i A') }}<br>
                        {{ $announcement->details }}
                    </div>
                @else
                    <div class="announcement-display">🚫 No current announcements.</div>
                @endif
            </div>
        </div>

        <div class="main-content">
            <div class="map-container">
                <div id="map" style="width: 100%; height: 100%; border-radius: 10px;"></div>
            </div>

            <div class="legend">
                <h3>LEGEND :</h3>
                <div class="legend-items">
                    <div class="legend-item">
                        <div class="color-box full-dnl"></div>
                        <span>FULL (DNL)</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box h-full-dnl"></div>
                        <span>H-FULL (DNL)</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box empty-dnl"></div>
                        <span>EMPTY (DNL)</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box full"></div>
                        <span>FULL</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box h-full"></div>
                        <span>HALF-FULL</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box empty"></div>
                        <span>EMPTY</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box staff"></div>
                        <span>STAFF</span>
                    </div>
                    <div class="legend-item">
                        <div class="color-box blocked"></div>
                        <span>BLOCKED</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="logo-watermark">
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Watermark" class="watermark">
        </div>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            let map;
            let parkingMarkers = [];
            let zonesData = [];

            function formatTime(timeString) {
                if (!timeString) return '';
                try {
                    const [hours, minutes] = timeString.split(':');
                    const hour = parseInt(hours);
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const displayHour = hour % 12 || 12;
                    return `${displayHour}:${minutes} ${ampm}`;
                } catch (e) {
                    return timeString;
                }
            }

            function formatDate(dateString) {
                if (!dateString) return '';
                try {
                    const date = new Date(dateString + 'T00:00:00');
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                } catch (e) {
                    return dateString;
                }
            }

            function getMarkerColor(status, zoneType, isBlocked) {
                if (isBlocked) return '#6c757d'; // Gray for blocked
                if (zoneType === 'staff') return '#2196F3';

                if (status.startsWith('dnl_')) {
                    switch(status) {
                        case 'dnl_full': 'repeating-linear-gradient(45deg, #000000, #000000 5px, #f5b7b1 5px, #f5b7b1 10px)';
                        case 'dnl_half_full': return 'repeating-linear-gradient(45deg, #000000, #000000 5px, #ffe599 5px, #ffe599 10px)';
                        case 'dnl_empty': return 'repeating-linear-gradient(45deg, #000000, #000000 5px, #b6e7c9 5px, #b6e7c9 10px)';
                        default: return '#b6e7c9';
                    }
                }
                
                switch(status) {
                    case 'full': return '#dc3545'; 
                    case 'half_full': return '#ffc107';
                    case 'empty': return '#28a745';
                    default: return '#28a745'; // Default green
                }
            }

            function getStatusText(status, zoneType, isBlocked) {
                if (isBlocked) return 'BLOCKED';
                if (zoneType === 'staff') return 'STAFF';
                if (status.startsWith('dnl_')) {
                    return status.replace('dnl_', '').toUpperCase() + ' (DNL)';
                }
                return status.toUpperCase().replace('_', '-');  
            }

            function initializeMap() {
                // Initialize the map centered on MMU Cyberjaya
                map = L.map('map').setView([2.9277394, 101.6407846], 17
                );
                //2.9277394,101.6407846,17

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Load initial parking zones
                loadParkingZones();
            }

            function loadParkingZones() {
                fetch('/zones-for-map')
                    .then(response => response.json())
                    .then(data => {
                        zonesData = data;
                        createParkingMarkers();
                    })
                    .catch(error => {
                        console.error('Error loading parking zones:', error);
                        // Fallback: try the alternative endpoint
                        fetch('/zone-statuses')
                            .then(response => response.json())
                            .then(data => {
                                zonesData = data;
                                createParkingMarkers();
                            })
                            .catch(err => {
                                console.error('Error loading zone statuses:', err);
                            });
                    });
            }

            function createParkingMarkers() {
            // Clear existing markers
            parkingMarkers.forEach(marker => map.removeLayer(marker));
            parkingMarkers = [];

            console.log('Total zones received:', zonesData.length);
            console.log('All zones data:', zonesData);

            zonesData.forEach(zone => {
                // Skip zones without coordinates
                if (!zone.latitude || !zone.longitude) return;

                console.log(`Processing zone ${zone.zone_number}:`, zone);
                console.log(`Zone ${zone.zone_number} - future_block exists:`, !!zone.future_block);
                if (zone.future_block) {
                    console.log(`Zone ${zone.zone_number} - future_block data:`, zone.future_block);
                }

                const isBlocked = zone.is_blocked || zone.current_status === 'blocked';
                const markerColor = getMarkerColor(zone.current_status, zone.zone_type, isBlocked);
                const statusText = getStatusText(zone.current_status, zone.zone_type, isBlocked);

                // Create custom marker HTML
                const markerHtml = `
                    <div style="
                        background: ${markerColor};
                        border: 3px solid #333;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 12px;
                        color: ${markerColor === '#ffc107' ? '#333' : 'white'};
                        cursor: pointer;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        P${zone.zone_number}
                    </div>
                `;

                // Create marker
                const marker = L.marker([zone.latitude, zone.longitude], {
                    icon: L.divIcon({
                        className: 'custom-parking-icon',
                        html: markerHtml,
                        iconSize: [40, 40],
                        iconAnchor: [20, 20],
                        popupAnchor: [0, -25]
                    })
                });

                // Create popup content
                let popupContent = `
                    <div style="text-align: center; padding: 10px; min-width: 150px;">
                        <h4 style="margin: 0 0 8px 0; color: #333;">${zone.zone_name || 'Parking Zone ' + zone.zone_number}</h4>
                        <div style="
                            background: ${markerColor};
                            color: ${markerColor === '#ffc107' ? '#333' : 'white'};
                            padding: 4px 12px;
                            border-radius: 15px;
                            font-weight: bold;
                            margin: 8px 0;
                            display: inline-block;
                        ">
                            ${statusText}
                        </div>
                `;

                // DEBUG: Always show this test message first
                //popupContent += `
                    //<div style="margin: 10px 0; padding: 5px; background: lightblue; font-size: 12px;">
                        //DEBUG: Zone ${zone.zone_number}<br>
                        //Has future_block: ${zone.future_block ? 'YES' : 'NO'}<br>
                        //Is blocked: ${isBlocked ? 'YES' : 'NO'}
                    //</div>
                //`;

                if (isBlocked && zone.block_reason) {
                    popupContent += `<p style="margin: 8px 0; font-size: 14px;"><strong>Reason:</strong> ${zone.block_reason}</p>`;
                    if (zone.block_date) {
                        popupContent += `<p style="margin: 4px 0; font-size: 12px;"><strong>Date:</strong> ${formatDate(zone.block_date)}</p>`;
                    }
                    if (zone.block_start_time && zone.block_end_time) {
                        popupContent += `<p style="margin: 4px 0; font-size: 12px;"><strong>Time:</strong> ${formatTime(zone.block_start_time)} - ${formatTime(zone.block_end_time)}</p>`;
                    }
                }

                // FUTURE BLOCK CHECK WITH DETAILED LOGGING (Tester)
                console.log(`Zone ${zone.zone_number} - About to check future block conditions:`);
                console.log(`  - isBlocked: ${isBlocked}`);
                console.log(`  - zone.future_block exists: ${!!zone.future_block}`);
                
                if (!isBlocked && zone.future_block) {
                     new Date(zone.future_block.date + 'T' + zone.future_block.start_time) > new Date()
                    console.log(`Zone ${zone.zone_number} - ADDING FUTURE BLOCK TEXT`);
                    popupContent += `<div style="margin: 10px 0; color: orange; font-size: 13px; background: lightyellow; padding: 5px;">
                        ⚠️ <b>Future Block Scheduled:</b><br>
                        <b>Date:</b> ${formatDate(zone.future_block.date)}<br>
                        <b>Time:</b> ${formatTime(zone.future_block.start_time)} - ${formatTime(zone.future_block.end_time)}<br>
                        <b>Reason:</b> ${zone.future_block.reason}<br>
                        <b>Note:</b> Please plan alternative parking during this time.
                    </div>`;
                } else {
                    console.log(`Zone ${zone.zone_number} - NOT ADDING FUTURE BLOCK TEXT because:`);
                    if (isBlocked) console.log(`  - Zone is currently blocked`);
                    if (!zone.future_block) console.log(`  - No future_block data`);
                }

                popupContent += `
                        <button onclick="handleMarkerClick(${zone.zone_number}, ${isBlocked}, '${zone.block_reason || ''}')" style="
                            background: linear-gradient(45deg, #667eea, #764ba2);
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 20px;
                            cursor: pointer;
                            margin-top: 8px;
                            font-weight: 600;
                            transition: all 0.3s;
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            ${isBlocked ? 'View Block Info' : 'View Details'}
                        </button>
                    </div>
                `;

                marker.bindPopup(popupContent);
                marker.addTo(map);
                parkingMarkers.push(marker);
            });
            }

            // Handle marker clicks
            function handleMarkerClick(zoneNumber, isBlocked, blockReason) {
                if (isBlocked) {
                    // Find the zone data for block info
                    const zone = zonesData.find(z => z.zone_number == zoneNumber);
                    if (zone) {
                        let message = `🚫 Zone P${zoneNumber} is BLOCKED.\n\nReason: ${blockReason || 'No reason specified'}`;
                        
                        if (zone.block_date) {
                            message += `\nDate: ${formatDate(zone.block_date)}`;
                        }
                        
                        if (zone.block_start_time && zone.block_end_time) {
                            message += `\nTime: ${formatTime(zone.block_start_time)} - ${formatTime(zone.block_end_time)}`;
                        }
                        
                        alert(message);
                    }
                } else {
                    // Navigate to detail page
                    window.location.href = `/parking-detail/${zoneNumber}`;
                }
            }

            function updateZoneStatuses() {
                return fetch('/zone-statuses')
                    .then(response => response.json())
                    .then(data => {
                        zonesData = data;
                        createParkingMarkers();
                    })
                    .catch(error => {
                        console.error('Error updating zone statuses:', error);
                    });
            }

            // Announcement functions
            function updateAnnouncement(title, details, time) {
                const announcementElement = document.getElementById('current-announcement');
                const timeElement = document.getElementById('announcement-time');
                
                if (announcementElement) {
                    announcementElement.textContent = `${title} - ${details}`;
                    if (timeElement) {
                        timeElement.textContent = `(${time})`;
                    } else {
                        const timeSpan = document.createElement('small');
                        timeSpan.id = 'announcement-time';
                        timeSpan.textContent = `(${time})`;
                        announcementElement.parentNode.insertBefore(timeSpan, announcementElement.nextSibling);
                    }
                }
            }

            // Event listeners
            window.addEventListener('announcementUpdated', function(e) {
                updateAnnouncement(e.detail.title, e.detail.details, e.detail.time);
            });

            window.addEventListener('storage', function(event) {
                if (event.key === 'announcementUpdate') {
                    const data = JSON.parse(event.newValue);
                    updateAnnouncement(data.title, data.details, data.time);
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                // Dropdown toggle
                const dropdownBtn = document.getElementById('userDropdownBtn');
                if (dropdownBtn) {
                    dropdownBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.getElementById('dropdownContent').classList.toggle('show');
                    });
                }

                // Close dropdown when clicking outside
                window.addEventListener('click', function(event) {
                    if (!event.target.matches('#userDropdownBtn')) {
                        document.querySelectorAll('.dropdown-content.show').forEach(dropdown => {
                            dropdown.classList.remove('show');
                        });
                    }
                });

                // Initialize map
                initializeMap();

                // Update zone statuses periodically (every 5 seconds)
                setInterval(() => updateZoneStatuses(), 5000);

                // Update when page becomes visible
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        updateZoneStatuses();
                    }
                });

                // Update when page gains focus
                window.addEventListener('focus', function() {
                    updateZoneStatuses();
                });
            });
        </script>
    </body>
</html>