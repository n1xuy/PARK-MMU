<!DOCTYPE html>
<html>
<head>
    <title>Parking Management - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/parking-styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
            </div>
            <h1 class="admin-title">PARKING ZONE MANAGEMENT</h1>
        </div>
        
        <div class="parking-content">
            
            <!-- Zone Management Form -->
            <div class="zone-management-form">
                <h2><i class="fas fa-parking"></i> Zone Management</h2>
                
                <form id="zoneForm">
                    <input type="hidden" id="zoneId" name="zoneId">
                    
                    <div class="form-group">
                        <label for="zoneName">Zone Name:</label>
                        <input type="text" id="zoneName" name="zoneName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zoneLocation">Location:</label>
                        <input type="text" id="zoneLocation" name="zoneLocation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="totalSpaces">Total Spaces:</label>
                        <input type="number" id="totalSpaces" name="totalSpaces" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="availableSpaces">Available Spaces:</label>
                        <input type="number" id="availableSpaces" name="availableSpaces" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zoneType">Zone Type:</label>
                        <select id="zoneType" name="zoneType" required>
                            <option value="student">Student Parking</option>
                            <option value="staff">Staff Parking</option>
                            <option value="disabled">Disabled Parking</option>
                            <option value="visitor">Visitor Parking</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="zoneStatus">Status:</label>
                        <select id="zoneStatus" name="zoneStatus" required>
                            <option value="active">Active</option>
                            <option value="maintenance">Under Maintenance</option>
                            <option value="full">Full</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="saveZone" class="btn-primary"><i class="fas fa-save"></i> Save Zone</button>
                        <button type="button" id="newZone" class="btn-secondary"><i class="fas fa-plus"></i> New Zone</button>
                        <button type="button" id="deleteZone" class="btn-danger"><i class="fas fa-trash"></i> Delete Zone</button>
                    </div>
                </form>
            </div>
            
            <!-- Zone List Table -->
            <div class="zone-list">
                <h2><i class="fas fa-list"></i> Existing Zones</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Zone ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="zoneTableBody">
                        <!-- Zones will be populated here by JavaScript -->
                        <tr>
                            <td>Z001</td>
                            <td>North Parking</td>
                            <td>Student</td>
                            <td>45/100</td>
                            <td>Active</td>
                            <td><button class="btn-edit" onclick="editZone('Z001')"><i class="fas fa-edit"></i></button></td>
                        </tr>
                        <tr>
                            <td>Z002</td>
                            <td>East Parking</td>
                            <td>Staff</td>
                            <td>12/50</td>
                            <td>Active</td>
                            <td><button class="btn-edit" onclick="editZone('Z002')"><i class="fas fa-edit"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="{{ route('admin.menu') }}" class="back-button">
            <img src="{{ asset('images/return page.png') }}" alt="Back">
        </a>
    </div>

    <script>
        // Sample JavaScript for zone management functionality
        document.addEventListener('DOMContentLoaded', function() {
            // New Zone button
            document.getElementById('newZone').addEventListener('click', function() {
                document.getElementById('zoneForm').reset();
                document.getElementById('zoneId').value = '';
            });
            
            // Save Zone button
            document.getElementById('saveZone').addEventListener('click', function() {
                // Here you would typically send the form data to your backend
                const zoneData = {
                    id: document.getElementById('zoneId').value,
                    name: document.getElementById('zoneName').value,
                    location: document.getElementById('zoneLocation').value,
                    totalSpaces: document.getElementById('totalSpaces').value,
                    availableSpaces: document.getElementById('availableSpaces').value,
                    type: document.getElementById('zoneType').value,
                    status: document.getElementById('zoneStatus').value
                };
                
                console.log('Saving zone:', zoneData);
                alert('Zone saved successfully!');
                // In a real application, you would call an API endpoint here
            });
            
            // Delete Zone button
            document.getElementById('deleteZone').addEventListener('click', function() {
                const zoneId = document.getElementById('zoneId').value;
                if (zoneId) {
                    if (confirm('Are you sure you want to delete this zone?')) {
                        console.log('Deleting zone:', zoneId);
                        alert('Zone deleted successfully!');
                        document.getElementById('zoneForm').reset();
                        // In a real application, you would call an API endpoint here
                    }
                } else {
                    alert('No zone selected to delete');
                }
            });
        });
        
        function editZone(zoneId) {
            // In a real application, you would fetch zone data from your backend
            console.log('Editing zone:', zoneId);
            
            // Sample data - replace with actual data fetch
            const sampleData = {
                'Z001': {
                    name: 'North Parking',
                    location: 'North Campus, Block A',
                    totalSpaces: 100,
                    availableSpaces: 45,
                    type: 'student',
                    status: 'active'
                },
                'Z002': {
                    name: 'East Parking',
                    location: 'East Wing, Near Library',
                    totalSpaces: 50,
                    availableSpaces: 12,
                    type: 'staff',
                    status: 'active'
                }
            };
            
            if (sampleData[zoneId]) {
                const zone = sampleData[zoneId];
                document.getElementById('zoneId').value = zoneId;
                document.getElementById('zoneName').value = zone.name;
                document.getElementById('zoneLocation').value = zone.location;
                document.getElementById('totalSpaces').value = zone.totalSpaces;
                document.getElementById('availableSpaces').value = zone.availableSpaces;
                document.getElementById('zoneType').value = zone.type;
                document.getElementById('zoneStatus').value = zone.status;
            }
        }
    </script>
</body>
</html>