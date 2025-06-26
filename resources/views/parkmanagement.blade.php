<!DOCTYPE html>
<html>
<head>
    <title>Parking Management - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/parkmanage.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
                </a>
            </div>
            <div class="admin-title">
                ADMIN{{ Auth::guard('admin')->user()?->username ? ' - ' . Auth::guard('admin')->user()->username : '' }}
            </div>
        </div>
    

        <div class="main-content">
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('future')">Future Blocks</button>
                <button class="tab" onclick="switchTab('history')">Block History</button>
            </div>

            <div id="future-tab" class="tab-content active">
                <div class="search-filter">
                    <input type="text" class="search-input" placeholder="Search by zone or reason..." id="future-search">
                    <select class="filter-select" id="future-filter">
                        <option value="">All Zones</option>
                        <option value="student">Student Zones</option>
                        <option value="staff">Staff Zones</option>
                    </select>
                    <button class="control-btn" onclick="loadFutureBlocks()">🔄 Refresh</button>
                </div>
                <div class="future-blocks-container" id="future-blocks">
                    <div class="no-blocks">Loading future blocks...</div>
                </div>
            </div>

            <div id="history-tab" class="tab-content">
                <div class="search-filter">
                    <input type="text" class="search-input" placeholder="Search history..." id="history-search">
                    <select class="filter-select" id="history-status">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select class="filter-select" id="history-period">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="">All time</option>
                    </select>
                    <button class="control-btn" onclick="loadHistory()">🔄 Refresh</button>
                </div>
                <div class="history-container" id="history-blocks">
                    <div class="no-blocks">Loading block history...</div>
                </div>
            </div>
        </div>

        <div id="blockModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalZoneTitle">Block Parking Zone</h2>
                <form id="blockForm" action="{{ route('admin.parking-blocks.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="zone_id" name="zone_id">
                    <input type="hidden" id="edit_block_id" name="edit_block_id">
                    
                    <div class="form-group">
                        <label for="reason">Details:</label>
                        <textarea id="reason" name="reason" required placeholder="Enter reason for blocking this zone..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required min="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule_type">Schedule Type:</label>
                        <select id="schedule_type" name="schedule_type" onchange="toggleScheduleOptions()">
                            <option value="single">Single Day</option>
                            <option value="weekly">Weekly Recurring</option>
                        </select>
                    </div>

                    <div id="weekly-options" style="display: none;">
                        <div class="form-group">
                            <label>Days of Week:</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="weekly_days[]" value="1"> Monday</label>
                                <label><input type="checkbox" name="weekly_days[]" value="2"> Tuesday</label>
                                <label><input type="checkbox" name="weekly_days[]" value="3"> Wednesday</label>
                                <label><input type="checkbox" name="weekly_days[]" value="4"> Thursday</label>
                                <label><input type="checkbox" name="weekly_days[]" value="5"> Friday</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="recurring_end_date">End Date:</label>
                            <input type="date" id="recurring_end_date" name="recurring_end_date">
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Block Zone</button>
                </form> 
            </div>
        </div>

        <a href="{{ route('admin.menu') }}" class="back-button">
        <img src="{{ asset('images/return page.png') }}" alt="Back">
        </a>


    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];
        let currentTileLayer;
        let isStreetView = true;
        let zonesData = [];
        let parkingMarkers = [];
        let isUpdatingMarkers = false;
        const mmuCenter = [2.92802, 101.64193];

        function initMap() {
            try {
                map = L.map('map', {
                    center: mmuCenter,
                    zoom: 18,
                    zoomControl: true,
                    attributionControl: true
                });

                currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 20
                }).addTo(map);

                map.whenReady(function() {
                    createParkingZoneMarkers();
                });
            } catch (error) {
                console.error('Error initializing map:', error);
                showError('Failed to load map. Please refresh the page.');
            }
        }

        function createParkingZoneMarkers() {
            fetch('/zones-for-map')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    zonesData = data;
                    createParkingMarkers();
                })
                .catch(error => {
                    console.error('Error loading parking zones:', error);
                    showError('Failed to load parking zones. Please refresh the page.');
                });
        }

        function createParkingMarkers() {
            // Clear existing markers
            parkingMarkers.forEach(marker => {
                if (map.hasLayer(marker)) {
                    map.removeLayer(marker);
                }
            });
            parkingMarkers = [];

            if (!zonesData || !Array.isArray(zonesData)) {
                console.error('Invalid zones data:', zonesData);
                return;
            }

            zonesData.forEach(zone => {
                if (!zone.latitude || !zone.longitude) return;

                const isBlocked = zone.is_blocked || zone.current_status === 'blocked';
                const markerColor = getMarkerColor(zone.current_status, zone.zone_type, isBlocked);
                const statusText = getStatusText(zone.current_status, zone.zone_type, isBlocked);

                const markerHtml = `
                    <div style="
                        background: ${markerColor};
                        border: 3px solid #333;
                        border-radius: 50%;
                        width: 45px;
                        height: 45px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 15px;
                        color: ${markerColor === '#ffc107' ? '#333' : 'white'};
                        cursor: pointer;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        P${zone.zone_number}
                    </div>
                `;

                const marker = L.marker([zone.latitude, zone.longitude], {
                    icon: L.divIcon({
                        className: 'custom-parking-icon',
                        html: markerHtml,
                        iconSize: [40, 40],
                        iconAnchor: [20, 20],
                        popupAnchor: [0, -25]
                    })
                });

                let popupContent = `
                    <div style="text-align: center; padding: 10px; min-width: 200px;">
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
                        <p style="margin: 4px 0; font-size: 12px;"><strong>Type:</strong> ${zone.zone_type.charAt(0).toUpperCase() + zone.zone_type.slice(1)}</p>
                `;

                if (isBlocked && zone.block_reason) {
                    popupContent += `<p style="margin: 8px 0; font-size: 14px;"><strong>Reason:</strong> ${zone.block_reason}</p>`;
                    
                    if (zone.block_date) {
                        popupContent += `<p style="margin: 4px 0; font-size: 12px;"><strong>Date:</strong> ${formatDate(zone.block_date)}</p>`;
                    }
                    
                    if (zone.block_start_time && zone.block_end_time) {
                        popupContent += `<p style="margin: 4px 0; font-size: 12px;"><strong>Time:</strong> ${formatTime(zone.block_start_time)} - ${formatTime(zone.block_end_time)}</p>`;
                    }
                }

                if (zone.zone_type !== 'staff') {
                    const buttonId = `btn-${zone.zone_id}`;
                    if (isBlocked) {
                        popupContent += `
                            <button id="${buttonId}" onclick="unblockZone(${zone.zone_id}, '${zone.zone_number}')" style="
                                background: #28a745;
                                color: white;
                                border: none;
                                padding: 8px 16px;
                                border-radius: 20px;
                                cursor: pointer;
                                margin-top: 8px;
                                font-weight: 600;
                            ">Unblock Zone</button>
                        `;
                    } else {
                        popupContent += `
                            <button onclick="openBlockModal(${zone.zone_id}, '${zone.zone_number}')" style="
                                background: #dc3545;
                                color: white;
                                border: none;
                                padding: 8px 16px;
                                border-radius: 20px;
                                cursor: pointer;
                                margin-top: 8px;
                                font-weight: 600;
                            ">Block Zone</button>
                        `;
                    }
                }

                popupContent += '</div>';

                marker.bindPopup(popupContent);
                marker.addTo(map);
                parkingMarkers.push(marker);
            });
        }

        function getMarkerColor(status, zoneType, isBlocked) {
            if (isBlocked) return '#6c757d';
            if (zoneType === 'staff') return '#2196F3';
            
            switch(status) {
                case 'full':
                    return '#dc3545'; // Red
                case 'half_full':
                    return '#ffc107'; // Yellow
                case 'empty':
                case '':
                default:
                    return '#28a745'; // Green for empty/available
            }
        }   

        function getStatusText(status, zoneType, isBlocked) {
            if (isBlocked) return 'Blocked';
            if (zoneType === 'staff') return 'Staff';
            if (status === 'empty' || status === '') return 'AVAILABLE';
            return status.toUpperCase().replace('_', '-');
        }

        function formatTime(timeString) {
            if (!timeString) return 'N/A';
            // Handle both HH:MM:SS and HH:MM formats
            const timeParts = timeString.split(':');
            const hours = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            // Convert to 12-hour format
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            
            return `${displayHours}:${minutes} ${ampm}`;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const dateObj = new Date(dateString);
            // Malaysian date format: DD/MM/YYYY
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            
            return `${day}/${month}/${year}`;
        }

        function displayFutureBlocks(blocks) {
            const container = document.getElementById('future-blocks');
            
            if (!blocks || blocks.length === 0) {
                container.innerHTML = '<div class="no-blocks">No future blocks scheduled</div>';
                return;
            }

            let html = '<div class="future-blocks-list">';
            
            blocks.forEach(block => {
                html += `
                <div class="future-block-card">
                    <div class="block-header">
                        <span class="zone-badge">P${block.zone_number}</span>
                        <span class="block-date">${formatDate(block.date)}</span>
                        <button class="cancel-btn" onclick="cancelBlock(${block.id})">✖ Cancel</button>
                    </div>
                    <div class="block-details">
                        <p><strong>Time:</strong> ${formatTime(block.start_time)} - ${formatTime(block.end_time)}</p>
                        <p><strong>Reason:</strong> ${block.reason}</p>
                        ${block.schedule_type === 'weekly' ? 
                            `<p><strong>Recurring:</strong> Weekly on ${getDayNames(block.weekly_days)} until ${formatDate(block.recurring_end_date)}</p>` : ''}
                    </div>
                </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        function getDayNames(daysArray) {
            if (!daysArray || !Array.isArray(daysArray)) return 'N/A';
            const dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            return daysArray.map(day => dayNames[day]).join(', ');
        }

        function displayHistory(history) {
            console.log('Displaying history:', history); // Debug log
    
            const container = document.getElementById('history-blocks');
            
            if (!container) {
                console.error('Container element with id "history-blocks" not found!');
                return;
            }
            
            if (!history || history.length === 0) {
                container.innerHTML = '<div class="no-blocks">No history records found</div>';
                return;
            }

            let html = '<div class="history-list">';
            
            history.forEach((record, index) => {
                console.log(`Processing record ${index}:`, record); // Debug log
                
                const timeText = record.start_time && record.end_time 
                    ? `${formatTime(record.start_time)} - ${formatTime(record.end_time)}`
                    : 'N/A';

                const statusClass = record.status ? record.status.toLowerCase() : 'unknown';
                const statusText = record.status ? 
                    record.status.charAt(0).toUpperCase() + record.status.slice(1) : 'Unknown';

                html += `
                <div class="history-card ${statusClass}">
                    <div class="history-header">
                        <span class="zone-badge">P${record.zone_number || 'N/A'}</span>
                        <span class="history-date">${formatDate(record.date)}</span>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="history-details">
                        <p><strong>Time:</strong> ${timeText}</p>
                        <p><strong>Reason:</strong> ${record.reason || 'N/A'}</p>
                        <p><strong>Action by:</strong> ${record.admin_username || 'System'}</p>
                        <p><strong>Action at:</strong> ${formatDateTime(record.created_at)}</p>
                        ${record.schedule_type === 'weekly' ? 
                            `<p><strong>Schedule:</strong> Weekly (${record.weekly_days ? getDayNames(record.weekly_days) : 'N/A'})</p>` : ''}
                    </div>
                </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            
            console.log('History display completed');
        }

        function cancelBlock(blockId) {
            if (!confirm('Are you sure you want to cancel this block?')) return;
            
            fetch(`/admin/zone-blocks/cancel/${blockId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Block cancelled successfully');
                    loadFutureBlocks();
                    updateMarkers();
                } else {
                    alert(data.message || 'Failed to cancel block');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the block');
            });
        }

        function formatDateTime(datetimeString) {
            if (!datetimeString) return 'N/A';
            
            const dateObj = new Date(datetimeString);
            // Malaysian datetime format: DD/MM/YYYY, HH:MM
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            
            return `${day}/${month}/${year}, ${hours}:${minutes}`;
        }

        function toggleScheduleOptions() {
            const scheduleType = document.getElementById('schedule_type').value;
            const weeklyOptions = document.getElementById('weekly-options');
            
            if (scheduleType === 'weekly') {
                weeklyOptions.style.display = 'block';
                const defaultEndDate = new Date();
                defaultEndDate.setDate(defaultEndDate.getDate() + 14);
                document.getElementById('recurring_end_date').valueAsDate = defaultEndDate;
            } else {
                weeklyOptions.style.display = 'none';
            }
        }

        // Fixed openBlockModal function
        window.openBlockModal = function(zoneId, zoneNumber, zoneName) {
            const modal = document.getElementById('blockModal');
            const zoneIdInput = document.getElementById('zone_id');
            const modalTitle = document.getElementById('modalZoneTitle');
            
            zoneIdInput.value = zoneId;
            const displayName = zoneName && zoneName !== 'undefined' ? zoneName : `Zone P${zoneNumber}`;
            modalTitle.textContent = `Block ${displayName}`;
            document.getElementById('date').valueAsDate = new Date();
            modal.style.display = 'block';
        };

        // Fixed unblockZone function with proper error handling and button state management
        window.unblockZone = function(zoneId, zoneNumber) {
            if (!confirm(`Confirm to unblock Zone P${zoneNumber}?`)) return;

            const button = event.target;
            const originalText = button.textContent;
            
            // Set button to loading state
            button.textContent = 'Unblocking...';
            button.disabled = true;

            const restoreButton = () => {
                button.textContent = originalText;
                button.disabled = false;
            };

            fetch(`/admin/zone-blocks/unblock/${zoneId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(`Zone P${data.zone.number} unblocked and reset successfully!`);
                    // Update both markers and future blocks
                    return Promise.all([
                        updateMarkers(),
                        loadFutureBlocks()
                    ]);
                } else {
                    restoreButton();
                    alert(data.message || 'Failed to unblock the zone.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                restoreButton();
                alert('An error occurred while unblocking.');
            });
        };

        // Fixed updateMarkers function with race condition protection
        function updateMarkers() {
            if (isUpdatingMarkers) {
                console.log('Update already in progress, skipping...');
                return Promise.resolve();
            }

            isUpdatingMarkers = true;
            
            return fetch('/zones-for-map')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Fresh zone data:', data);
                    zonesData = data;
                    createParkingMarkers();
                    return data;
                })
                .catch(error => {
                    console.error('Error updating markers:', error);
                    throw error;
                })
                .finally(() => {
                    isUpdatingMarkers = false;
                });
        }

        // Fixed switchTab function
        window.switchTab = function(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            const targetTab = document.getElementById(tabName + '-tab');
            if (targetTab) {
                targetTab.classList.add('active');
            }
            
            event.target.classList.add('active');
            
            if (tabName === 'future') {
                loadFutureBlocks();
            } else if (tabName === 'history') {
                loadHistory();
            } 
        };

        // Fixed loadFutureBlocks with proper error handling
        window.loadFutureBlocks = function() {
            const search = document.getElementById('future-search')?.value || '';
            const filter = document.getElementById('future-filter')?.value || '';
            
            fetch(`/admin/future-blocks?search=${encodeURIComponent(search)}&filter=${encodeURIComponent(filter)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    displayFutureBlocks(data);
                })
                .catch(error => {
                    console.error('Error loading future blocks:', error);
                    const container = document.getElementById('future-blocks');
                    if (container) {
                        container.innerHTML = '<div class="no-blocks">Error loading future blocks. Please try again.</div>';
                    }
                });
        };

        // Fixed loadHistory with proper error handling
        window.loadHistory = function() {
            const search = document.getElementById('history-search')?.value || '';
            const status = document.getElementById('history-status')?.value || '';
            const period = document.getElementById('history-period')?.value || '';
            
            const url = `/admin/block-history?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&period=${encodeURIComponent(period)}`;
            
            console.log('Loading history from:', url);
            
            // Show loading state
            const container = document.getElementById('history-blocks');
            if (container) {
                container.innerHTML = '<div class="loading">Loading history...</div>';
            }
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response data:', data);
                    console.log('Data type:', typeof data);
                    console.log('Data length:', Array.isArray(data) ? data.length : 'Not an array');
                    
                    if (Array.isArray(data)) {
                        console.log('First record:', data[0]);
                    }
                    
                    displayHistory(data);
                })
                .catch(error => {
                    console.error('Error details:', error);
                    const container = document.getElementById('history-blocks');
                    if (container) {
                        container.innerHTML = `
                            <div class="error">
                                <p>Error loading history:</p>
                                <p><strong>${error.message}</strong></p>
                                <p>Check the browser console for more details.</p>
                            </div>
                        `;
                    }
                });
        };

        function showError(message) {
            const mapContainer = document.querySelector('.map-container');
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div class="error-message">
                        <strong>Error:</strong> ${message}
                        <br><br>
                        <button onclick="location.reload()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Reload Page
                        </button>
                    </div>
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            loadFutureBlocks();
            loadHistory();

            const modal = document.getElementById('blockModal');
            const closeBtn = document.querySelector('.close');
            const form = document.getElementById('blockForm');
            const MAX_BLOCK_HOURS = 24;
            
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    if (modal) modal.style.display = 'none';
                });
            }
            
            window.addEventListener('click', (event) => {
                if (event.target === modal && modal) {
                    modal.style.display = 'none';
                }
            });
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    
                    const dateValue = formData.get('date');
                    const startTimeValue = formData.get('start_time');
                    const endTimeValue = formData.get('end_time');
                    
                    if (!dateValue || !startTimeValue || !endTimeValue) {
                        alert("Please fill in all required fields");
                        return;
                    }
                    
                    const start = new Date(`${dateValue} ${startTimeValue}`);
                    const end = new Date(`${dateValue} ${endTimeValue}`);
                    
                    if (end <= start) {
                        alert("End time must be after start time");
                        return;
                    }
                    
                    if ((end - start) > (MAX_BLOCK_HOURS * 3600000)) {
                        alert(`Blocks cannot exceed ${MAX_BLOCK_HOURS} hours`);
                        return;
                    }

                    // Disable submit button during processing
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalSubmitText = submitBtn.textContent;
                    submitBtn.textContent = 'Processing...';
                    submitBtn.disabled = true;

                    fetch('/admin/zone-blocks/store', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            modal.style.display = 'none';
                            form.reset();
                            alert(`Zone P${data.zone.number} blocked successfully!`);
                            
                            // Update markers and future blocks
                            Promise.all([
                                updateMarkers(),
                                loadFutureBlocks()
                            ]).catch(error => {
                                console.error('Error updating after block:', error);
                            });
                            
                        } else {    
                            alert(data.message || 'Failed to block parking zone');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    })
                    .finally(() => {
                        // Restore submit button
                        submitBtn.textContent = originalSubmitText;
                        submitBtn.disabled = false;
                    });
                });
            }
            
            // Set up periodic refresh with error handling
            setInterval(() => {
                updateMarkers().catch(error => {
                    console.error('Periodic update failed:', error);
                });
            }, 30000);
        });
    </script>
</body>
</html>